// cypress/e2e/09_admin_produk.cy.js
// Sheet 07 - Manajemen Produk (Admin)

describe('Sheet 07 - Manajemen Produk (Admin)', () => {
  // GANTI dengan admin valid
  const adminEmail = 'admin@farmunand.local';
  const adminPassword = '111111';

  // GANTI dengan ID produk yang benar-benar ada di DB
  const existingProductId = 1;

  function loginAdmin() {
    cy.visit('/login');
    cy.get('input[name="email"]').clear().type(adminEmail);
    cy.get('input[name="password"]').clear().type(adminPassword);
    cy.contains('button', 'Login').click();
    cy.url({ timeout: 10000 }).should('include', '/dashboard');
  }

  beforeEach(() => {
    loginAdmin();
  });

  // PRO-001: Halaman daftar produk admin bisa dibuka
  it('PRO-001: Halaman daftar produk admin bisa dibuka', () => {
    cy.visit('/admin/produk');

    // Judul / heading halaman (dibuat longgar)
    cy.contains(/Data Produk|Daftar Produk|Produk|Manajemen Produk/i)
      .should('exist');

    // Ada tabel daftar produk
    cy.get('table, .table').should('exist');
  });


  // PRO-002: Tombol Tambah Produk membuka form create
  it('PRO-002: Tombol Tambah Produk membuka form tambah', () => {
    cy.visit('/admin/produk');

    cy.contains('a,button', /Tambah Produk|Produk Baru/i)
      .first()
      .click();

    cy.url().should('include', '/admin/produk/create');

    // Cek field-field utama form
    cy.get('form').should('exist');
    cy.get('input[name="nama"], input[name="nama_produk"]').should('exist');
    cy.get('input[name="harga"]').should('exist');
    cy.get('input[name="stok"]').should('exist');
    cy.get('select[name="kategori_id"], select[name="kategori"]').should(
      'exist'
    );
  });

    // PRO-003: Tambah produk gagal jika field wajib kosong
  it('PRO-003: Tambah produk gagal jika field wajib kosong', () => {
    cy.visit('/admin/produk/create');

    // Submit form tanpa mengisi apa pun
    cy.get('form').submit();

    // Bisa tetap di /create atau kembali ke /admin/produk
    cy.url().should('match', /\/admin\/produk(\/create)?$/);

    // Pastikan ada indikasi error validasi di halaman:
    // - bootstrap invalid-feedback / text-danger
    // - atau alert error di atas form
    cy.get('.invalid-feedback, .text-danger, .alert-danger, .alert[role="alert"]')
      .should('exist');
  });


    // PRO-004: Admin bisa membuka form edit produk dari daftar
  it('PRO-004: Admin bisa membuka form edit produk dari daftar', () => {
    cy.visit('/admin/produk');

    // Klik tombol Edit/Ubah pada baris pertama tabel
    cy.get('table tbody tr').first().within(() => {
      cy.contains('a,button', /Edit|Ubah/i).click();
    });

    // URL mengarah ke /admin/produk/edit/{id}
    cy.url().should('match', /\/admin\/produk\/edit\/\d+$/);

    // Form edit tampil
    cy.get('form').should('exist');

    // Field nama produk terisi (tidak kosong)
    cy.get('input[name="nama"], input[name="nama_produk"]')
      .first()
      .invoke('val')
      .should('not.be.empty');
  });

  // PRO-005: Modal / konfirmasi hapus produk
  it('PRO-005: Konfirmasi hapus produk muncul saat tombol Hapus diklik', () => {
    cy.visit('/admin/produk');

    cy.get('tbody').then(($tbody) => {
      if (!$tbody.find('tr').length) {
        cy.log('Tidak ada produk untuk diuji hapus.');
        return;
      }

      // Klik tombol Hapus pertama
      cy.contains('button, a', /Hapus/i)
        .first()
        .click();

      // Dua kemungkinan: modal konfirmasi atau alert bawaan browser
      cy.get('body').then(($body) => {
        if ($body.find('.modal.show, #deleteProductModal').length) {
          cy.get('.modal.show, #deleteProductModal')
            .should('be.visible')
            .within(() => {
              cy.contains(/Yakin|Ya, Hapus/i).should('exist');
            });
        } else {
          // kalau pakai window.confirm di onsubmit
          cy.window().then((win) => {
            cy.stub(win, 'confirm').returns(false);
          });
        }
      });
    });
  });
});
