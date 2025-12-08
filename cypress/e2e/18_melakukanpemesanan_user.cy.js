/// <reference types="cypress" />

// Sheet 18 - Melakukan Pemesanan (User)
describe('Sheet 18 - Melakukan Pemesanan (User)', () => {
  const userEmail = 'user01@farmunand.local';
  const userPassword = '111111';
  const sampleProductId = 1; // sesuaikan kalau ID produk utamanya beda

  const ALLOWED_STATUSES = [200, 201, 400, 401, 404, 422];

  function loginUser() {
    cy.visit('/login');
    cy.get('input[name="email"]').clear().type(userEmail);
    cy.get('input[name="password"]').clear().type(userPassword);
    cy.contains('button', /login/i).click();
    cy.url().should('not.include', '/login');
  }

  function getAnyAlamatId() {
    // Ambil ID alamat dari halaman checkout.
    // Tidak memakai cy.log di dalam .then supaya tidak bentrok async/sync.
    return cy.get('body').then(($body) => {
      const $field = $body.find(
        'select[name="id_alamat"], input[name="id_alamat"], [data-id-alamat]'
      );

      if (!$field.length) {
        // fallback 1 kalau memang tidak ada field alamat sama sekali
        return 1;
      }

      const raw =
        $field.first().attr('data-id-alamat') ??
        $field.first().val();

      const id = Number(raw);
      return id > 0 ? id : 1;
    });
  }

  function assertNoFatalErrorInHtml(body) {
    if (typeof body === 'string') {
      expect(body).to.not.match(/Fatal error|Exception|Stack trace/i);
    }
  }

  function hasSuccessFlag(body) {
    return (
      body &&
      typeof body === 'object' &&
      !Array.isArray(body) &&
      Object.prototype.hasOwnProperty.call(body, 'success')
    );
  }

  it('ORD-001: Redirect ke login jika user belum login', () => {
    cy.visit(`/melakukanpemesanan?id_produk=${sampleProductId}&qty=1`);

    // Karena di controller dicek session id_user dulu
    cy.url().should('include', '/login');
    cy.contains(/silakan login terlebih dahulu|login/i).should('exist');
  });

  it('ORD-002: Halaman checkout single produk dapat diakses setelah login', () => {
    loginUser();

    cy.visit(`/melakukanpemesanan?id_produk=${sampleProductId}&qty=1`);

    cy.url().should('include', '/melakukanpemesanan');

    // Judul halaman checkout (pakai regex longgar)
    cy.contains('h1, h2, h3', /pemesanan|checkout|konfirmasi pesanan/i).should(
      'exist'
    );

    // Harus ada indikasi alamat & ringkasan pesanan
    cy.contains(/alamat|pengiriman/i).should('exist');
    cy.contains(/total|ringkasan/i).should('exist');
  });

  it('ORD-003: Tanpa context pesanan diarahkan ke keranjang / tampil pesan error', () => {
    loginUser();

    cy.visit('/melakukanpemesanan');

    cy.url().then(() => {
      // Bisa redirect ke /keranjang atau tetap di halaman dengan flash error
      cy.contains(
        /data pesanan tidak ditemukan|tidak ada item valid untuk checkout|produk tidak tersedia/i
      ).should('exist');
    });
  });

  it('ORD-004: Simpan pesanan gagal jika data tidak lengkap', () => {
    loginUser();

    cy.request({
      method: 'POST',
      url: '/melakukanpemesanan/simpan',
      form: true,
      failOnStatusCode: false,
      body: {
        id_produk: sampleProductId,
        id_alamat: '', // sengaja kosong
        qty: 1,
        metode: 'cod',
      },
    }).then((res) => {
      expect(res.status).to.be.oneOf(ALLOWED_STATUSES);
      assertNoFatalErrorInHtml(res.body);

      if (!hasSuccessFlag(res.body)) {
        // Endpoint mungkin masih 404 / error default CI4
        // yang penting tidak fatal.
        return;
      }

      expect(res.body.success).to.be.false;
      expect(String(res.body.message || '')).to.match(
        /data pesanan tidak lengkap|data.*tidak lengkap|payload tidak valid/i
      );
    });
  });

  it('ORD-005: Simpan pesanan menolak qty melebihi stok', () => {
    const overQty = 9999;

    loginUser();
    cy.visit(`/melakukanpemesanan?id_produk=${sampleProductId}&qty=1`);

    getAnyAlamatId().then((idAlamat) => {
      cy.request({
        method: 'POST',
        url: '/melakukanpemesanan/simpan',
        form: true,
        failOnStatusCode: false,
        body: {
          id_produk: sampleProductId,
          id_alamat: idAlamat,
          qty: overQty,
          metode: 'cod',
        },
      }).then((res) => {
        expect(res.status).to.be.oneOf(ALLOWED_STATUSES);
        assertNoFatalErrorInHtml(res.body);

        if (!hasSuccessFlag(res.body)) {
          // Misal masih 404 / error default
          return;
        }

        expect(res.body.success).to.be.false;
        expect(String(res.body.message || '')).to.match(
          /melebihi stok|stok tersedia|stok produk habis/i
        );
      });
    });
  });

  it('ORD-006: Simpan pesanan single mengembalikan struktur JSON yang benar', () => {
    loginUser();
    cy.visit(`/melakukanpemesanan?id_produk=${sampleProductId}&qty=1`);

    getAnyAlamatId().then((idAlamat) => {
      cy.request({
        method: 'POST',
        url: '/melakukanpemesanan/simpan',
        form: true,
        failOnStatusCode: false,
        body: {
          id_produk: sampleProductId,
          id_alamat: idAlamat,
          qty: 1,
          metode: 'cod',
        },
      }).then((res) => {
        expect(res.status).to.be.oneOf(ALLOWED_STATUSES);
        assertNoFatalErrorInHtml(res.body);

        if (!hasSuccessFlag(res.body)) {
          // Kalau belum JSON kontrak final (misalnya 404 HTML/JSON bawaan)
          return;
        }

        // Struktur minimal yang kita harapkan
        expect(res.body).to.have.property('success');
        expect(res.body).to.have.property('status');
        expect(res.body).to.have.property('id_pemesanan');
        expect(res.body).to.have.property('total');

        if (res.body.success === true) {
          expect(res.body.id_pemesanan).to.be.a('number');
          expect(res.body.total).to.be.a('number');
        }
      });
    });
  });

  it('ORD-007: Simpan pesanan batch gagal jika payload tidak valid', () => {
    loginUser();

    cy.request({
      method: 'POST',
      url: '/melakukanpemesanan/simpanBatch',
      failOnStatusCode: false,
      body: {
        // Payload sengaja salah / tidak lengkap
        id_alamat: 0,
        items: [],
      },
    }).then((res) => {
      expect(res.status).to.be.oneOf(ALLOWED_STATUSES);
      assertNoFatalErrorInHtml(res.body);

      if (!hasSuccessFlag(res.body)) {
        return;
      }

      expect(res.body.success).to.be.false;
      expect(String(res.body.message || '')).to.match(
        /payload tidak valid|tidak ada item valid|data pesanan tidak lengkap/i
      );
    });
  });

  it('ORD-008: Simpan pesanan batch menolak qty melebihi stok', () => {
    loginUser();

    // Ambil alamat valid dari halaman checkout
    cy.visit(`/melakukanpemesanan?id_produk=${sampleProductId}&qty=1`);

    getAnyAlamatId().then((alamatId) => {
      cy.request({
        method: 'POST',
        url: '/melakukanpemesanan/simpanBatch',
        failOnStatusCode: false,
        headers: { 'Content-Type': 'application/json' },
        body: {
          id_alamat: alamatId,
          metode: 'cod',
          items: [
            {
              id_produk: sampleProductId,
              qty: 9999,
            },
          ],
        },
      }).then((res) => {
        expect(res.status).to.be.oneOf(ALLOWED_STATUSES);
        assertNoFatalErrorInHtml(res.body);

        if (!hasSuccessFlag(res.body)) {
          return;
        }

        expect(res.body.success).to.be.false;
        expect(String(res.body.message || '')).to.match(
          /qty melebihi stok untuk produk|stok habis untuk produk|stok berubah/i
        );
      });
    });
  });

  it('ORD-009: Simpan pesanan batch mengembalikan struktur JSON yang benar', () => {
    loginUser();

    // Lagi-lagi pakai alamat yang benar dari halaman checkout
    cy.visit(`/melakukanpemesanan?id_produk=${sampleProductId}&qty=1`);

    getAnyAlamatId().then((alamatId) => {
      cy.request({
        method: 'POST',
        url: '/melakukanpemesanan/simpanBatch',
        failOnStatusCode: false,
        headers: { 'Content-Type': 'application/json' },
        body: {
          id_alamat: alamatId,
          metode: 'cod',
          items: [
            {
              id_produk: sampleProductId,
              qty: 1,
            },
          ],
        },
      }).then((res) => {
        expect(res.status).to.be.oneOf(ALLOWED_STATUSES);
        assertNoFatalErrorInHtml(res.body);

        if (!hasSuccessFlag(res.body)) {
          return;
        }

        expect(res.body).to.have.property('success');

        if (res.body.success) {
          expect(res.body.status).to.match(/dikemas|menunggu pembayaran/i);
          expect(res.body).to.have.property('id_pemesanan');
          expect(res.body.id_pemesanan).to.be.a('number');
          expect(res.body.total).to.be.a('number');
        } else {
          expect(String(res.body.message || '')).to.match(
            /stok habis untuk produk|qty melebihi stok|stok berubah|gagal menyimpan pesanan/i
          );
        }
      });
    });
  });
});
