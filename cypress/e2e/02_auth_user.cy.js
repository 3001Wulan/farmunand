describe('Sheet 01B - Auth User', () => {
  const userEmail = 'user01@farmunand.local';
  const userPassword = '111111';

  it('USR-001: Halaman login bisa dibuka', () => {
    cy.visit('/login');

    cy.get('form').should('exist');
    cy.get('input[name="email"]').should('exist');
    cy.get('input[name="password"]').should('exist');
    cy.contains(/login/i).should('exist');
  });

  it('USR-002: Login sukses sebagai user', () => {
    cy.visit('/login');

    cy.get('input[name="email"]').type(userEmail);
    cy.get('input[name="password"]').type(userPassword);

    cy.contains('button', 'Login').click();

    cy.url().should('include', '/dashboarduser');

    cy.contains('Dashboard').should('exist');
  });

  it('USR-003: Login gagal dengan password salah', () => {
    cy.visit('/login');

    cy.get('input[name="email"]').type(userEmail);
    cy.get('input[name="password"]').type('password_salah_banget');

    cy.contains('button', 'Login').click();

    cy.url().should('include', '/login');

    cy.contains(/Email atau password salah\./i).should('exist');
  });


  it('USR-FORGOT-001: Halaman lupa password bisa dibuka', () => {
    cy.visit('/forgot-password');

    cy.get('form').should('exist');
    cy.get('input[name="email"]').should('exist');
    cy.contains(/kirim link reset|reset password|kirim/i).should('exist');
  });

    it('USR-FORGOT-002: Lupa password mengirimkan notifikasi untuk email terdaftar', () => {
    const userEmail = 'user01@farmunand.local';

    cy.visit('/forgot-password');

    cy.get('input[name="email"]').clear().type(userEmail);

    cy.contains('button', /kirim|reset|submit/i).click();
    cy.url({ timeout: 10000 }).should('include', '/login');
    cy.get('body').then(($body) => {
      const selector = '.alert, .alert-success, .alert-info, .flash-message, #flash-message';

      if ($body.find(selector).length) {
        cy.get(selector)
          .first()
          .invoke('text')
          .then((text) => {
            const lower = text.toLowerCase();
            expect(lower).to.match(/email/);      
            expect(lower).to.match(/reset|password/); 
          });
      } else {
        cy.contains(/reset.*email|email.*reset|cek.*email/i).should('exist');
      }
    });
  });


});
