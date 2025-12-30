/// <reference types="cypress" />

describe('Admin Produk - CRUD & Search', () => {
    const adminEmail = 'admin@farmunand.local';
    const adminPassword = '111111';

    beforeEach(() => {
        cy.visit('/login');

        cy.get('input[name="email"]').clear().type(adminEmail);
        cy.get('input[name="password"]').clear().type(adminPassword);
        cy.contains(/login/i).click();

        cy.url().should('include', '/dashboard');
    });

    it('PRD-001: List produk tampil & fitur search/filter aman', () => {
        cy.visit('/admin/produk');

        cy.get('table tbody').should('exist');

        cy.get('input[name="keyword"]').clear().type('Produk');
        cy.get('button[type="submit"]').contains(/filter/i).click();

        cy.get('table tbody tr').then(($rows) => {
            if ($rows.first().text().includes('Tidak ada produk')) {
                cy.log('Tidak ada produk sesuai keyword');
            } else {
                cy.wrap($rows).each(($row) => {
                    cy.wrap($row).invoke('text').should('include', 'Produk');
                });
            }
        });

        const kategori = 'Minuman';
        cy.get('select[name="kategori"]').select(kategori);
        cy.get('button[type="submit"]').contains(/filter/i).click();

        cy.get('table tbody tr').then(($rows) => {
            if ($rows.first().text().includes('Tidak ada produk')) {
                cy.log(`Tidak ada produk kategori ${kategori}`);
            } else {
                cy.wrap($rows).each(($row) => {
                    cy.wrap($row).invoke('text').should('include', kategori);
                });
            }
        });
    });


    it('PRD-002: CRUD Produk (Tambah, Edit, Hapus) berhasil', () => {

        cy.visit('/admin/produk/create');

        const productName = `Produk Test ${Date.now()}`;

        cy.get('input[name="nama_produk"]').type(productName);
        cy.get('textarea[name="deskripsi"]').type('Deskripsi produk testing');
        cy.get('input[name="harga"]').type('100000');
        cy.get('input[name="stok"]').type('10');
        cy.get('select[name="kategori"]').select('Makanan');
        cy.get('input[name="foto"]').selectFile('cypress/fixtures/sample.png');

        cy.get('button[type="submit"]').click();
        cy.contains('Produk berhasil ditambahkan!').should('exist');

 
        cy.visit('/admin/produk');
        cy.contains('tr', productName).should('exist');

        cy.contains('tr', productName).within(() => {
            cy.contains('Edit').click();
        });

        const updatedName = `${productName} Updated`;

        cy.get('input[name="nama_produk"]').clear().type(updatedName);
        cy.get('input[name="harga"]').clear().type('120000');
        cy.get('button[type="submit"]').click();

        cy.contains('Produk berhasil diperbarui!').should('exist');

        cy.visit('/admin/produk');
        cy.contains(updatedName).should('exist');

 
        cy.contains('tr', updatedName).within(() => {
            cy.get('.btn-delete-product').click();
        });

        cy.get('#deleteProductModal').should('be.visible');
        cy.get('#modalProductName').should('contain.text', updatedName);

        cy.get('#form-delete-product').should('exist').submit();

        cy.contains('Produk berhasil dihapus!').should('exist');

        cy.visit('/admin/produk');
        cy.contains(updatedName).should('not.exist');
    });
});
