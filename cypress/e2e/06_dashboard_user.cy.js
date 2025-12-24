describe('Sheet 06 - Dashboard Pembeli', () => {

  beforeEach(() => {
    
    cy.setCookie('ci_session', 'dummy-session');

    
    cy.request({
      method: 'GET',
      url: '/test-set-session-user', 
      failOnStatusCode: false
    });

    cy.visit('/dashboarduser');
  });

  
  it('USR-DASH-001: Dashboard Pembeli bisa diakses', () => {
    cy.get('body').should('contain.text', 'Dashboard');
  });

  
  it('USR-DASH-002: Kartu metrik pesanan tampil', () => {
    cy.get('body').should('contain.text', 'Pesanan');
  });

  
  it('USR-DASH-003: Daftar produk rekomendasi tampil', () => {
    cy.get('body').should('contain.text', 'Produk');
  });

  
  it('USR-DASH-004: Akses dashboard ditolak jika belum login', () => {
    cy.clearCookies();
    cy.visit('/dashboarduser');

    cy.url().should('include', '/login');
  });

});
