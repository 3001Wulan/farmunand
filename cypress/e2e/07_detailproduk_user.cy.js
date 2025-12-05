// Tes ini memverifikasi akses dan konten halaman Detail Produk.

describe('Sheet 07 - Detail Produk Pembeli', () => {
    // Data login user (PASTIKAN VALID)
    const userEmail = 'user01@farmunand.local'; 
    const userPassword = '111111'; 
    
    // Data Produk (GANTI dengan ID produk yang BENAR-BENAR ADA di DB)
    // PASTIKAN ID ini memiliki data ulasan untuk menguji DET-PROD-004
    const existingProductId = 1;
    // Data Produk Non-Eksis (untuk menguji error 404)
    const nonExistentProductId = 9999; 

    // USR-DPROD-001: Memastikan user bisa mengakses halaman detail produk
    beforeEach(() => {
        // 1. Lakukan Login User (memastikan sesi aktif)
        cy.visit('/login'); 

        cy.get('input[name="email"]').type(userEmail);
        cy.get('input[name="password"]').type(userPassword);
        cy.contains('button', 'Login').click();
        
        // Kita tidak perlu verifikasi username di sini, cukup pastikan redirect sukses
        cy.url().should('not.include', '/login'); 
    });

    // DET-PROD-002: Memastikan konten produk utama dimuat
    it('DET-PROD-002: Detail produk (nama, harga, deskripsi, aksi) tampil dengan benar', () => {
        cy.visit(`/detailproduk/${existingProductId}`);
        cy.url().should('include', `/detailproduk/${existingProductId}`);

        // PERBAIKAN: Verifikasi Judul Halaman menggunakan cy.title()
        cy.title().should('include', 'Detail Produk');

        // 1. Cek Judul Produk (Menggunakan .product-title)
        cy.get('.product-title').invoke('text').should('not.be.empty');

        // 2. Cek Harga Produk (Menggunakan .product-price dan format Rp)
        cy.get('.product-price').invoke('text').should('match', /Rp\s\d{1,3}(\.\d{3})*/);

        // 3. Cek Bagian Deskripsi Produk (Menggunakan .section-title)
        cy.contains('.section-title', /Deskripsi Produk/i).should('exist');

        // 4. Cek Tombol Aksi (Menggunakan form action buttons)
        cy.get('#actionForm').within(() => {
            // Tombol Masukkan Keranjang
            cy.contains('button', /Masukkan Keranjang/i).should('be.visible');
            // Tombol Checkout
            cy.contains('button', /Checkout/i).should('be.visible');
            // Input Kuantitas
            cy.get('input#qty').should('have.attr', 'min', '1');
        });
    });

    // DET-PROD-003: Memastikan halaman 404 muncul jika produk tidak ditemukan
    it('DET-PROD-003: Menampilkan halaman tidak ditemukan (404) untuk ID yang salah', () => {
        // FailOnStatusCode diperlukan karena CodeIgniter melempar 404/500
        cy.visit(`/detailproduk/${nonExistentProductId}`, { failOnStatusCode: false });

        // Controller menggunakan PageNotFoundException, memverifikasi status dan pesan
        cy.request({
            url: `/detailproduk/${nonExistentProductId}`,
            failOnStatusCode: false,
        }).then((response) => {
            // Memastikan status code adalah 404 (atau 500 jika error CodeIgniter)
            expect(response.status).to.be.oneOf([404, 500]); 
        });
        
        // Verifikasi pesan "Produk tidak ditemukan" muncul di body halaman
        cy.contains(/Produk.*tidak ditemukan|Page Not Found/i, { timeout: 10000 }).should('exist');
    });

    // DET-PROD-004: Memastikan bagian ulasan (reviews) tampil
    it('DET-PROD-004: Bagian ulasan tampil dengan data reviewer atau pesan kosong', () => {
        cy.visit(`/detailproduk/${existingProductId}`);
        
        // Cek Judul Bagian Ulasan (Menggunakan .section-title)
        cy.contains('.section-title', /Penilaian Produk/i).should('exist');
        
        // Selector untuk item ulasan
        const reviewSelector = '.review-item';

        // Mendapatkan elemen body section Penilaian Produk dan menggunakan .then() untuk logika bersyarat
        cy.get('.section-body').last().then(($sectionBody) => {
            
            // Menggunakan jQuery (.find()) pada subjek yang benar ($sectionBody)
            if ($sectionBody.find(reviewSelector).length) {
                // KASUS 1: Ada ulasan. Cek detail ulasan pertama.
                // Menggunakan cy.wrap() untuk melanjutkan rantai Cypress pada elemen jQuery
                cy.wrap($sectionBody).find(reviewSelector).first().within(() => {
                    // Cek nama reviewer (class .review-name)
                    cy.get('.review-name').should('exist').invoke('text').should('not.be.empty'); 
                    
                    // Cek rating bintang (class .review-stars dengan 5 ikon bintang)
                    cy.get('.review-stars i.bi-star-fill, i.bi-star-half, i.bi-star').should('have.length', 5);
                    
                    // Cek teks ulasan (class .review-text) jika ada
                    // Menggunakan .should('be.visible') jika ulasan ada, atau .should('exist') jika ulasan kosong
                    cy.get('.review-text').should('exist'); 
                });
            } else {
                // KASUS 2: Tidak ada ulasan. Pastikan pesan "Belum ada ulasan" muncul.
                cy.wrap($sectionBody).contains('.text-muted', /Belum ada ulasan/i).should('exist');
            }
        });
    });
});