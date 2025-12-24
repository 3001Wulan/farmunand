describe('Sheet Pesanan User - FarmUnand', () => {
    const userEmail = 'user01@farmunand.local';
    const userPassword = '111111';

    beforeEach(() => {
        cy.visit('/login');
        cy.get('input[name="email"]').clear().type(userEmail);
        cy.get('input[name="password"]').clear().type(userPassword);
        cy.contains(/login/i).click();
        cy.url().should('include', '/dashboarduser');
    });

    const pages = [
        { name: 'Pesanan Saya', url: '/riwayatpesanan', header: 'Pesanan Saya' },
        { name: 'Belum Bayar', url: '/pesananbelumbayar', header: 'Pesanan Belum Bayar' },
        { name: 'Dikemas', url: '/pesanandikemas', header: 'Pesanan Dikemas' },
        { name: 'Dikirim', url: '/konfirmasipesanan', header: 'Pesanan Dikirim' },
        { name: 'Selesai', url: '/pesananselesai', header: 'Pesanan Selesai' },
        { name: 'Dibatalkan', url: '/pesanandibatalkan', header: 'Pesanan Dibatalkan' },
        { name: 'Penilaian', url: '/penilaian/daftar', header: 'Berikan Penilaian' },
    ];

    pages.forEach(page => {
        it(`HAL: Buka halaman ${page.name}`, () => {
            cy.visit(page.url);
            cy.url().should('include', page.url);
            cy.get('h5').should('contain.text', page.header);
            cy.get('body').then($body => {
                if ($body.find('.order-card, .card').length > 0) {
                    cy.get('.order-card, .card').should('exist');
                } else {
                    cy.get('.alert').should('exist');
                }
            });
        });
    });
});
