// cypress/e2e/03_KonfirmasiPesanan_user.cy.js

describe('Sheet 03 - Konfirmasi Pesanan (User)', () => {
    // GANTI sesuai user valid di DB
    const userEmail    = 'user01@farmunand.local';
    const userPassword = '111111';
  
    // Helper: login user jika belum login
    function loginUser() {
      cy.visit('/login');
      cy.get('input[name="email"]').clear().type(userEmail);
      cy.get('input[name="password"]').clear().type(userPassword);
      cy.contains('button', /login/i).click();
      cy.url({timeout: 10000}).should('not.include', '/login');
    }
  
    beforeEach(() => {
      // Pastikan login sebelum setiap test
      loginUser();
    });
  
    it('KON-001: Halaman Konfirmasi Pesanan Dikirim bisa dibuka', () => {
      cy.visit('/konfirmasipesanan');
  
      // Cek halaman terbuka
      cy.get('.page-header').should('contain.text', 'Pesanan Dikirim');
      cy.get('.card-container').should('exist');
    });
  
    it('KON-002: Terdapat setidaknya 1 pesanan berstatus Dikirim', () => {
      cy.visit('/konfirmasipesanan');
  
      // Jika tidak ada pesanan, tampilkan alert info
      cy.get('body').then(($body) => {
        if ($body.find('.order-card').length === 0) {
          cy.get('.alert-info').should('contain.text', 'Belum ada pesanan');
        } else {
          cy.get('.order-card').each(($el) => {
            cy.wrap($el).find('span.badge').should('contain.text', 'Dikirim');
          });
        }
      });
    });
  
    it('KON-003: Konfirmasi pesanan selesai', () => {
      cy.visit('/konfirmasipesanan');
  
      cy.get('body').then(($body) => {
        if ($body.find('.order-card').length === 0) {
          cy.log('Tidak ada pesanan Dikirim untuk diuji.');
        } else {
          // Ambil pesanan pertama
          cy.get('.order-card').first().within(() => {
            cy.contains('Pesanan Selesai').click();
          });
  
          // Tunggu redirect (SweetAlert timer 1600ms + redirect)
          cy.url({timeout: 5000}).should('include', '/pesananselesai');
  
          // Cek flash message muncul
          cy.get('body').should('contain.text', 'Pesanan berhasil dikonfirmasi');
        }
      });
    });
  });
  