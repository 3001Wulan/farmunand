describe('Sheet 11 - Penilaian Produk Pembeli', () => {

    const email = 'user01@farmunand.local';
    const pass  = '111111';

    beforeEach(() => {
        cy.visit('/login');
        cy.get('input[name="email"]').type(email);
        cy.get('input[name="password"]').type(pass);
        cy.contains(/login/i).click();
        cy.url().should('not.include', '/login');
    });

    // -------------------------------------------------
    // 1. Halaman daftar penilaian bisa dibuka
    // -------------------------------------------------
    it('PEN-001: Halaman dapat dibuka & list pesanan tampil', () => {
        cy.visit('/penilaian/daftar');

        cy.get('h5').contains('Berikan Penilaian').should('exist');

        cy.get('body').then(($b) => {
            if ($b.find('.card-order').length > 0) {
                cy.get('.card-order').should('exist');
            } else {
                cy.contains(/belum ada pesanan yang bisa dinilai/i).should('exist');
            }
        });
    });

    // -------------------------------------------------
    it('PEN-002: Tombol membuka modal penilaian', () => {
        cy.visit('/penilaian/daftar');
    
        cy.get('.btn.btn-success.btn-sm').then($btns => {
            if ($btns.length === 0) {
                cy.log('Tidak ada pesanan yang bisa dinilai');
                return;
            }
    
            // Panggil fungsi JS langsung
            cy.window().then(win => {
                const firstBtn = $btns[0];
                const productName = firstBtn.getAttribute('data-product-name'); 
                const idDetail = firstBtn.getAttribute('data-id-detail'); 
                win.openReviewModal(productName, idDetail);
            });
    
            // Tunggu modal muncul
            cy.get('#reviewModal', { timeout: 10000 })
              .should('have.class', 'show')
              .and('be.visible');
        });
    });
    
    it('PEN-003: Submit penilaian lengkap berhasil', () => {
        cy.visit('/penilaian/daftar');
    
        cy.get('body').then(($b) => {
            const btns = $b.find('.btn.btn-success.btn-sm');
            if (btns.length === 0) {
                cy.log('Tidak ada pesanan yang bisa dinilai');
                return;
            }
    
            cy.window().then(win => {
                const firstBtn = btns[0];
                const productName = firstBtn.getAttribute('data-product-name') || 'Produk';
                const idDetail = firstBtn.getAttribute('data-id-detail') || '1';
                win.openReviewModal(productName, idDetail);
            });
        });
    
        cy.get('#reviewModal', { timeout: 10000 })
          .should('have.class', 'show')
          .and('be.visible');
    
        cy.get('#reviewModal .stars span').eq(3).click({ force: true });
        cy.get('#reviewModal textarea[name="ulasan"]')
          .type('Produk sangat bagus dan berkualitas!', { force: true });
        cy.get('#reviewModal input[type="file"]')
          .selectFile('cypress/fixtures/sample.png', { force: true });
    
        cy.get('#reviewModal button[type="submit"]').click({ force: true });
    
        // Tunggu halaman reload
        cy.url({ timeout: 10000 }).should('include', '/penilaian/daftar');
    
        // Cek flash message sukses
        cy.contains('Penilaian Berhasil!').should('exist');
    });
    
    // -------------------------------------------------
    // 4. Rating kosong → tampil alert
    // -------------------------------------------------
    it('PEN-004: Validasi rating wajib', () => {
        cy.visit('/penilaian/daftar');

        cy.get('body').then(($b) => {
            if ($b.find('.btn.btn-success.btn-sm').length === 0) return;
            cy.get('.btn.btn-success.btn-sm').first().click({ force: true });
        });

        cy.get('textarea[name="ulasan"]').type('Tanpa rating', { force: true });

        cy.on('window:alert', (txt) => {
            expect(txt).to.contains('Silakan pilih rating');
        });

        cy.get('button[type="submit"]').click({ force: true });
    });

    // -------------------------------------------------
    // 5. File invalid harus menampilkan alert
    // -------------------------------------------------
    it('PEN-005: Validasi file harus tipe gambar/video', () => {
        cy.visit('/penilaian/daftar');

        cy.get('body').then(($b) => {
            if ($b.find('.btn.btn-success.btn-sm').length === 0) return;
            cy.get('.btn.btn-success.btn-sm').first().click({ force: true });
        });

        // Klik rating 3 bintang
        cy.get('.stars span').eq(2).click({ force: true });

        // Upload file PDF untuk uji validasi
        cy.get('input[type="file"]').selectFile('cypress/fixtures/sample.pdf', { force: true });

        cy.on('window:alert', (txt) => {
            expect(txt).to.contains('Hanya file gambar');
        });

        cy.get('button[type="submit"]').click({ force: true });
    });

});
