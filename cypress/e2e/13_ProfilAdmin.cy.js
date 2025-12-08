describe('Profile Admin', () => {
    const email = 'admin@farmunand.local';
    const pass  = '111111';

    beforeEach(() => {
        // Login sebelum tiap test
        cy.visit('/login');
        cy.get('input[name="email"]').type(email);
        cy.get('input[name="password"]').type(pass);
        cy.contains(/login/i).click();
        cy.url().should('not.include', '/login');
    });

    // -------------------------------------------------
    it('PRF-001: Halaman profil admin dapat dibuka', () => {
        cy.visit('/profileadmin');

        // Cek header profil
        cy.get('h2').contains('Profil Admin').should('exist');
        cy.get('p').contains(email.split('@')[0]).should('exist'); // cek username tampil
        cy.get('.profile-photo').should('exist');

        // Cek info-box tampil
        cy.get('.info-box').eq(0).should('contain', 'Username:');
        cy.get('.info-box').eq(1).should('contain', 'Nama:');
        cy.get('.info-box').eq(2).should('contain', 'Email:');
        cy.get('.info-box').eq(3).should('contain', 'No HP:');

        // Tombol edit profil ada
        cy.get('a.btn-edit').should('exist').and('contain', 'Edit Profil');
    });

    // -------------------------------------------------
    it('PRF-002: Halaman edit profil dapat dibuka', () => {
        cy.visit('/profileadmin');
        cy.get('a.btn-edit').click();

        cy.url().should('include', '/profileadmin/edit');
        cy.get('input[name="username"]').should('exist');
        cy.get('input[name="nama"]').should('exist');
        cy.get('input[name="email"]').should('exist');
        cy.get('input[name="no_hp"]').should('exist');
        cy.get('input[type="file"]').should('exist');
        cy.get('button[type="submit"]').should('exist');
    });

    // -------------------------------------------------
    it('PRF-003: Update profil berhasil tanpa ganti foto', () => {
        cy.visit('/profileadmin/edit');

        cy.get('input[name="username"]').clear().type('AdminUpdated');
        cy.get('input[name="nama"]').clear().type('Admin Update');
        // cy.get('input[name="email"]').clear().type('admin@farmunand.local');
        cy.get('input[name="no_hp"]').clear().type('081234567890');

        cy.get('button[type="submit"]').click();

        cy.url().should('include', '/profileadmin');
        cy.contains('Profil Admin berhasil diperbarui.').should('exist');
    });

    // -------------------------------------------------
    it('PRF-004: Update profil berhasil dengan ganti foto', () => {
        cy.visit('/profileadmin/edit');

        cy.get('input[name="username"]').clear().type('AdminFoto');
        cy.get('input[type="file"]').selectFile('cypress/fixtures/sample.png', { force: true });

        cy.get('button[type="submit"]').click();

        cy.url().should('include', '/profileadmin');
        cy.contains('Profil Admin berhasil diperbarui.').should('exist');
        cy.get('.profile-photo').should('have.attr', 'src').and('include', 'uploads/profile/');
    });

    // -------------------------------------------------
    // -------------------------------------------------
    it('PRF-005: Validasi field wajib sesuai view', () => {
        cy.visit('/profileadmin/edit');
    
        // Cek atribut required
        cy.get('#username').should('have.attr', 'required');
        cy.get('#email').should('have.attr', 'required');
    
        // Kosongkan username & email
        cy.get('#username').clear();
        // cy.get('#email').clear(); // email biasanya tetap, tapi bisa dicoba
    
        // Cek HTML5 validity
        cy.get('form').then($form => {
            const form = $form[0];
            expect(form.checkValidity()).to.be.false; // form invalid
        });
    
        // Tombol submit tetap ada
        cy.get('button[type="submit"]').should('exist');
    });
    
});
