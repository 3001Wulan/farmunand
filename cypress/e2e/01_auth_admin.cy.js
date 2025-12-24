describe('Sheet 01A - Auth Admin', () => {
  const adminEmail = 'admin@farmunand.local';   
  const adminPassword = '111111';       

  it('ADM-001: Halaman login bisa dibuka', () => {
    cy.visit('/login');

    cy.get('form').should('exist');
    cy.get('input[name="email"]').should('exist');
    cy.get('input[name="password"]').should('exist');
    cy.contains(/login/i).should('exist');
  });

  it('ADM-002: Login sukses sebagai admin', () => {
    cy.visit('/login');

    cy.get('input[name="email"]').clear().type(adminEmail);
    cy.get('input[name="password"]').clear().type(adminPassword);

    cy.contains('button', 'Login').click();

    cy.url().should((url) => {
      expect(url).to.match(/\/dashboard(\b|\/|\?)/);
    });

    cy.contains(/Dashboard/i).should('exist');
  });

  it('ADM-003: Login gagal dengan password salah', () => {
    cy.visit('/login');

    cy.get('input[name="email"]').clear().type(adminEmail);
    cy.get('input[name="password"]').clear().type('password_salah_banget');

    cy.contains('button', 'Login').click();

    cy.url().should('include', '/login');

    cy.contains(/Email atau password salah|Akun Anda terkunci sementara/i)
      .should('exist');
  });

  it('ADM-004: Logout admin mengembalikan ke halaman login', () => {
    cy.visit('/login');
    cy.get('input[name="email"]').type(adminEmail);
    cy.get('input[name="password"]').type(adminPassword);
    cy.contains('button', 'Login').click();

    cy.url().should('include', '/dashboard');

    cy.visit('/logout');

    cy.url().should('match', /\/login/);
  });

});
