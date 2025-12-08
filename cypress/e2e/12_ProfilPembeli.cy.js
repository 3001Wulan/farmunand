describe('Sheet Profil Pembeli', () => {

    const email = 'user01@farmunand.local';
    const pass  = '111111';

    beforeEach(() => {
        // Login terlebih dahulu
        cy.visit('/login');
        cy.get('input[name="email"]').type(email);
        cy.get('input[name="password"]').type(pass);
        cy.contains(/login/i).click();
        cy.url().should('not.include', '/login');
    });

    // -------------------------------------------------
    // 1. Halaman profil bisa dibuka
    // -------------------------------------------------
    it('PB-001: Halaman profil tampil', () => {
        cy.visit('/profile');
        cy.contains('Profil Saya').should('exist');
        cy.get('.profile-photo').should('exist');
        cy.get('.info-label').contains('Username:').next().should('exist');
        cy.get('.info-label').contains('Nama:').next().should('exist');
        cy.get('.info-label').contains('Email:').next().should('exist');
        cy.get('.info-label').contains('No HP:').next().should('exist');
        cy.get('.info-label').contains('Role:').next().should('exist');
    });

    // -------------------------------------------------
    // 2. Halaman edit profil bisa dibuka
    // -------------------------------------------------
    it('PRB-002: Halaman edit profil tampil', () => {
        cy.visit('/profile/edit');
        cy.contains('Edit Profil').should('exist');
        cy.get('input[name="username"]').should('exist');
        cy.get('input[name="nama"]').should('exist');
        cy.get('input[name="email"]').should('exist');
        cy.get('input[name="no_hp"]').should('exist');
        cy.get('#previewFoto').should('exist');
    });

    // -------------------------------------------------
    // 3. Update profil tanpa ganti foto
    // -------------------------------------------------
    it('PRB-003: Update profil tanpa ganti foto berhasil', () => {
        cy.visit('/profile/edit');

        cy.get('input[name="username"]').clear().type('PembeliUpdated');
        cy.get('input[name="nama"]').clear().type('Pembeli Update');
        cy.get('input[name="no_hp"]').clear().type('081234567890');

        // Email tetap tidak diganti
        cy.get('button[type="submit"]').click();

        cy.url().should('include', '/profile');
        cy.contains('Profil berhasil diperbarui!').should('exist');

        // Foto profil di halaman profil
        cy.get('.profile-photo')
          .should('have.attr', 'src')
          .and('include', 'uploads/profile/');
    });

    // -------------------------------------------------
    // 4. Update profil dengan ganti foto
    // -------------------------------------------------
    it('PRB-004: Update profil dengan ganti foto berhasil', () => {
        cy.visit('/profile/edit');

        cy.get('input[name="username"]').clear().type('PembeliFoto');
        cy.get('input[name="nama"]').clear().type('Pembeli Foto');
        cy.get('input[name="no_hp"]').clear().type('081234567891');

        cy.get('input[name="foto"]').selectFile('cypress/fixtures/sample.png', { force: true });

        cy.get('button[type="submit"]').click();

        cy.url().should('include', '/profile');
        cy.contains('Profil berhasil diperbarui!').should('exist');

        // Foto profil di halaman profil
        cy.get('.profile-photo')
          .should('have.attr', 'src')
          .and('include', 'uploads/profile/');
    });

    // -------------------------------------------------
    // 5. Validasi username & nama wajib
    // -------------------------------------------------
    it('PRB-005: Validasi field wajib', () => {
        cy.visit('/profile/edit');
    
        cy.get('input[name="username"]').clear();
        cy.get('input[name="nama"]').clear();
    
        cy.get('button[type="submit"]').click();
    
        // Hanya cek URL tetap di halaman edit
        cy.location('pathname', { timeout: 6000 }).should('eq', '/profile/edit');
    });
    

    // -------------------------------------------------
    // 6. Validasi upload file bukan gambar
    // -------------------------------------------------
    it('PRB-006: Validasi file harus gambar', () => {
        cy.visit('/profile/edit');

        cy.get('input[name="foto"]').selectFile('cypress/fixtures/sample.pdf', { force: true });
        cy.get('button[type="submit"]').click();

        cy.url().should('include', '/profile/edit');
        cy.get('div.alert.alert-danger')
  .should('exist')
  .and('contain.text', 'foto is not a valid, uploaded image file.');

    });

});
