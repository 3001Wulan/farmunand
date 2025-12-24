describe('Sheet 07 - Detail Produk Pembeli', () => {
    const userEmail = 'user01@farmunand.local'; 
    const userPassword = '111111'; 
    
    const existingProductId = 1;
    const nonExistentProductId = 9999; 

    beforeEach(() => {
        cy.visit('/login'); 

        cy.get('input[name="email"]').type(userEmail);
        cy.get('input[name="password"]').type(userPassword);
        cy.contains('button', 'Login').click();
        
        cy.url().should('not.include', '/login'); 
    });
    it('DET-PROD-002: Detail produk (nama, harga, deskripsi, aksi) tampil dengan benar', () => {
        cy.visit(`/detailproduk/${existingProductId}`);
        cy.url().should('include', `/detailproduk/${existingProductId}`);
        cy.title().should('include', 'Detail Produk');
        cy.get('.product-title').invoke('text').should('not.be.empty');
        cy.get('.product-price').invoke('text').should('match', /Rp\s\d{1,3}(\.\d{3})*/);
        cy.contains('.section-title', /Deskripsi Produk/i).should('exist');
        cy.get('#actionForm').within(() => {
            cy.contains('button', /Masukkan Keranjang/i).should('be.visible');
            cy.contains('button', /Checkout/i).should('be.visible');
            cy.get('input#qty').should('have.attr', 'min', '1');
        });
    });
    it('DET-PROD-003: Menampilkan halaman tidak ditemukan (404) untuk ID yang salah', () => {
        cy.visit(`/detailproduk/${nonExistentProductId}`, { failOnStatusCode: false });
        cy.request({
            url: `/detailproduk/${nonExistentProductId}`,
            failOnStatusCode: false,
        }).then((response) => {
            expect(response.status).to.be.oneOf([404, 500]); 
        });
        cy.contains(/Produk.*tidak ditemukan|Page Not Found/i, { timeout: 10000 }).should('exist');
    });

    it('DET-PROD-004: Bagian ulasan tampil dengan data reviewer atau pesan kosong', () => {
        cy.visit(`/detailproduk/${existingProductId}`);
        
        cy.contains('.section-title', /Penilaian Produk/i).should('exist');
        
        const reviewSelector = '.review-item';
        cy.get('.section-body').last().then(($sectionBody) => {
            if ($sectionBody.find(reviewSelector).length) {
                cy.wrap($sectionBody).find(reviewSelector).first().within(() => {
                    cy.get('.review-name').should('exist').invoke('text').should('not.be.empty'); 
            
                    cy.get('.review-stars i.bi-star-fill, i.bi-star-half, i.bi-star').should('have.length', 5);
                    cy.get('.review-text').should('exist'); 
                });
            } else {
                cy.wrap($sectionBody).contains('.text-muted', /Belum ada ulasan/i).should('exist');
            }
        });
    });
});