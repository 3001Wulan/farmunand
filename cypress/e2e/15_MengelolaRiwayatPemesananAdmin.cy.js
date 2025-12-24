describe('Mengelola Riwayat Pesanan (Admin)', () => {

  const adminEmail = 'admin@farmunand.local';
  const adminPassword = '111111';

  beforeEach(() => {
    cy.visit('/login');

    cy.get('input[name="email"]').clear().type(adminEmail);
    cy.get('input[name="password"]').clear().type(adminPassword);
    cy.get('button[type="submit"]').click();

    
    cy.contains(/dashboard|admin|beranda/i, { timeout: 10000 })
      .should('exist');
  });

 
  it('Halaman riwayat pesanan tampil dengan tabel', () => {
    cy.visit('/MengelolaRiwayatPesanan');

    cy.get('table').should('exist');
    cy.get('table tbody tr').should('have.length.at.least', 1);
  });

  
  it('Filter berdasarkan status', () => {
    cy.visit('/MengelolaRiwayatPesanan?status=Dikemas');

    cy.get('table tbody tr').each(($row) => {
      cy.wrap($row)
        .contains(/Dikemas/i)
        .should('exist');
    });
  });

  
  it('Filter berdasarkan keyword', () => {
    const keyword = 'Telur';

    cy.visit(`/MengelolaRiwayatPesanan?keyword=${keyword}`);

    cy.get('table tbody tr')
      .should('have.length.at.least', 1)
      .each(($row) => {
        cy.wrap($row).should('contain.text', keyword);
      });
  });

  
  it('Update status pesanan valid', () => {
    cy.visit('/MengelolaRiwayatPesanan');

    cy.get('select[name="status_pemesanan"]')
      .first()
      .select('Dikirim', { force: true });

    cy.contains(/berhasil|diperbarui|update/i, { timeout: 10000 })
      .should('exist');
  });

  
  it('Update status pesanan tidak valid', () => {
  cy.visit('/MengelolaRiwayatPesanan');

  
  cy.get('table tbody tr')
    .contains('Dikirim')
    .closest('tr')
    .within(() => {

     
      cy.get('span.badge').invoke('text').as('statusAwal');

      
      cy.get('select[name="status_pemesanan"]')
        .select('Dibatalkan', { force: true });

      
      cy.get('@statusAwal').then((status) => {
        cy.get('span.badge').should('contain.text', status.trim());
      });
    });
});
  });


