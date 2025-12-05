// cypress/e2e/05_dashboard_admin.cy.js

// Tes ini memverifikasi akses dan konten halaman Dashboard Admin.

describe('Sheet 05 - Dashboard Admin', () => {
    // Data login admin (GANTI dengan data nyata di DB Anda)
    // PASTIKAN data ini BENAR-BENAR ada dan berstatus ADMIN di DB
    const adminEmail = 'admin@farmunand.local'; // GANTI SINI DENGAN DATA VALID
    const adminPassword = '111111'; // GANTI SINI DENGAN DATA VALID

    // ADM-DASH-001: Memastikan admin bisa mengakses dashboard setelah login
    beforeEach(() => {
        // 1. Lakukan Login Admin
        cy.visit('/login'); 

        cy.get('input[name="email"]').type(adminEmail);
        cy.get('input[name="password"]').type(adminPassword);
        cy.contains('button', 'Login').click();

        // 2. Verifikasi Redirect ke Dashboard
        cy.url().should('include', '/dashboard'); 
        // Verifikasi judul Dashboard muncul
        cy.contains(/Dashboard( Admin)?/i, { timeout: 10000 }).should('exist');
    });

    // ADM-DASH-002: Memastikan semua kartu statistik utama tampil
    it('ADM-DASH-002: Semua kartu statistik utama tampil', () => {
        // Verifikasi bahwa container untuk kartu statistik muncul
        cy.get('.row.g-4').should('exist');
        
        // Cek Keberadaan Teks di Kartu
        cy.contains('h5', /Total Produk/i).should('exist');
        cy.contains('h5', /Total User/i).should('exist');
        cy.contains('h5', /Transaksi Hari Ini/i).should('exist');
        cy.contains('h5', /Penjualan Bulan Ini/i).should('exist');
        cy.contains('h5', /Stok Rendah/i).should('exist');
        cy.contains('h5', /Total Pesanan/i).should('exist'); 
    });

    // ADM-DASH-003: Memastikan navigasi/sidebar muncul dan nama user tampil
    it('ADM-DASH-003: Nama admin ditampilkan dan menu sidebar muncul', () => {
        // Selector yang tepat berdasarkan HTML Anda
        const sidebarSelector = '.sidebarAdmin'; 

        // 1. Verifikasi Nama dan Role Admin
        // Memastikan elemen p.fw-bold ada dan mengandung teks 'Admin' (dari role)
        cy.get('p.fw-bold').contains(/admin/i).should('exist'); 
        
        // 2. Perbaikan Stabilitas Sidebar
        
        // Pastikan elemen sidebar utama dimuat dan terlihat
        cy.get(sidebarSelector).should('be.visible'); 
        
        // Cek semua menu utama di dalam sidebar (disesuaikan dengan teks di HTML)
        
        // Profil
        cy.get(sidebarSelector).contains('a.sidebar-link', /Profil/i).should('exist');
        
        // Dashboard
        cy.get(sidebarSelector).contains('a.sidebar-link', /Dashboard/i).should('exist');
        
        // Produk (Pengganti 'Manajemen Produk')
        cy.get(sidebarSelector).contains('a.sidebar-link', /Produk/i).should('exist');
        
        // Pesanan
        cy.get(sidebarSelector).contains('a.sidebar-link', /Pesanan/i).should('exist');
        
        // Manajemen Akun User (Pengganti 'Manajemen User')
        cy.get(sidebarSelector).contains('a.sidebar-link', /Manajemen Akun User/i).should('exist');
        
        // Laporan Penjualan
        cy.get(sidebarSelector).contains('a.sidebar-link', /Laporan Penjualan/i).should('exist');

        // Pastikan link Log Out ada
        cy.get(sidebarSelector).contains('a.logout-btn', /Log Out/i).should('exist');
    });

    // ADM-DASH-004: Akses ditolak jika user logout
    it('ADM-DASH-004: Akses ditolak jika tidak login', () => {
        // Hapus session/logout
        cy.visit('/logout'); 

        // Coba akses dashboard langsung
        cy.visit('/dashboard'); 

        // Harusnya diarahkan kembali ke halaman login
        cy.url().should('match', /\/login/); 
        // Verifikasi pesan 'Silakan login dulu.' atau variannya muncul
        cy.contains(/Silakan login dulu|Anda harus login/i).should('exist');
    });
});