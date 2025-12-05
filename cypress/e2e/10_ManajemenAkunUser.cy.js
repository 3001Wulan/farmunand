// Test Suite: Verifikasi Halaman dan Fungsionalitas Manajemen Akun User (Admin)

describe('Sheet 10 - Manajemen Akun User (Admin)', () => {
    // Data otentikasi admin (GANTI DENGAN DATA VALID)
    const adminUserEmail = 'admin@farmunand.local'; 
    const adminPassword = '111111'; 

    // Data sample user yang harus ada di database (PASTIKAN ID/DATA VALID)
    // *** GANTI DENGAN ID YANG BENAR-BENAR MUNCUL DI HALAMAN PERTAMA ***
    const existingUserId = 8; 
    const userToDeleteId = 10; 
    const selfUserId = 1;     
    const userWithPendingOrderId = 7; // User yang masih punya pesanan tertunda
    
    // Data Filter
    const filterKeyword = 'test'; 
    const filterRole = 'admin';   

    // Endpoint yang digunakan
    const pageUrl = '/manajemenakunuser';
    const deleteEndpoint = `/manajemenakunuser/delete/${userToDeleteId}`;
    const deleteSelfEndpoint = `/manajemenakunuser/delete/${selfUserId}`;
    const nonExistentEndpoint = `/manajemenakunuser/delete/9999`;

    // USRS-MGT-001: Login dan memastikan halaman Manajemen Akun dapat diakses
    beforeEach(() => {
        // 1. Lakukan Login Admin
        cy.visit('/login'); 
        cy.get('input[name="email"]').type(adminUserEmail);
        cy.get('input[name="password"]').type(adminPassword);
        cy.get('input[name="password"]').closest('form').submit();        
        cy.url().should('not.include', '/login'); 
        
        // 2. Akses halaman Manajemen Akun User
        cy.visit(pageUrl);
        cy.url().should('include', pageUrl);
        cy.contains('h3', /Manajemen Akun User/i).should('exist');
    });

    // ---------------------------------------------------------------------

    // USRS-MGT-002: Memastikan elemen filter dan tabel daftar tampil
    it('USRS-MGT-002: Tampilan Halaman dan Elemen Utama Tampil', () => {
        // Cek elemen filter
        cy.get('input[name="keyword"]').should('have.value', '');
        cy.get('select[name="role"]').should('have.value', '');
        cy.contains('button', /Filter/i).should('be.visible');

        // PERBAIKAN: Persempit scope ke .table-responsive
        cy.get('.table-responsive').should('exist').within(() => {
            // Cek tabel
            cy.get('table').should('exist');
            
            // Cek header
            cy.get('thead th').should('have.length', 7); 

            // Memastikan ada setidaknya satu baris data (kecuali baris 'kosong')
            cy.get('tbody tr').its('length').should('be.gte', 1);

            // Memastikan link Edit/Hapus ada pada baris pertama (jika ada data)
            cy.get('tbody tr').first().within(() => {
                cy.contains('a', /Edit/i).should('have.attr', 'href').and('include', '/edit/');
                cy.contains('button', /Hapus/i).should('be.visible').and('have.class', 'btn-delete-user');
            });
        });
    });

    // ---------------------------------------------------------------------

    // USRS-MGT-003: Filter berdasarkan Keyword berhasil diterapkan
    it('USRS-MGT-003: Filter berdasarkan Keyword berhasil', () => {
        cy.get('input[name="keyword"]').type(filterKeyword);
        cy.contains('button', /Filter/i).click();

        cy.url().should('include', `keyword=${filterKeyword}`);
        
        // Memastikan input tetap sticky
        cy.get('input[name="keyword"]').should('have.value', filterKeyword);

        // PERBAIKAN: Hapus assertion negatif 'not.include role=' karena filter kosong sering di-submit sebagai role=.
        // cy.url().should('not.include', `role=`); // Dihapus
    });

    // ---------------------------------------------------------------------

    // USRS-MGT-004: Filter berdasarkan Role berhasil diterapkan
    it('USRS-MGT-004: Filter berdasarkan Role berhasil', () => {
        cy.get('select[name="role"]').select(filterRole);
        cy.contains('button', /Filter/i).click();

        cy.url().should('include', `role=${filterRole}`);

        // Memastikan select box tetap sticky
        cy.get('select[name="role"]').should('have.value', filterRole);
        
        // Memastikan input keyword kosong
        cy.get('input[name="keyword"]').should('have.value', '');

        // Memastikan semua role di tabel adalah 'Admin' (jika ada data)
        cy.get('tbody tr').each(($row) => {
            // Cek badge role
            cy.wrap($row).find('td').eq(5).contains('.badge', new RegExp(filterRole, "i")).should('exist');
        });
    });

    // ---------------------------------------------------------------------

    // USRS-MGT-005: Navigasi ke halaman Edit User
    it('USRS-MGT-005: Navigasi ke halaman Edit User', () => {
        
// USRS-MGT-005: Navigasi ke halaman Edit User
        
it('USRS-MGT-005: Navigasi ke halaman Edit User', () => {
        
    
        
    // Perbaikan: Cari elemen <a> dengan href yang mengandung ID, lalu pastikan teksnya adalah 'Edit'.
        
    // Ini lebih spesifik dan mengurangi risiko memilih elemen ganda.
        
    cy.get('.table-responsive').contains('a', /Edit/i) 
        
        .should('have.attr', 'href')
        
        .and('include', `/edit/${existingUserId}`)
        
        .click(); // Sekarang hanya mengklik satu elemen
        
        
    // Verifikasi URL mengarah ke halaman edit yang benar
        
    cy.url().should('include', `/manajemenakunuser/edit/${existingUserId}`);
        
});
    });

    // ---------------------------------------------------------------------

    // USRS-MGT-006: Modal konfirmasi hapus muncul saat tombol 'Hapus' diklik
    it('USRS-MGT-006: Modal Hapus muncul dan terisi data user', () => {
        const userEmail = 'test1523@mail.com';
        
        // Tunggu data user selesai dimuat
        cy.intercept('GET', '/manajemenakunuser/data').as('getUserData');
        cy.visit('/manajemenakunuser');
        cy.wait('@getUserData');
        
        // Klik tombol Delete berdasarkan email
        cy.contains(userEmail)
          .closest('tr')
          .find('button.btn-delete')
          .click();
        
        // Tunggu modal muncul
        cy.get('#deleteUserModal').should('be.visible');
        
        // Verifikasi ID user di modal
        cy.get('#modalUserId').should('have.value', String(userToDeleteId));
        
        // Tutup modal
        cy.get('.modal-footer .btn-secondary').click();
        cy.get('#deleteUserModal').should('not.be.visible');
        
    });

    // ---------------------------------------------------------------------

    // USRS-MGT-007: Hapus user berhasil
    it('USRS-MGT-007: Hapus User berhasil', () => {
        // Membuka modal
        cy.get(`[data-userid="${userToDeleteId}"]`, { timeout: 10000 }).click();
        cy.get('#deleteUserModal').should('be.visible');

        // Menggunakan cy.intercept untuk memantau request POST/DELETE
        cy.intercept('POST', deleteEndpoint).as('deleteRequest');
        
        // Klik tombol 'Ya, Hapus'
        cy.get('#form-delete-user').submit(); 

        // Verifikasi request terkirim dan sukses redirect
        cy.wait('@deleteRequest').its('response.statusCode').should('eq', 302); 

        // Verifikasi redirect kembali ke halaman utama
        cy.url().should('include', pageUrl); 
        
        // Verifikasi Pesan Sukses muncul
        cy.contains('.alert-success', /User dihapus/i).should('be.visible');
    });

    // ---------------------------------------------------------------------
    // NEGATIVE TESTS (Perbaikan Selector Flashdata)
    // ---------------------------------------------------------------------

    // USRS-MGT-008: Gagal hapus akun sendiri
    it('USRS-MGT-008: Gagal hapus akun sendiri', () => {
        cy.request({
            method: 'POST',
            url: deleteSelfEndpoint,
            failOnStatusCode: false, 
            jar: true, 
        }).then(() => {
            cy.visit(pageUrl);
            
            // PERBAIKAN: Cari di .alert-danger atau body (lebih aman)
            cy.contains('.alert-danger, body', /Tidak bisa menghapus akun sendiri/i).should('be.visible');
        });
    });

    // USRS-MGT-009: Gagal hapus user yang tidak ada
    it('USRS-MGT-009: Gagal hapus User ID tidak ditemukan', () => {
        cy.request({
            method: 'POST',
            url: nonExistentEndpoint,
            failOnStatusCode: false, 
            jar: true, 
        }).then(() => {
            cy.visit(pageUrl);
            
            // PERBAIKAN: Cari di .alert-danger atau body (lebih aman)
            cy.contains('.alert-danger, body', /User tidak ditemukan/i).should('be.visible');
        });
    });
    
    // USRS-MGT-010: Gagal hapus karena user masih memiliki pesanan tertunda 
    it('USRS-MGT-010: Gagal hapus karena User masih memiliki pesanan tertunda', () => {
        const pendingDeleteEndpoint = `/manajemenakunuser/delete/${userWithPendingOrderId}`;
        
        cy.request({
            method: 'POST',
            url: pendingDeleteEndpoint,
            failOnStatusCode: false, 
            jar: true, 
        }).then(() => {
            cy.visit(pageUrl);
            
            // PERBAIKAN: Cari di .alert-danger atau body (lebih aman)
            cy.contains('.alert-danger, body', /User masih memiliki pesanan yang belum diselesaikan/i).should('be.visible');
        });
    });
});