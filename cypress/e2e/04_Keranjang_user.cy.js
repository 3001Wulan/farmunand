// Tes ini memverifikasi fungsionalitas Keranjang Belanja Pembeli.

describe('Sheet 08 - Keranjang Belanja', () => {
  // Data login user (PASTIKAN VALID)
  const userEmail = 'user01@farmunand.local';
  const userPassword = '111111';

  // Data Produk (GANTI dengan ID produk yang BENAR-BENAR ADA di DB dan STOK > 0)
  const testProductId = 1;

  const cartItemSelector = '.table tbody tr';

  /**
   * Helper: pastikan user login dan berada di halaman keranjang.
   * (Dijalankan sebelum setiap test)
   */
  beforeEach(() => {
    // 1. Lakukan Login User
    cy.visit('/login');

    cy.get('input[name="email"]').clear().type(userEmail);
    cy.get('input[name="password"]').clear().type(userPassword);

    // Submit form login
    cy.get('input[name="password"]').closest('form').submit();

    cy.url().should('not.include', '/login');

    // 2. Akses halaman Keranjang
    cy.visit('/keranjang');
    cy.url().should('include', '/keranjang');
    cy.contains('.card-h', /Keranjang Saya/i).should('exist');
  });

  /**
   * Helper: kalau ada tombol Kosongkan, klik, supaya keranjang bersih.
   * Dipakai sebelum test yang butuh state keranjang kosong.
   */
  function emptyCartIfAny() {
    cy.visit('/keranjang');

    cy.get('body').then(($body) => {
      const hasClearBtn = $body.find(
        'button.btn-outline-danger:contains("Kosongkan")'
      ).length;

      if (hasClearBtn) {
        cy.window().then((win) => {
          cy.stub(win, 'confirm').returns(true);
        });

        cy.contains('button.btn-outline-danger', /Kosongkan/i).click({
          multiple: true,
        });

        cy.url().should('include', '/keranjang');
        cy.contains('td', /Keranjang masih kosong/i).should('exist');
      }
    });
  }

  /**
   * Helper: pastikan keranjang punya tepat 1 item (produk testProductId, qty default).
   * Diawali dengan mengosongkan keranjang.
   */
  function ensureCartHasSingleItem() {
    emptyCartIfAny();

    // Tambah 1 produk dari halaman detail
    cy.visit(`/detailproduk/${testProductId}`);
    cy.contains('button', /Masukkan Keranjang/i).first().click();

    // Balik ke keranjang
    cy.visit('/keranjang');
    cy.url().should('include', '/keranjang');

    // Pastikan hanya ada 1 baris item (bukan baris "Keranjang masih kosong")
    cy.get(cartItemSelector)
      .not(':contains("Keranjang masih kosong")')
      .should('have.length', 1);
  }

  // USR-CART-002: Memastikan produk dapat ditambahkan ke keranjang dari detail produk
  it('USR-CART-002: Tambah produk dan verifikasi item muncul di keranjang', () => {
    emptyCartIfAny();

    // 2. Tambahkan produk dari halaman detail (kuantitas default 1)
    cy.visit(`/detailproduk/${testProductId}`);
    cy.contains('button', /Masukkan Keranjang/i).first().click();

    // 3. Verifikasi redirect ke halaman keranjang setelah penambahan
    cy.url().should('include', '/keranjang');

    // 4. Verifikasi item baru muncul di keranjang
    cy.get(cartItemSelector)
      .should('have.length.of.at.least', 1)
      .first()
      .within(() => {
        cy.get('.fw-semibold')
          .should('exist')
          .invoke('text')
          .should('not.be.empty');

        cy.get('.view-state span.badge').should('contain', '1');
      });

    // 5. Pastikan total harga muncul
    // Verifikasi total harga muncul (tanpa TypeError)
    cy.get('tfoot th.text-start')
    .invoke('text')
    .then((text) => {
        expect(text.trim()).to.match(/Rp\s?\d{1,3}(\.\d{3})*/);
    });

  });

it('USR-CART-003: Mengubah kuantitas item', () => {
  const targetQty = 3;

  ensureCartHasSingleItem();

  cy.get(cartItemSelector)
    .not(':contains("Keranjang masih kosong")')
    .first()
    .within(() => {
      // Klik tombol Ubah yang terlihat
      cy.contains('button', /Ubah/i)
        .filter(':visible')
        .first()
        .click({ force: true });

      // Form edit muncul
      cy.get('.edit-state')
        .should('exist')
        .and('not.have.class', 'd-none')
        .within(() => {
          // 🔴 HANYA ambil input number di form edit (bukan yang hidden)
          cy.get('input[name="qty"][type="number"]')
            .clear({ force: true })
            .type(String(targetQty), { force: true });

          // Klik tombol Simpan yang terlihat di form edit
          cy.contains('button', /Simpan/i)
            .filter(':visible')
            .first()
            .click({ force: true });
        });
    });

  // Verifikasi kuantitas di view-state sudah berubah
  cy.url().should('include', '/keranjang');
  cy.get(cartItemSelector)
    .not(':contains("Keranjang masih kosong")')
    .first()
    .within(() => {
      cy.get('.view-state span.badge').should('contain', String(targetQty));
    });
});


  // USR-CART-004: Memastikan produk dapat dihapus dari keranjang
  it('USR-CART-004: Menghapus item dari keranjang', () => {
    const deleteButtonSelector = '.btn-delete-item';

    ensureCartHasSingleItem();

    // 2. Klik tombol Hapus (yang memicu modal)
    cy.get(cartItemSelector)
      .not(':contains("Keranjang masih kosong")')
      .first()
      .as('initialItem');

    cy.get('@initialItem').find(deleteButtonSelector).click();

    // 3. Konfirmasi hapus di dalam Modal
    cy.get('#deleteCartItemModal')
      .should('be.visible')
      .within(() => {
        cy.contains('button.btn-danger', /Ya, Hapus/i).click();
      });

    // 4. Verifikasi keranjang kosong
    cy.url().should('include', '/keranjang');
    cy.wait(500);

    cy.contains('td', /Keranjang masih kosong/i).should('exist');
    cy.get(cartItemSelector).should('have.length', 1);
  });

  // USR-CART-005: Tombol Checkout Semua mengarahkan ke halaman pemesanan
  it('USR-CART-005: Tombol Checkout Semua mengarahkan ke halaman pemesanan', () => {
    ensureCartHasSingleItem();

    cy.contains('button.btn-secondary', /Checkout Semua/i)
      .first()
      .click();

    cy.url().should('include', '/melakukanpemesanan');

    cy.contains('h1, h2, h3, h4', /Pemesanan|Checkout|Alamat|Ringkasan/i)
      .should('exist');
  });

  // === SKENARIO BARU ===

  // USR-CART-006: Tombol Checkout 1 produk (single item) mengarahkan ke halaman pemesanan
  it('USR-CART-006: Tombol Checkout 1 produk mengarahkan ke halaman pemesanan', () => {
    ensureCartHasSingleItem();

    cy.get(cartItemSelector)
      .not(':contains("Keranjang masih kosong")')
      .first()
      .within(() => {
        cy.contains('button', /^Checkout$/i).click();
      });

    cy.url().should('include', '/melakukanpemesanan');

    cy.contains('h1, h2, h3, h4', /Pemesanan|Checkout|Alamat|Ringkasan/i)
      .should('exist');
  });

  // USR-CART-007: Tombol Kosongkan benar-benar mengosongkan keranjang
  it('USR-CART-007: Tombol Kosongkan mengosongkan keranjang', () => {
    ensureCartHasSingleItem();

    cy.window().then((win) => {
      cy.stub(win, 'confirm').returns(true);
    });

    cy.contains('button.btn-outline-danger', /Kosongkan/i).click();

    cy.url().should('include', '/keranjang');
    cy.contains('td', /Keranjang masih kosong/i).should('exist');
  });

  // USR-CART-008: Tombol Lanjut Belanja mengarah ke dashboard user
it('USR-CART-008: Tombol Lanjut Belanja mengarahkan ke Dashboard User', () => {
  // di beforeEach kamu sudah login & berada di /keranjang
  cy.contains('a.btn-outline-secondary', /Lanjut Belanja/i).click();

  // Cukup pastikan benar-benar pindah ke dashboard user
  cy.url().should('include', '/dashboarduser');

  // Opsional (kalau mau sedikit tambahan): pastikan bukan balik ke /login
  cy.url().should('not.include', '/login');
});


});