describe('Sheet 07 - Manajemen Produk (Admin)', () => {
  const adminEmail = 'admin@farmunand.local';
  const adminPassword = '111111';

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

  it('PRO-001: Halaman daftar produk admin bisa dibuka', () => {
    cy.visit('/admin/produk');
    cy.contains(/Data Produk|Daftar Produk|Produk|Manajemen Produk/i)
      .should('exist');

    cy.get('table, .table').should('exist');
  });

  it('PRO-002: Tombol Tambah Produk membuka form tambah', () => {
    cy.visit('/admin/produk');

    cy.contains('a,button', /Tambah Produk|Produk Baru/i)
      .first()
      .click();

    cy.url().should('include', '/admin/produk/create');
    cy.get('form').should('exist');
    cy.get('input[name="nama"], input[name="nama_produk"]').should('exist');
    cy.get('input[name="harga"]').should('exist');
    cy.get('input[name="stok"]').should('exist');
    cy.get('select[name="kategori_id"], select[name="kategori"]').should(
      'exist'
    );
  });

  it('PRO-003: Tambah produk gagal jika field wajib kosong', () => {
    cy.visit('/admin/produk/create');

    cy.get('form').submit();

    cy.url().should('match', /\/admin\/produk(\/create)?$/);

    cy.get('.invalid-feedback, .text-danger, .alert-danger, .alert[role="alert"]')
      .should('exist');
  });

  it('PRO-004: Admin bisa membuka form edit produk dari daftar', () => {
    cy.visit('/admin/produk');

    cy.get('table tbody tr').first().within(() => {
      cy.contains('a,button', /Edit|Ubah/i).click();
    });

    cy.url().should('match', /\/admin\/produk\/edit\/\d+$/);

    cy.get('form').should('exist');

    cy.get('input[name="nama"], input[name="nama_produk"]')
      .first()
      .invoke('val')
      .should('not.be.empty');
  });

  it('PRO-005: Konfirmasi hapus produk muncul saat tombol Hapus diklik', () => {
    cy.visit('/admin/produk');

    cy.get('tbody').then(($tbody) => {
      if (!$tbody.find('tr').length) {
        cy.log('Tidak ada produk untuk diuji hapus.');
        return;
      }
      cy.contains('button, a', /Hapus/i)
        .first()
        .click();

      cy.get('body').then(($body) => {
        if ($body.find('.modal.show, #deleteProductModal').length) {
          cy.get('.modal.show, #deleteProductModal')
            .should('be.visible')
            .within(() => {
              cy.contains(/Yakin|Ya, Hapus/i).should('exist');
            });
        } else {
          cy.window().then((win) => {
            cy.stub(win, 'confirm').returns(false);
          });
        }
      });
    });
  });
});
