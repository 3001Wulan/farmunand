describe('Mengelola Riwayat Pesanan (Admin)', () => {
    const adminEmail = 'admin@farmunand.local';
    const adminPassword = '111111';
  
    beforeEach(() => {
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
        cy.intercept('GET', '/MengelolaRiwayatPesanan*').as('getOrders');
        cy.visit('/MengelolaRiwayatPesanan');
        cy.wait('@getOrders'); 
        cy.get('table tbody tr td span.badge')
          .contains('Dikemas')
          .first()
          .closest('tr') 
          .within(() => {
            cy.get('select[name="status_pemesanan"]').select('Dikirim');
          });
        cy.get('.alert-success', { timeout: 5000 })
          .should('be.visible')
          .and('contain.text', 'Status pesanan berhasil diperbarui');
      });
      
      it('Update status pesanan tidak valid', () => {
        cy.visit('/MengelolaRiwayatPesanan');
        cy.get('table tbody tr td span.badge')
          .contains('Dikirim')
          .first()
          .closest('tr')
          .within(() => {
            cy.get('select[name="status_pemesanan"]').select('Dibatalkan', { force: true });
          });
        cy.get('.alert-danger', { timeout: 5000 })
          .should('be.visible')
          .and('contain.text', 'Pesanan "Dikirim" tidak dapat diubah lagi');
      });         
      
  });
  