describe('Sheet 05 - Dashboard Admin', () => {
    const adminEmail = 'admin@farmunand.local'; 
    const adminPassword = '111111'; 

    beforeEach(() => {
        cy.visit('/login'); 

        cy.get('input[name="email"]').type(adminEmail);
        cy.get('input[name="password"]').type(adminPassword);
        cy.contains('button', 'Login').click();

        cy.url().should('include', '/dashboard'); 
        cy.contains(/Dashboard( Admin)?/i, { timeout: 10000 }).should('exist');
    });

    it('ADM-DASH-002: Semua kartu statistik utama tampil', () => {
        cy.get('.row.g-4').should('exist');
        cy.contains('h5', /Total Produk/i).should('exist');
        cy.contains('h5', /Total User/i).should('exist');
        cy.contains('h5', /Transaksi Hari Ini/i).should('exist');
        cy.contains('h5', /Penjualan Bulan Ini/i).should('exist');
        cy.contains('h5', /Stok Rendah/i).should('exist');
        cy.contains('h5', /Total Pesanan/i).should('exist'); 
    });

    it('ADM-DASH-003: Nama admin ditampilkan dan menu sidebar muncul', () => {
        const sidebarSelector = '.sidebarAdmin'; 
        cy.get('p.fw-bold').contains(/admin/i).should('exist'); 
        cy.get(sidebarSelector).should('be.visible'); 

        cy.get(sidebarSelector).contains('a.sidebar-link', /Profil/i).should('exist');
        cy.get(sidebarSelector).contains('a.sidebar-link', /Dashboard/i).should('exist');
        cy.get(sidebarSelector).contains('a.sidebar-link', /Produk/i).should('exist');
        cy.get(sidebarSelector).contains('a.sidebar-link', /Pesanan/i).should('exist');
        cy.get(sidebarSelector).contains('a.sidebar-link', /Manajemen Akun User/i).should('exist');
        cy.get(sidebarSelector).contains('a.sidebar-link', /Laporan Penjualan/i).should('exist');
        cy.get(sidebarSelector).contains('a.logout-btn', /Log Out/i).should('exist');
    });

    it('ADM-DASH-004: Akses ditolak jika tidak login', () => {
        cy.visit('/logout'); 
        cy.visit('/dashboard'); 
        cy.url().should('match', /\/login/); 
        cy.contains(/Silakan login dulu|Anda harus login/i).should('exist');
    });
});