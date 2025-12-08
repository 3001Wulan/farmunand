// cypress/e2e/01_auth_admin.cy.js

describe('Sheet 01A - Auth Admin', () => {
  // GANTI sesuai data admin nyata di DB
  const adminEmail = 'admin@farmunand.local';   // <- sesuaikan
  const adminPassword = '111111';       // <- sesuaikan

  // ADM-001: Halaman login bisa dibuka
  it('ADM-001: Halaman login bisa dibuka', () => {
    cy.visit('/login');

    cy.get('form').should('exist');
    cy.get('input[name="email"]').should('exist');
    cy.get('input[name="password"]').should('exist');
    cy.contains(/login/i).should('exist');
  });

  // ADM-002: Login sukses sebagai admin
  it('ADM-002: Login sukses sebagai admin', () => {
    cy.visit('/login');

    cy.get('input[name="email"]').clear().type(adminEmail);
    cy.get('input[name="password"]').clear().type(adminPassword);

    cy.contains('button', 'Login').click();

    // Redirect ke dashboard admin
    cy.url().should((url) => {
      // contoh: http://localhost:8080/index.php/dashboard
      expect(url).to.match(/\/dashboard(\b|\/|\?)/);
    });

    // Cek teks "Dashboard" muncul di halaman
    cy.contains(/Dashboard/i).should('exist');
  });

  // ADM-003: Login gagal dengan password salah
  // (untuk sekarang: cukup cek ada pesan error, entah "salah" atau "akun terkunci")
  it('ADM-003: Login gagal dengan password salah', () => {
    cy.visit('/login');

    cy.get('input[name="email"]').clear().type(adminEmail);
    cy.get('input[name="password"]').clear().type('password_salah_banget');

    cy.contains('button', 'Login').click();

    // Harus tetap di halaman login
    cy.url().should('include', '/login');

    // Pesan error bisa salah password atau akun terkunci
    cy.contains(/Email atau password salah|Akun Anda terkunci sementara/i)
      .should('exist');
  });

  // ADM-004: Logout admin
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
