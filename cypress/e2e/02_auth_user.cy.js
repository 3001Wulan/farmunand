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

    // --------------------------------------------------------------------
  // USR-FORGOT-001 s/d USR-FORGOT-002: Lupa Password
  // --------------------------------------------------------------------

  it('USR-FORGOT-001: Halaman lupa password bisa dibuka', () => {
    cy.visit('/forgot-password');

    cy.get('form').should('exist');
    cy.get('input[name="email"]').should('exist');
    cy.contains(/kirim link reset|reset password|kirim/i).should('exist');
  });

    it('USR-FORGOT-002: Lupa password mengirimkan notifikasi untuk email terdaftar', () => {
    // Pakai email user yang memang terdaftar
    const userEmail = 'user01@farmunand.local';

    cy.visit('/forgot-password');

    cy.get('input[name="email"]').clear().type(userEmail);

    cy.contains('button', /kirim|reset|submit/i).click();

    // Boleh redirect ke /login atau tetap di /forgot-password,
    // tapi dari log kita tahu dia pindah ke /login
    cy.url({ timeout: 10000 }).should('include', '/login');

    // Cari elemen flash/alert di halaman login
    cy.get('body').then(($body) => {
      const selector = '.alert, .alert-success, .alert-info, .flash-message, #flash-message';

      if ($body.find(selector).length) {
        // Kalau ada alert, cek teksnya mengandung kata "email" dan "reset" atau "password"
        cy.get(selector)
          .first()
          .invoke('text')
          .then((text) => {
            const lower = text.toLowerCase();
            expect(lower).to.match(/email/);      // ada kata email
            expect(lower).to.match(/reset|password/); // dan reset/password
          });
      } else {
        // Fallback: kalau tidak ada kelas alert yang jelas, cari teks di seluruh halaman
        cy.contains(/reset.*email|email.*reset|cek.*email/i).should('exist');
      }
    });
  });


});
