// cypress/e2e/01_auth_user.cy.js

describe('Sheet 01B - Auth User', () => {
  // GANTI sesuai data user nyata di DB
  const userEmail = 'user01@farmunand.local';
  const userPassword = '111111';

  // USR-001: Halaman login bisa dibuka (boleh sama dgn admin)
  it('USR-001: Halaman login bisa dibuka', () => {
    cy.visit('/login');

    cy.get('form').should('exist');
    cy.get('input[name="email"]').should('exist');
    cy.get('input[name="password"]').should('exist');
    cy.contains(/login/i).should('exist');
  });

  // USR-002: Login sukses sebagai user pembeli
  it('USR-002: Login sukses sebagai user', () => {
    cy.visit('/login');

    cy.get('input[name="email"]').type(userEmail);
    cy.get('input[name="password"]').type(userPassword);

    cy.contains('button', 'Login').click();

    // Redirect ke dashboard user
    cy.url().should('include', '/dashboarduser');

    // Cek elemen khas dashboard user
    // (sesuaikan dengan tampilan milikmu, sementara pakai cek "Dashboard")
    cy.contains('Dashboard').should('exist');
    // contoh lain yang mungkin ada:
    // cy.contains(/ringkasan pesanan/i).should('exist');
    // cy.contains(/rekomendasi produk/i).should('exist');
  });

  // USR-003: Login gagal dengan password salah (akun BELUM terkunci)
  it('USR-003: Login gagal dengan password salah', () => {
    cy.visit('/login');

    cy.get('input[name="email"]').type(userEmail);
    cy.get('input[name="password"]').type('password_salah_banget');

    cy.contains('button', 'Login').click();

    // Tetap di halaman login
    cy.url().should('include', '/login');

    // Pesan error biasa sebelum limit habis, misal:
    // "Email atau password salah. Sisa percobaan: X."
    // Sesuaikan dengan teks di halamanmu.
    cy.contains(/Email atau password salah\./i).should('exist');
  });
});
