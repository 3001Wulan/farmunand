/// <reference types="cypress" />

describe('Admin Produk - CRUD & Search', () => {
    const adminEmail = 'admin@farmunand.local';
    const adminPassword = '111111';
    let randomProductName = '';

    beforeEach(() => {
        cy.visit('/login');

        cy.get('input[name="email"]').clear().type(adminEmail);
        cy.get('input[name="password"]').clear().type(adminPassword);
        cy.contains(/login/i).click();

        cy.url().should('include', '/dashboard');
    });

    // -----------------------------
    // 1. List Produk & Search/Filter
    // -----------------------------
    it('PRD-001: List produk tampil & search/filter aman', () => {
        cy.visit('/admin/produk');

        // Pastikan tabel ada
        cy.get('table tbody tr').should('exist');

        // -------------------------
        // Search Produk
        // -------------------------
        cy.get('input[name="keyword"]').clear().type('Produk');
        cy.get('button[type="submit"]').contains(/filter/i).click();

        cy.get('table tbody tr').then(($rows) => {
            if ($rows.first().text().includes('Tidak ada produk ditemukan')) {
                cy.log('Tidak ada produk hasil pencarian "Produk"');
            } else {
                cy.wrap($rows).each(($row) => {
                    cy.wrap($row).should('contain.text', 'Produk');
                });
            }
        });

        // -------------------------
        // Filter Kategori
        // -------------------------
        const kategori = 'Minuman';
        cy.get('select[name="kategori"]').select(kategori);
        cy.get('button[type="submit"]').contains(/filter/i).click();

        cy.get('table tbody tr').then(($rows) => {
            if ($rows.first().text().includes('Tidak ada produk ditemukan')) {
                cy.log(`Tidak ada produk kategori "${kategori}"`);
            } else {
                cy.wrap($rows).each(($row) => {
                    cy.wrap($row).should('contain.text', kategori);
                });
            }
        });
    });
    // -----------------------------
    // 2. Tambah Produk Baru
    // -----------------------------
    it('PRD-002: Tambah produk baru berhasil', () => {
        cy.visit('/admin/produk/create');

        randomProductName = `Produk Test ${Math.floor(Math.random() * 10000)}`;

        cy.get('input[name="nama_produk"]').type(randomProductName);
        cy.get('textarea[name="deskripsi"]').type('Deskripsi produk test');
        cy.get('input[name="harga"]').type('100000');
        cy.get('input[name="stok"]').type('10');
        cy.get('select[name="kategori"]').select('Makanan');

        // Upload foto
        cy.get('input[name="foto"]').selectFile('cypress/fixtures/sample.png');

        cy.get('button[type="submit"]').click();

        cy.contains('Produk berhasil ditambahkan!').should('exist');
        cy.visit('/admin/produk');
        cy.contains(randomProductName).should('exist');
    });

    // -----------------------------
    // 3. Edit Produk
    // -----------------------------
    it('PRD-003: Edit produk berhasil', () => {
        cy.visit('/admin/produk');

        cy.contains('tr', randomProductName).within(() => {
            cy.get('a').contains('Edit').click();
        });

        const updatedName = `${randomProductName} Updated`;

        cy.get('input[name="nama_produk"]').clear().type(updatedName);
        cy.get('input[name="harga"]').clear().type('120000');
        cy.get('button[type="submit"]').click();

        cy.contains('Produk berhasil diperbarui!').should('exist');
        cy.visit('/admin/produk');
        cy.contains(updatedName).should('exist');

        randomProductName = updatedName;
    });

    // -----------------------------
    // 4. Hapus Produk via Modal
    // -----------------------------
    it('PRD-004: Hapus produk berhasil', () => {
        cy.visit('/admin/produk');

        cy.contains('tr', randomProductName).within(() => {
            cy.get('.btn-delete-product').click();
        });

        // Modal muncul
        cy.get('#deleteProductModal').should('be.visible');
        cy.get('#modalProductName').should('contain.text', randomProductName);

        // Konfirmasi hapus
        cy.get('#form-delete-product').submit();

        cy.contains('Produk berhasil dihapus!').should('exist');
        cy.visit('/admin/produk');
        cy.contains(randomProductName).should('not.exist');
    });
});
