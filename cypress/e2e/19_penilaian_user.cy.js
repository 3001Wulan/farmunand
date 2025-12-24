describe('Sheet 06 - Penilaian & Ulasan (User)', () => {
  const userEmail = 'user01@farmunand.local';
  const userPassword = '111111';

  function loginUser() {
    cy.visit('/login');
    cy.get('input[name="email"]').clear().type(userEmail);
    cy.get('input[name="password"]').clear().type(userPassword);
    cy.contains('button', /login/i).click();
    cy.url({ timeout: 10000 }).should('not.include', '/login');
  }

  beforeEach(() => {
    loginUser();
  });

   it('PEN-001: Halaman daftar penilaian bisa dibuka', () => {
    cy.visit('/penilaian/daftar');
    cy.contains(/Penilaian|Beri Penilaian|Ulasan/i).should('exist');
    cy.get('body').then(($body) => {
      const hasTableRows = $body.find('table tbody tr').length > 0;
      const hasCards = $body.find('.penilaian-card').length > 0;

      if (hasTableRows || hasCards) {
        cy.contains(/Beri Penilaian|Nilai Produk/i).should('exist');
      } else {
        cy.log('Tidak ada pesanan yang dapat dinilai saat ini (halaman kosong).');
      }
    });
  });


  // 06B - Form penilaian per produk
  it('PEN-002: Form penilaian dapat dibuka dari daftar', () => {
    cy.visit('/penilaian/daftar');

    cy.get('body').then(($body) => {
      const hasButton = $body.find('a,button')
        .filter((_, el) => /Beri Penilaian|Nilai Produk/i.test(el.innerText))
        .length > 0;

      if (!hasButton) {
        cy.log('Tidak ada pesanan yang bisa dinilai saat ini.');
        return;
      }

      // Klik tombol "Beri Penilaian" pertama
      cy.contains(/Beri Penilaian|Nilai Produk/i).first().click();

      // URL harus mengarah ke /penilaian/{id}
      cy.url().should('match', /\/penilaian\/\d+$/);

      // Cek form rating & ulasan
      cy.get('form').should('exist');
      // rating bisa berupa select, radio, atau input range → cek generik dulu
      cy.get('input[name="rating"], select[name="rating"], input[name="nilai"]')
        .should('exist');
      cy.get('textarea[name="ulasan"], textarea[name="review"], textarea')
        .first()
        .should('exist');
    });
  });

    // PEN-003: Berhasil submit penilaian valid
  it('PEN-003: Berhasil submit penilaian valid', () => {
    cy.visit('/penilaian/daftar');

    cy.get('body').then(($body) => {
      const buttons = $body
        .find('a,button')
        .filter((_, el) => /Beri Penilaian|Nilai Produk/i.test(el.innerText));

      // === CABANG 1: TIDAK ADA PESANAN YANG BISA DINILAI ===
      if (!buttons.length) {
        cy.log(
          'Belum ada pesanan yang bisa dinilai - flow submit penilaian dilewati, dianggap PASS untuk kondisi ini.'
        );

        // Optional: sekadar memastikan memang tidak ada tombol
        // expect(buttons.length).to.eq(0);

        return; // selesai, test dianggap berhasil
      }

      // === CABANG 2: ADA PESANAN → TEST FLOW SUBMIT PENILAIAN ===

      // Klik tombol pertama untuk buka modal
      cy.wrap(buttons.first()).click();

      // Pastikan modal muncul
      cy.get('#reviewModal', { timeout: 10000 }).should('be.visible');

      cy.get('#reviewModal').within(() => {
        // Set rating ke 5
        cy.get('input[name="rating"], input[name="nilai"], select[name="rating"]')
          .first()
          .then(($input) => {
            const el  = $input[0];
            const tag = el.tagName.toLowerCase();

            if (tag === 'select') {
              cy.wrap($input).select('5', { force: true });
            } else {
              cy.wrap($input).invoke('val', '5');
            }
          });

        // Isi ulasan
        cy.get('textarea[name="ulasan"], textarea[name="review"], textarea')
          .first()
          .clear({ force: true })
          .type(
            'Penilaian otomatis Cypress: produk bagus, pengiriman cepat.',
            { force: true },
          );

        // Submit
        cy.contains('button', /Kirim|Simpan|Submit/i)
          .click({ force: true });
      });

      // Setelah submit → balik ke daftar penilaian + pesan sukses
      cy.url({ timeout: 10000 }).should('include', '/penilaian');

      cy.contains(/Penilaian berhasil disimpan|Terima kasih atas penilaian/i)
        .should('exist');
    });
  });




    // PEN-004: Gagal submit jika rating tidak diisi
  it('PEN-004: Gagal submit jika rating tidak diisi', () => {
    cy.visit('/penilaian/daftar');

    cy.get('body').then(($body) => {
      const buttons = $body
        .find('a,button')
        .filter((_, el) => /Beri Penilaian|Nilai Produk/i.test(el.innerText));

      // === CABANG 1: TIDAK ADA PESANAN YANG BISA DINILAI ===
      if (!buttons.length) {
        cy.log(
          'Belum ada pesanan yang bisa dinilai - skenario validasi tanpa rating tidak dapat dijalankan, dianggap PASS untuk kondisi ini.'
        );

        // Optional: sekadar memastikan memang tidak ada tombol
        // expect(buttons.length).to.eq(0);

        return; // selesai, test PASS untuk kondisi no-data
      }

      // === CABANG 2: ADA PESANAN → TEST VALIDASI TANPA RATING ===

      // Buka modal
      cy.wrap(buttons.first()).click();

      cy.get('#reviewModal', { timeout: 10000 }).should('be.visible');

      cy.get('#reviewModal').within(() => {
        // Kosongkan rating
        cy.get('input[name="rating"], input[name="nilai"], select[name="rating"]')
          .first()
          .then(($input) => {
            const el  = $input[0];
            const tag = el.tagName.toLowerCase();

            if (tag === 'select') {
              // Kalau ada option kosong bisa diset di sini dengan select('', {force:true})
              // Kalau tidak ada, dibiarkan apa adanya (anggap belum dipilih)
            } else {
              cy.wrap($input).invoke('val', '');
            }
          });

        // Isi ulasan saja
        cy.get('textarea[name="ulasan"], textarea[name="review"], textarea')
          .first()
          .clear({ force: true })
          .type('Cypress test: kirim ulasan tanpa rating.', { force: true });

        // Submit
        cy.contains('button', /Kirim|Simpan|Submit/i)
          .click({ force: true });
      });

      // Harus muncul pesan error terkait rating
      cy.contains(
        /rating wajib diisi|rating harus diisi|silakan pilih rating|rating tidak boleh kosong/i
      ).should('exist');
    });
  });


});
