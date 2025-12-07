/// <reference types="cypress" />

describe('Profil Pembeli - Functional Tests', () => {
    const userEmail = 'user01@farmunand.local';
    const userPassword = '111111';

    beforeEach(() => {
        // Login sebagai user
        cy.visit('/login');
        cy.get('input[name="email"]').clear().type(userEmail);
        cy.get('input[name="password"]').clear().type(userPassword);
        cy.contains(/login/i).click();
        cy.url().should('include', '/dashboard');
    });

    // -----------------------------
    // 1. Lihat Profil
    // -----------------------------
    it('PRF-001: Lihat profil berhasil', () => {
        cy.visit('/profile');

        cy.get('h2').should('contain.text', 'Profil Saya');
        cy.get('.info-box .info-label').should('have.length', 5);
        cy.get('.info-box .info-value').should('have.length', 5);
        cy.get('img.profile-photo').should('exist');
    });

    // -----------------------------
    // 2. Menu Edit Profil
    // -----------------------------
    it('PRF-002: Menu edit profil tampil', () => {
        cy.visit('/profile');
        cy.get('.btn-edit').click();
        cy.url().should('include', '/profile/edit');

        cy.get('input[name="username"]').should('exist');
        cy.get('input[name="nama"]').should('exist');
        cy.get('input[name="no_hp"]').should('exist');
        cy.get('input[name="foto"]').should('exist');
    });

    // -----------------------------
    // 3. Update Profil dengan Foto (email tidak diubah)
    // -----------------------------
    it('PRF-003: Update profil berhasil dan foto berubah', () => {
        cy.visit('/profile/edit');

        const randomUsername = `user${Math.floor(Math.random() * 1000)}`;

        cy.get('img.profile-photo').invoke('attr', 'src').then((oldSrc) => {
            cy.get('input[name="username"]').clear().type(randomUsername);
            cy.get('input[name="nama"]').clear().type('Nama Test');
            cy.get('input[name="no_hp"]').clear().type('08123456789');
            cy.get('input[name="foto"]').selectFile('cypress/fixtures/sample.png');

            cy.get('button[type="submit"]').click();
            cy.url().should('include', '/profile');
            cy.contains('Profil berhasil diperbarui!').should('exist');
            cy.contains(randomUsername).should('exist');

            cy.get('img.profile-photo').should(($newImg) => {
                expect($newImg.attr('src')).to.not.equal(oldSrc);
            });
        });
    });

    // -----------------------------
    // 4. Validasi gagal jika field kosong
    // -----------------------------
    it('PRF-004: Update profil gagal jika field kosong', () => {
        cy.visit('/profile/edit');

        cy.get('input[name="username"]').clear();
        cy.get('input[name="nama"]').clear();
        cy.get('input[name="no_hp"]').clear();

        cy.get('button[type="submit"]').click();
        cy.url().should('include', '/profile/edit');

        // Pastikan input memiliki class is-invalid
        cy.get('input[name="username"]').should('have.class', 'is-invalid');
        cy.get('input[name="nama"]').should('have.class', 'is-invalid');
        cy.get('input[name="no_hp"]').should('have.class', 'is-invalid');
    });

    // -----------------------------
    // 5. Validasi gagal jika tipe data salah (no_hp string)
    // -----------------------------
    it('PRF-005: Update profil gagal jika tipe data salah', () => {
        cy.visit('/profile/edit');

        cy.get('input[name="username"]').clear().type('ValidUsername');
        cy.get('input[name="nama"]').clear().type('Nama Test');
        cy.get('input[name="no_hp"]').clear().type('abcde'); // harus numeric

        cy.get('button[type="submit"]').click();
        cy.url().should('include', '/profile/edit');
        cy.get('input[name="no_hp"]').should('have.class', 'is-invalid');
    });
});
