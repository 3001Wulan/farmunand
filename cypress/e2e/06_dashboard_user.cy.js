describe('Sheet 06 - Dashboard Pembeli', () => {
    const userEmail = 'user01@farmunand.local';
    const userPassword = '111111';
    const sidebarSelector = '.sidebarUser';

    beforeEach(() => {
        // Login user
        cy.visit('/login'); 
        cy.get('input[name="email"]').type(userEmail);
        cy.get('input[name="password"]').type(userPassword);
        cy.contains('button', 'Login').click();

        // Verifikasi redirect ke dashboard
        cy.url().should('include', '/dashboarduser'); 
        cy.get(sidebarSelector, { timeout: 10000 }).should('be.visible'); 

        // Ambil username dari welcome card
        cy.get('.welcome-card .username')
          .should('exist')
          .invoke('text')
          .then(username => {
              cy.wrap(username.trim()).as('currentUsername');
          });
    });

    it('USR-DASH-001: Sidebar menampilkan username', function() {
        cy.get('@currentUsername').then(expectedUsername => {
            cy.get(sidebarSelector)
              .find('p.fw-bold')
              .invoke('text')
              .then(text => {
                  const cleaned = text.replace(/\s+/g, ' ').trim();
                  expect(cleaned).to.include(expectedUsername);
              });
        });
    });

    it('USR-DASH-002: Kartu metrik pesanan tampil dan berisi data', () => {
        cy.contains('.card-body', /Pesanan Sukses/i).within(() => {
            cy.get('i.text-success').should('exist');
            cy.get('p.text-success').invoke('text').should('match', /^\d+$/); 
        });
        cy.contains('.card-body', /Pending/i).within(() => {
            cy.get('i.text-warning').should('exist');
            cy.get('p.text-warning').invoke('text').should('match', /^\d+$/);
        });
        cy.contains('.card-body', /Dibatalkan/i).within(() => {
            cy.get('i.text-danger').should('exist');
            cy.get('p.text-danger').invoke('text').should('match', /^\d+$/);
        });
    });

    it('USR-DASH-003: Daftar Produk Terbaru dimuat dengan detail', () => {
        cy.contains('.card-header', /Rekomendasi Produk/i).should('exist');

        const produkContainerSelector = '.card-body.d-flex.gap-3.flex-wrap';
        cy.get(produkContainerSelector).then($container => {
            const $cards = $container.find('.product-card');
            if ($cards.length > 0) {
                cy.wrap($cards.first()).within(() => {
                    cy.get('h6.card-title', { timeout: 10000 })
                      .should('exist')
                      .should($el => expect($el.text().trim()).not.to.be.empty);

                    cy.get('p.text-success', { timeout: 10000 })
                      .should('exist')
                      .should($el => expect($el.text().trim()).to.match(/Rp\s\d{1,3}(\.\d{3})*/));

                    cy.get('.rating-stars i', { timeout: 10000 }).should('have.length', 5);

                    cy.get('.btn-buy', { timeout: 10000 })
                      .should('have.attr', 'data-id')
                      .and('match', /^\d+$/);
                });
            } else {
                cy.log('Tidak ada produk tersedia, lewati pengecekan detail.');
            }
        });
    });

    it('USR-DASH-004: Akses ditolak jika tidak login', () => {
        cy.visit('/logout'); 
        cy.visit('/dashboarduser'); 
        cy.url().should('match', /\/login/); 
        cy.contains(/Silakan login dulu|Anda harus login/i).should('exist');
    });
});
