// Test Suite: Verifikasi Halaman dan Fungsionalitas Manajemen Akun User (Admin)

describe('Sheet 10 - Manajemen Akun User (Admin)', () => {

    const adminEmail = 'admin@farmunand.local';
    const adminPass  = '111111';

    // =====================================================================
    // Login Helper
    // =====================================================================
    function loginAdmin() {
        cy.visit('/login');
        cy.get('input[name="email"]').clear().type(adminEmail);
        cy.get('input[name="password"]').clear().type(adminPass);
        cy.contains(/login/i).click();
        cy.url({ timeout: 10000 }).should('not.include', '/login');
    }

    beforeEach(() => {
        loginAdmin();
    });

    // =====================================================================
    // 1. Halaman dapat dibuka
    // =====================================================================
    it('MAU-001: Halaman Manajemen Akun User dapat dibuka', () => {
        cy.visit('/manajemenakunuser');

        cy.contains(/Manajemen Akun User/i).should('exist');
        cy.get('table').should('exist');
    });

    // =====================================================================
    // 2. Filter keyword bekerja (FINAL FIXED)
    // =====================================================================
    it('MAU-002: Filter keyword bekerja', () => {

        // 1. Buka halaman manajemen akun user
        cy.visit('/manajemenakunuser');
    
        // 2. Isi keyword pencarian
        cy.get('input[name="keyword"]')
            .clear()
            .type('user');
    
        // 3. Klik tombol filter / cari
        cy.contains(/Filter|Cari/i)
            .click({ force: true });
    
        // 4. URL harus mengandung parameter keyword
        cy.url().should('include', 'keyword=user');
    
        // 5. Validasi setiap row hasil filter
        cy.get('tbody tr').each(($row) => {
    
            // Ambil kolom nama (td index 1) + email (td index 2)
            const name  = $row.find('td').eq(1).text().trim().toLowerCase();
            const email = $row.find('td').eq(2).text().trim().toLowerCase();
    
            // Validasi bahwa keyword “user” cocok dengan nama atau email
            const isMatch = name.includes('user') || email.includes('user');
    
            expect(
                isMatch,
                `Nama: ${name} | Email: ${email} seharusnya mengandung keyword 'user'`
            ).to.be.true;
        });
    
    });
    

    // =====================================================================
    // 3. Filter role bekerja
    // =====================================================================
    it('MAU-003: Filter role bekerja', () => {
        cy.visit('/manajemenakunuser');
    
        cy.get('select[name="role"]').select('user');
    
        cy.contains(/Filter|Cari/i).click({ force: true });
    
        cy.url().should('include', 'role=user');
    
        cy.get('tbody tr').each(($row) => {
            cy.wrap($row)
                .find('td')
                .eq(5) // <-- UBAH KE INDEX ROLE YANG BENAR
                .should($td => {
                    const roleText = $td.text().trim().toLowerCase();
                    expect(roleText).to.eq('user');
                });
        });
    });
    

    // =====================================================================
    // 4. Halaman edit user dapat dibuka
    // =====================================================================
    it('MAU-004: Halaman edit user terbuka', () => {
        cy.visit('/manajemenakunuser');

        cy.get('tbody tr').first().within(() => {
            cy.contains(/Ubah|Edit/i).click({ force: true });
        });

        cy.url().should('include', '/edit');
        cy.get('form').should('exist');
    });

    // =====================================================================
    // 5. Update user berhasil
    // =====================================================================
    it('MAU-005: Update user berhasil', () => {
        cy.visit('/manajemenakunuser');

        cy.get('tbody tr').first().within(() => {
            cy.contains(/Ubah|Edit/i).click({ force: true });
        });

        cy.get('input[name="nama"]').clear().type('User Automasi');
        cy.get('input[name="no_hp"]').clear().type('081234567890');

        cy.contains(/Simpan/i).click({ force: true });

        cy.url().should('include', '/manajemenakunuser');
        cy.contains(/User diperbarui/i).should('exist');
    });

    // =====================================================================
    // 6. Tidak bisa menghapus akun sendiri
    // =====================================================================
    it('MAU-006: Tidak bisa menghapus akun sendiri', () => {
        cy.visit('/manajemenakunuser');
    
        // 1. Klik tombol hapus pada akun admin
        cy.contains('td', adminEmail)
          .parent()
          .within(() => {
              cy.contains(/Hapus/i).click({ force: true });
          });
    
        // 2. Modal konfirmasi harus muncul
        cy.get('#deleteUserModal').should('be.visible');
    
        // 3. Klik tombol "Ya, Hapus"
        cy.get('#deleteUserModal')
          .find('button.btn-danger')
          .click({ force: true });
    
        // 4. Setelah submit, admin TIDAK BOLEH hilang dari tabel
        cy.contains('td', adminEmail).should('exist');
    });
    
    

    // =====================================================================
    // 7. Tidak bisa menghapus user yang memiliki pesanan pending
    // =====================================================================
    it('MAU-007: Cegah hapus user yang punya pesanan pending', () => {
        cy.visit('/manajemenakunuser');
    
        const pendingOrderEmail = 'user01@farmunand.local';
    
        // Pastikan user ada
        cy.contains('td', pendingOrderEmail)
          .should('exist')
          .parent()
          .within(() => {
              cy.contains(/Hapus/i).click({ force: true });
          });
    
        // Modal muncul
        cy.get('#deleteUserModal').should('be.visible');
    
        // Klik konfirmasi hapus
        cy.get('#deleteUserModal')
          .find('button.btn-danger')
          .click({ force: true });
    
        // User TIDAK BOLEH hilang dari tabel
        cy.contains('td', pendingOrderEmail).should('exist');
    });
    
    // =====================================================================
    // 8. Hapus user valid (bukan admin & tidak punya pesanan)
    // =====================================================================
    it('MAU-008: Hapus user valid berhasil', () => {
        cy.visit('/manajemenakunuser');

        cy.get('tbody tr').each(($tr) => {

            const role = $tr.find('td').eq(2).text().trim().toLowerCase();
            const email = $tr.find('td').eq(1).text().trim();

            // syarat user dapat dihapus:
            if (role === 'user' && email !== adminEmail) {
                cy.wrap($tr).within(() => {
                    cy.contains(/Hapus/i).click({ force: true });
                });

                cy.contains(/User dihapus/i).should('exist');
                return false; // stop looping
            }
        });
    });

});
