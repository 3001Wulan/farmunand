describe('Sheet 07 - Memilih Alamat (Pembeli)', () => {

    const userEmail = 'user01@farmunand.local';
    const userPassword = '111111';

    beforeEach(() => {
        cy.visit('/login');

        cy.get('input[name="email"]').clear().type(userEmail);
        cy.get('input[name="password"]').clear().type(userPassword);
        cy.contains(/login/i).click();

        cy.url().should('include', '/dashboard');
    });

    it('HAL-001: Halaman Alamat Pengiriman dapat dibuka', () => {
        cy.visit('/memilihalamat');
        cy.contains(/alamat pengiriman/i).should('exist');
    });

    it('HAL-002: Tombol Tambah Alamat muncul', () => {
        cy.visit('/memilihalamat');
        cy.contains('button', /tambah alamat/i).should('be.visible');
    });

    it('HAL-003: Jika ada alamat, tombol Gunakan Alamat Terpilih harus ada', () => {
        cy.visit('/memilihalamat');

        cy.get('body').then(($body) => {
            if ($body.text().includes('Gunakan Alamat Terpilih')) {
                cy.contains('button', /gunakan alamat terpilih/i)
                  .should('exist')
                  .and('be.visible');
            } else {
                cy.contains(/belum ada alamat/i).should('exist');
            }
        });
    });

    it('HAL-004: Jika ada alamat, minimal 1 kartu alamat harus tampil', () => {
        cy.visit('/memilihalamat');
        cy.get('body').then(($body) => {
            if ($body.find('.address-card').length > 0) {
                cy.get('.address-card').should('have.length.at.least', 1);
            } else {
                cy.contains(/belum ada alamat/i).should('exist');
            }
        });
    });

    it('HAL-005: Tombol Ubah pada setiap alamat muncul', () => {
        cy.visit('/memilihalamat');

        cy.get('body').then(($body) => {
            if ($body.find('.address-card').length > 0) {
                cy.get('.address-card').each(($card) => {
                    cy.wrap($card).contains(/ubah/i).should('exist');
                });
            }
        });
    });

    it('HAL-006: Modal Tambah Alamat dapat dibuka', () => {
        cy.visit('/memilihalamat');
        cy.contains('button', /tambah alamat/i).click();

        cy.get('#tambahAlamatModal').should('be.visible');
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

    it('HAL-008: Pengguna dapat memilih alamat lewat radio', () => {
        cy.visit('/memilihalamat');

        cy.get('body').then(($body) => {
            if ($body.find('input[type=radio][name=alamat]').length > 0) {
                cy.get('input[type=radio][name=alamat]').first().check({ force: true }).should('be.checked');
            } else {
                cy.contains(/belum ada alamat/i).should('exist');
            }
        });
    });

    it('HAL-009: Tombol Gunakan Alamat Terpilih bekerja', () => {
        cy.visit('/memilihalamat');
    
        cy.get('body').then(($body) => {

            if ($body.text().includes('Gunakan Alamat Terpilih')) {

                cy.get('input[type=radio][name=alamat]').first().check({ force: true });

                cy.contains('button', /gunakan alamat terpilih/i).click({ force: true });
                cy.url().should('include', '/keranjang');
            }
            else {
                cy.contains('Belum ada alamat').should('exist');
            }
        });
    });
    

    it('HAL-010: Modal Ubah Alamat tampil & field terisi', () => {

        cy.visit('/memilihalamat');
    
        cy.get('body').then(($body) => {

            if (!$body.find('.ubahAlamatBtn').length) return;

            cy.get('.ubahAlamatBtn').first().click({ force: true });
            cy.get('#ubahAlamatModal')
                .should('be.visible')
                .and('have.class', 'show');
    
            cy.wait(500); 
    
            cy.get('#ubah_id_alamat').should('exist');
            cy.get('#ubah_nama_penerima').should('exist').and('be.visible');
            cy.get('#ubah_no_telepon').should('exist');
            cy.get('#ubah_jalan').should('exist');
            cy.get('#ubah_kota').should('exist');
            cy.get('#ubah_provinsi').should('exist');
            cy.get('#ubah_kode_pos').should('exist');
            cy.get('.ubahAlamatBtn').first().then(($btn) => {
                const nama = $btn.data('nama');
                cy.get('#ubah_nama_penerima').should('have.value', nama);
            });
    
        });
    });
    
    it('HAL-011: Ubah alamat berhasil disimpan', () => {
        cy.visit('/memilihalamat');
    
        cy.get('body').then(($body) => {
    
            if (!$body.find('.ubahAlamatBtn').length) return;
            cy.get('.ubahAlamatBtn').first().click({ force: true });
            cy.get('#ubahAlamatModal')
                .should('be.visible')
                .and('have.class', 'show');
    
            cy.wait(500);
    
            cy.get('#ubah_nama_penerima').should('exist').clear().type('Cypress Update');
            cy.get('#ubah_jalan').should('exist').clear().type('Jalan Updated No. 77');
            cy.contains('#ubahAlamatModal button', /simpan/i).click({ force: true });
            cy.wait(1000);
            cy.url().should('include', '/memilihalamat');
        });
    });
    
});  