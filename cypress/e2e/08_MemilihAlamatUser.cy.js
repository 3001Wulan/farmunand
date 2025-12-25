describe('Sheet 07 - Memilih Alamat (Pembeli)', () => {

    const userEmail = 'user01@farmunand.local';
    const userPassword = '111111';

    beforeEach(() => {
        cy.session('login-pembeli', () => {
            cy.visit('/login', { failOnStatusCode: false });

            cy.get('input[name="email"]', { timeout: 10000 })
              .should('be.visible')
              .clear()
              .type(userEmail);

            cy.get('input[name="password"]')
              .clear()
              .type(userPassword);

            cy.contains('button', /login/i).click();

            cy.url().should('include', '/dashboard');
        });
    });

    it('HAL-001: Halaman Alamat Pengiriman dapat dibuka', () => {
        cy.visit('/memilihalamat');
        cy.contains(/alamat pengiriman/i).should('be.visible');
    });

    it('HAL-002: Tombol Tambah Alamat muncul', () => {
        cy.visit('/memilihalamat');
        cy.contains('button', /tambah alamat/i).should('be.visible');
    });

    it('HAL-003: Jika ada alamat, tombol Gunakan Alamat Terpilih tampil', () => {
        cy.visit('/memilihalamat');

        cy.get('body').then($body => {
            if ($body.text().includes('Gunakan Alamat Terpilih')) {
                cy.contains('button', /gunakan alamat terpilih/i)
                  .should('exist')
                  .and('be.visible');
            } else {
                cy.contains(/belum ada alamat/i).should('exist');
            }
        });
    });

    it('HAL-004: Minimal 1 kartu alamat tampil jika data ada', () => {
        cy.visit('/memilihalamat');

        cy.get('body').then($body => {
            if ($body.find('.address-card').length > 0) {
                cy.get('.address-card').should('have.length.at.least', 1);
            } else {
                cy.contains(/belum ada alamat/i).should('exist');
            }
        });
    });

    it('HAL-005: Tombol Ubah muncul di setiap kartu alamat', () => {
        cy.visit('/memilihalamat');

        cy.get('.address-card').then($cards => {
            if ($cards.length > 0) {
                cy.wrap($cards).each(card => {
                    cy.wrap(card).contains(/ubah/i).should('exist');
                });
            }
        });
    });

    it('HAL-006: Modal Tambah Alamat dapat dibuka', () => {
        cy.visit('/memilihalamat');

        cy.contains('button', /tambah alamat/i).click();

        cy.get('#tambahAlamatModal')
          .should('be.visible')
          .and('have.class', 'show');

        cy.get('#tambahAlamatModal input[name="nama_penerima"]').should('exist');
    });

    it('HAL-007: Tambah alamat baru berhasil', () => {
        cy.visit('/memilihalamat');

        cy.contains('button', /tambah alamat/i).click();

        cy.get('#tambahAlamatModal input[name="nama_penerima"]').type('Tester Cypress');
        cy.get('#tambahAlamatModal input[name="jalan"]').type('Jalan Cypress No. 123');
        cy.get('#tambahAlamatModal input[name="no_telepon"]').type('08123456789');
        cy.get('#tambahAlamatModal input[name="kota"]').type('Padang');
        cy.get('#tambahAlamatModal input[name="provinsi"]').type('Sumatera Barat');
        cy.get('#tambahAlamatModal input[name="kode_pos"]').type('12345');

        cy.get('#tambahAlamatModal form').submit();

        cy.url().should('include', '/memilihalamat');
        cy.get('.address-card').should('have.length.at.least', 1);
    });

    it('HAL-008: Pengguna dapat memilih alamat dengan radio button', () => {
        cy.visit('/memilihalamat');

        cy.get('input[type=radio][name=alamat]').then($radio => {
            if ($radio.length > 0) {
                cy.wrap($radio.first())
                  .check({ force: true })
                  .should('be.checked');
            } else {
                cy.contains(/belum ada alamat/i).should('exist');
            }
        });
    });

    it('HAL-009: Tombol Gunakan Alamat Terpilih berfungsi', () => {
        cy.visit('/memilihalamat');

        cy.get('body').then($body => {
            if ($body.text().includes('Gunakan Alamat Terpilih')) {

                cy.get('input[type=radio][name=alamat]')
                  .first()
                  .check({ force: true });

                cy.contains('button', /gunakan alamat terpilih/i)
                  .click({ force: true });

                cy.url().should('include', '/keranjang');
            } else {
                cy.contains(/belum ada alamat/i).should('exist');
            }
        });
    });

    it('HAL-010: Modal Ubah Alamat tampil dan field terisi', () => {
        cy.visit('/memilihalamat');

        cy.get('.ubahAlamatBtn').then($btn => {
            if (!$btn.length) return;

            cy.wrap($btn.first()).click({ force: true });

            cy.get('#ubahAlamatModal')
              .should('be.visible')
              .and('have.class', 'show');

            cy.get('#ubah_nama_penerima').should('be.visible');
            cy.get('#ubah_jalan').should('exist');
            cy.get('#ubah_kota').should('exist');
            cy.get('#ubah_provinsi').should('exist');
            cy.get('#ubah_kode_pos').should('exist');
        });
    });

    it('HAL-011: Perubahan alamat berhasil disimpan', () => {
        cy.visit('/memilihalamat');

        cy.get('.ubahAlamatBtn').then($btn => {
            if (!$btn.length) return;

            cy.wrap($btn.first()).click({ force: true });

            cy.get('#ubah_nama_penerima')
              .clear()
              .type('Cypress Update');

            cy.get('#ubah_jalan')
              .clear()
              .type('Jalan Updated No. 77');

            cy.contains('#ubahAlamatModal button', /simpan/i)
              .click({ force: true });

            cy.url().should('include', '/memilihalamat');
        });
    });

});
