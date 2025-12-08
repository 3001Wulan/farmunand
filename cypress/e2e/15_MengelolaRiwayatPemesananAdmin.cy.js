describe('Mengelola Riwayat Pesanan (Admin)', () => {
    const adminEmail = 'admin@farmunand.local';
    const adminPassword = '111111';
  
    beforeEach(() => {
      // Login sebagai admin
      cy.visit('/login');
      cy.get('input[name="email"]').clear().type(adminEmail);
      cy.get('input[name="password"]').clear().type(adminPassword);
      cy.get('button[type="submit"]').click();
      cy.url().should('include', '/dashboard');
    });
  
    it('Halaman riwayat pesanan tampil dengan tabel', () => {
      cy.visit('/MengelolaRiwayatPesanan');
      cy.get('table tbody tr').should('exist');
    });
  
    it('Filter berdasarkan status', () => {
      cy.visit('/MengelolaRiwayatPesanan?status=Dikemas');
      cy.get('table tbody tr').each(($row) => {
        cy.wrap($row).find('td').eq(5).should('contain.text', 'Dikemas');
      });
    });
  
    it('Filter berdasarkan keyword', () => {
      const keyword = 'Telur Omega';
      cy.visit(`/MengelolaRiwayatPesanan?keyword=${keyword}`);
      cy.get('table tbody tr').each(($row) => {
        cy.wrap($row).should('contain.text', keyword);
      });
    });
  
    it('Update status pesanan valid', () => {
        // Intercept request GET agar Cypress menunggu data selesai dimuat
        cy.intercept('GET', '/MengelolaRiwayatPesanan*').as('getOrders');
      
        // Visit halaman riwayat pesanan admin
        cy.visit('/MengelolaRiwayatPesanan');
        cy.wait('@getOrders'); // Tunggu data selesai dimuat
      
        // Ambil row pertama yang memiliki badge "Dikemas"
        cy.get('table tbody tr td span.badge')
          .contains('Dikemas')
          .first()
          .closest('tr') // <-- pakai closest, bukan parent
          .within(() => {
            // Pilih status baru "Dikirim"
            cy.get('select[name="status_pemesanan"]').select('Dikirim');
          });
      
        // Pastikan alert sukses muncul
        cy.get('.alert-success', { timeout: 5000 })
          .should('be.visible')
          .and('contain.text', 'Status pesanan berhasil diperbarui');
      });
      
      it('Update status pesanan tidak valid', () => {
        cy.visit('/MengelolaRiwayatPesanan');
      
        // Ambil row pertama dengan badge "Dikirim"
        cy.get('table tbody tr td span.badge')
          .contains('Dikirim')
          .first()
          .closest('tr')
          .within(() => {
            // Pilih status yang ada di dropdown tapi tidak diizinkan dari "Dikirim"
            cy.get('select[name="status_pemesanan"]').select('Dibatalkan', { force: true });
          });
      
        // Pastikan alert error muncul
        cy.get('.alert-danger', { timeout: 5000 })
          .should('be.visible')
          .and('contain.text', 'Pesanan "Dikirim" tidak dapat diubah lagi');
      });         
      
  });
  