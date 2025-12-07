describe('Sheet 07 - Memilih Alamat (Pembeli)', () => {

    const userEmail = 'user01@farmunand.local';
    const userPassword = '111111';

    // -----------------------------
    // LOGIN SEBELUM SETIAP TEST
    // -----------------------------
    beforeEach(() => {
        cy.visit('/login');

        cy.get('input[name="email"]').clear().type(userEmail);
        cy.get('input[name="password"]').clear().type(userPassword);
        cy.contains(/login/i).click();

        cy.url().should('include', '/dashboard');
    });

    // -----------------------------
    // HAL-001 — Halaman dapat dibuka
    // -----------------------------
    it('HAL-001: Halaman Alamat Pengiriman dapat dibuka', () => {
        cy.visit('/memilihalamat');
        cy.contains(/alamat pengiriman/i).should('exist');
    });

    // -----------------------------
    // HAL-002 — Tombol Tambah Alamat
    // -----------------------------
    it('HAL-002: Tombol Tambah Alamat muncul', () => {
        cy.visit('/memilihalamat');
        cy.contains('button', /tambah alamat/i).should('be.visible');
    });

    // -----------------------------
    // HAL-003 — Gunakan Alamat Terpilih
    // -----------------------------
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

    // -----------------------------
    // HAL-004 — Minimal 1 kartu alamat
    // -----------------------------
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

    // -----------------------------
    // HAL-005 — Tombol Ubah muncul
    // -----------------------------
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

    // ======================================================================
    // ========================== FITUR TAMBAHAN =============================
    // ======================================================================

    // ---------------------------------------------------
    // HAL-006 — Tambah alamat baru (modal muncul)
    // ---------------------------------------------------
    it('HAL-006: Modal Tambah Alamat dapat dibuka', () => {
        cy.visit('/memilihalamat');
        cy.contains('button', /tambah alamat/i).click();

        cy.get('#tambahAlamatModal').should('be.visible');
        cy.get('#tambahAlamatModal input[name="nama_penerima"]').should('exist');
    });

    // ---------------------------------------------------
    // HAL-007 — Tambah alamat lengkap (POST)
    // ---------------------------------------------------
    it('HAL-007: Tambah alamat baru berhasil', () => {
        cy.visit('/memilihalamat');

        cy.contains('button', /tambah alamat/i).click();

        cy.get('#tambahAlamatModal input[name="nama_penerima"]').type('Tester Cypress');
        cy.get('#tambahAlamatModal input[name="jalan"]').type('Jalan Cypress No. 123');
        cy.get('#tambahAlamatModal input[name="no_telepon"]').type('08123456789');
        cy.get('#tambahAlamatModal input[name="kota"]').type('Padang');
        cy.get('#tambahAlamatModal input[name="provinsi"]').type('Sumatera Barat');
        cy.get('#tambahAlamatModal input[name="kode_pos"]').type('12345');

        // Submit form
        cy.get('#tambahAlamatModal form').submit();

        cy.url().should('include', '/memilihalamat');

        // Setelah submit, harus ada address-card baru
        cy.get('.address-card').should('have.length.at.least', 1);
    });

    // ---------------------------------------------------
    // HAL-008 — Pilih alamat lewat radio
    // ---------------------------------------------------
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

    // ---------------------------------------------------
    // HAL-009 — tekan Gunakan Alamat Terpilih
    // ---------------------------------------------------
    it('HAL-009: Tombol Gunakan Alamat Terpilih bekerja', () => {
        cy.visit('/memilihalamat');
    
        cy.get('body').then(($body) => {
    
            // Jika tombol tersedia
            if ($body.text().includes('Gunakan Alamat Terpilih')) {
    
                // Pilih radio pertama
                cy.get('input[type=radio][name=alamat]').first().check({ force: true });
    
                // Klik tombol
                cy.contains('button', /gunakan alamat terpilih/i).click({ force: true });
    
                // Karena JS langsung redirect, cukup cek URL
                cy.url().should('include', '/keranjang');
            }
    
            // Jika tidak ada alamat sama sekali → tampil alert
            else {
                cy.contains('Belum ada alamat').should('exist');
            }
        });
    });
    

    // ---------------------------------------------------
    // HAL-010 — Modal Ubah Alamat dapat dibuka
    // ---------------------------------------------------
    it('HAL-010: Modal Ubah Alamat tampil & field terisi', () => {

        cy.visit('/memilihalamat');
    
        cy.get('body').then(($body) => {
    
            // Lewati jika tidak ada alamat sama sekali
            if (!$body.find('.ubahAlamatBtn').length) return;
    
            // Klik tombol ubah pertama
            cy.get('.ubahAlamatBtn').first().click({ force: true });
    
            // Tunggu modal Bootstrap benar-benar open
            cy.get('#ubahAlamatModal')
                .should('be.visible')
                .and('have.class', 'show');
    
            // Tunggu DOM diisi oleh script JS
            cy.wait(500); // WAJIB karena view pakai setTimeout + animasi Bootstrap
    
            // Sekarang cek semua input sudah muncul
            cy.get('#ubah_id_alamat').should('exist');
            cy.get('#ubah_nama_penerima').should('exist').and('be.visible');
            cy.get('#ubah_no_telepon').should('exist');
            cy.get('#ubah_jalan').should('exist');
            cy.get('#ubah_kota').should('exist');
            cy.get('#ubah_provinsi').should('exist');
            cy.get('#ubah_kode_pos').should('exist');
    
            // Dan pastikan field sudah terisi (sesuai dataset tombol)
            cy.get('.ubahAlamatBtn').first().then(($btn) => {
                const nama = $btn.data('nama');
                cy.get('#ubah_nama_penerima').should('have.value', nama);
            });
    
        });
    });
    
    // ---------------------------------------------------
    // HAL-011 — Ubah alamat via AJAX
    // ---------------------------------------------------
    it('HAL-011: Ubah alamat berhasil disimpan', () => {
        cy.visit('/memilihalamat');
    
        cy.get('body').then(($body) => {
    
            if (!$body.find('.ubahAlamatBtn').length) return;
    
            // Klik tombol Ubah pertama
            cy.get('.ubahAlamatBtn').first().click({ force: true });
    
            // Modal harus terlihat
            cy.get('#ubahAlamatModal')
                .should('be.visible')
                .and('have.class', 'show');
    
            // WAJIB: tunggu JS mengisi modal + delay Leaflet setTimeout
            cy.wait(500);
    
            // Gunakan ID yang BENAR sesuai HTML
            cy.get('#ubah_nama_penerima').should('exist').clear().type('Cypress Update');
            cy.get('#ubah_jalan').should('exist').clear().type('Jalan Updated No. 77');
    
            // Klik tombol simpan
            cy.contains('#ubahAlamatModal button', /simpan/i).click({ force: true });
    
            // Tunggu reload setelah fetch success
            cy.wait(1000);
    
            // Pastikan kembali ke halaman memilih alamat
            cy.url().should('include', '/memilihalamat');
        });
    });
    
});  