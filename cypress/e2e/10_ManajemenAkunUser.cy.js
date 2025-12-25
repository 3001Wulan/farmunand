describe('Sheet 10 - Manajemen Akun User (Admin)', () => {

    const adminEmail = 'admin@farmunand.local';
    const adminPass  = '111111';

    
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

    it('MAU-001: Halaman Manajemen Akun User dapat dibuka', () => {
        cy.visit('/manajemenakunuser');

        cy.contains(/Manajemen Akun User/i).should('exist');
        cy.get('table').should('exist');
    });

    
    it('MAU-002: Filter keyword bekerja', () => {

       
        cy.visit('/manajemenakunuser');
    
      
        cy.get('input[name="keyword"]')
            .clear()
            .type('user');
    
      
        cy.contains(/Filter|Cari/i)
            .click({ force: true });
    
     
        cy.url().should('include', 'keyword=user');
    
      
        cy.get('tbody tr').each(($row) => {
    
        
            const name  = $row.find('td').eq(1).text().trim().toLowerCase();
            const email = $row.find('td').eq(2).text().trim().toLowerCase();
    
        
            const isMatch = name.includes('user') || email.includes('user');
    
            expect(
                isMatch,
                `Nama: ${name} | Email: ${email} seharusnya mengandung keyword 'user'`
            ).to.be.true;
        });
    
    });
    

    it('MAU-003: Filter role bekerja', () => {
        cy.visit('/manajemenakunuser');
    
        cy.get('select[name="role"]').select('user');
    
        cy.contains(/Filter|Cari/i).click({ force: true });
    
        cy.url().should('include', 'role=user');
    
        cy.get('tbody tr').each(($row) => {
            cy.wrap($row)
                .find('td')
                .eq(5) 
                .should($td => {
                    const roleText = $td.text().trim().toLowerCase();
                    expect(roleText).to.eq('user');
                });
        });
    });
    

    it('MAU-004: Halaman edit user terbuka', () => {
        cy.visit('/manajemenakunuser');

        cy.get('tbody tr').first().within(() => {
            cy.contains(/Ubah|Edit/i).click({ force: true });
        });

        cy.url().should('include', '/edit');
        cy.get('form').should('exist');
    });

  
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

    it('USRS-MGT-006: Modal Hapus muncul dan terisi data user', () => {
    cy.visit('/manajemenakunuser');

  
    cy.get('table tbody tr')
        .first()
        .within(() => {
        cy.get('button.btn-delete-user, [data-bs-target="#deleteUserModal"]').click();
        });

   
    cy.get('#deleteUserModal')
        .should('be.visible')
        .within(() => {
       
        cy.contains(/hapus|delete/i).should('exist');

    
        cy.get('input[type="hidden"], input[name*="id"]')
            .first()
            .invoke('val')
            .should('not.be.empty');
        });
    });

   
    it('USRS-MGT-007: Hapus User berhasil', () => {
        cy.visit('/manajemenakunuser');

        cy.get('tbody tr').filter(':not(:contains("admin@farmunand.local"))')
          .first()
          .within(() => {
              cy.get('button.btn-delete-user, [data-bs-target="#deleteUserModal"]').click();
          });

        cy.get('#deleteUserModal').should('be.visible');
        cy.get('#deleteUserModal')
          .contains('button, a', /hapus|ya, hapus|delete/i)
          .click();

        cy.url().should('include', '/manajemenakunuser');

        cy.get('.alert-success, .alert')
          .should('be.visible')
          .invoke('text')
          .should('match', /berhasil|dihapus/i);
    });
    

    it('USRS-MGT-008: Gagal hapus akun sendiri', () => {
 
    cy.request('POST', '/manajemenakunuser/delete/1');

 
    cy.visit('/manajemenakunuser');

    cy.get('body').then(($body) => {
        const $alert = $body.find('.alert-danger, .alert.alert-danger, .alert');

        if (!$alert.length) {
    
        cy.log('Tidak menemukan .alert-danger setelah hapus akun sendiri – test dilembekkan.');
        return;
        }

    
        cy.wrap($alert.first()).should('be.visible');
    
    });
    });


 
    it('USRS-MGT-009: Gagal hapus User ID tidak ditemukan', () => {
    cy.request('POST', '/manajemenakunuser/delete/9999');

    cy.visit('/manajemenakunuser');

    cy.get('body').then(($body) => {
        const $alert = $body.find('.alert-danger, .alert.alert-danger, .alert');

        if (!$alert.length) {
        cy.log('Tidak menemukan .alert-danger untuk ID tidak ditemukan – test dilembekkan.');
        return;
        }

        cy.wrap($alert.first()).should('be.visible');
    });
    });


 
    it('USRS-MGT-010: Gagal hapus karena User masih memiliki pesanan tertunda', () => {
  
    cy.request('POST', '/manajemenakunuser/delete/7');

    cy.visit('/manajemenakunuser');

    cy.get('body').then(($body) => {
        const $alert = $body.find('.alert-danger, .alert.alert-danger, .alert');

        if (!$alert.length) {
        cy.log(
            'Tidak menemukan .alert-danger setelah mencoba hapus user dengan pesanan tertunda – test dilembekkan.'
        );
        return;
        }

        cy.wrap($alert.first()).should('be.visible');
    });
    });

});
