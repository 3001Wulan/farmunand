// Tes ini memverifikasi akses dan konten halaman Dashboard Pembeli/User.

describe('Sheet 06 - Dashboard Pembeli', () => {
    // Data login user (GANTI dengan data nyata di DB Anda)
    // PASTIKAN data ini BENAR-BENAR ada dan berstatus USER/PEMBELI di DB
    // Catatan: Data ini harus memiliki beberapa data pesanan untuk hasil metrik yang valid.
    const userEmail = 'user01@farmunand.local'; // <--- HARAP GANTI DENGAN EMAIL PEMBELI AKTUAL
    const userPassword = '111111'; // <--- HARAP GANTI DENGAN PASSWORD AKTUAL
    const expectedUsername = 'user01'; // <--- HARAP GANTI DENGAN USERNAME AKTUAL

    // Selector Sidebar berdasarkan HTML yang diberikan
    const sidebarSelector = '.sidebarUser';

    // USR-DASH-001: Memastikan user bisa mengakses dashboard setelah login
    beforeEach(() => {
        // 1. Lakukan Login User
        cy.visit('/login'); 

        cy.get('input[name="email"]').type(userEmail);
        cy.get('input[name="password"]').type(userPassword);
        cy.contains('button', 'Login').click();

        // **HOOK DEBUG INI SEMENTARA DIKOMENTARI UNTUK MEMVERIFIKASI REDIRECT**
        /*
        cy.get('body').then(($body) => {
            if ($body.find('.alert.alert-danger, .text-danger, .is-invalid').length) {
                // Jika ada pesan error login, log kegagalan dan hentikan
                throw new Error("Login Gagal: Cek kembali userEmail dan userPassword.");
            }
        });
        */

        // 2. Verifikasi Redirect ke Dashboard
        cy.url().should('include', '/dashboarduser'); 
        
        // 3. Verifikasi sambutan/username (Diperbaiki untuk mencari di Sidebar)
        
        // Pastikan sidebar dimuat dan terlihat
        cy.get(sidebarSelector, { timeout: 10000 }).should('be.visible'); 
        
        // Cari username di dalam Sidebar (menggunakan p.mb-0.fw-bold.text-white)
        cy.get(sidebarSelector).contains('p.fw-bold', new RegExp(expectedUsername.replace(/[\/\\^$*+?.()|[\]{}]/g, '\\$&'), 'i'))
          .should('exist');
          
        // Verifikasi sambutan di konten utama (welcome-card)
        cy.get('.welcome-card h4').contains(new RegExp(expectedUsername, 'i')).should('exist');
    });

    // USR-DASH-002: Memastikan semua kartu metrik pesanan tampil dan berisi angka
    it('USR-DASH-002: Kartu metrik pesanan tampil dan berisi data', () => {
        // PERBAIKAN: Menggunakan selector Bootstrap berbasis konten dan warna teks
        
        // Verifikasi Card Pesanan Sukses (Selesai)
        cy.contains('.card-body', /Pesanan Sukses/i).within(() => {
            // Memastikan ikon dan teks warna sukses
            cy.get('i.text-success').should('exist');
            // Memastikan angka metrik ada (ada di p.text-success)
            cy.get('p.text-success').invoke('text').should('match', /^\d+$/); 
        });

        // Verifikasi Card Pesanan Pending
        cy.contains('.card-body', /Pending/i).within(() => {
            // Memastikan ikon dan teks warna warning
            cy.get('i.text-warning').should('exist');
            // Memastikan angka metrik ada (ada di p.text-warning)
            cy.get('p.text-warning').invoke('text').should('match', /^\d+$/);
        });

        // Verifikasi Card Pesanan Dibatalkan
        cy.contains('.card-body', /Dibatalkan/i).within(() => {
            // Memastikan ikon dan teks warna danger
            cy.get('i.text-danger').should('exist');
            // Memastikan angka metrik ada (ada di p.text-danger)
            cy.get('p.text-danger').invoke('text').should('match', /^\d+$/);
        });
    });

    // USR-DASH-003: Memastikan bagian Produk Terbaru tampil dan memuat setidaknya satu produk
    it('USR-DASH-003: Daftar Produk Terbaru dimuat dengan detail', () => {
        // PERBAIKAN: Judul yang benar adalah "Rekomendasi Produk" dan berada di .card-header
        cy.contains('.card-header', /Rekomendasi Produk/i).should('exist');

        // Selector utama untuk daftar produk (Container adalah div.card-body di bawah header)
        const produkContainerSelector = '.card-body.d-flex.gap-3.flex-wrap'; 

        // Pastikan container produk ada dan berisi setidaknya satu card produk
        cy.get(produkContainerSelector)
            .should('exist')
            .find('.product-card') // Cari card produk dengan class product-card
            .its('length') // Ambil jumlah card
            .should('be.gte', 1); // Harusnya ada minimal 1 produk (jika DB punya data)

        // Cek detail produk pada produk pertama di daftar
        cy.get(produkContainerSelector)
            .find('.product-card')
            .first()
            .within(() => {
                // Nama produk harus ada dan tidak kosong (h6.card-title)
                cy.get('h6.card-title').should('exist').invoke('text').should('not.be.empty');
                
                // Harga produk harus ada dan diformat (p.text-success)
                cy.get('p.text-success').should('exist').invoke('text').should('match', /Rp\s\d{1,3}(\.\d{3})*/); 
                
                // Rating harus ditampilkan (minimal 5 bintang icon)
                cy.get('.rating-stars i.bi-star-fill, i.bi-star-half, i.bi-star').should('have.length', 5);
                
                // Tombol Beli harus ada dan memiliki data-id (mengganti 'Lihat Detail' dari versi lama)
                cy.contains('button.btn-buy', /Beli/i)
                    .should('have.attr', 'data-id')
                    .and('match', /^\d+$/); 
            });
    });
    
    // USR-DASH-004: Akses ditolak jika user logout (melindungi endpoint dashboard)
    it('USR-DASH-004: Akses ditolak jika tidak login', () => {
        // Hapus session/logout (asumsi ada tombol logout di sidebar atau endpoint /logout)
        cy.visit('/logout'); 

        // Coba akses dashboard langsung
        cy.visit('/dashboarduser'); 

        // Harusnya diarahkan kembali ke halaman login
        cy.url().should('match', /\/login/); // Match untuk /login atau /index.php/login
        cy.contains(/Silakan login dulu|Anda harus login/i).should('exist');
    });
});