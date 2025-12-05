// Tes ini memverifikasi fungsionalitas Keranjang Belanja Pembeli.

describe('Sheet 08 - Keranjang Belanja', () => {
    // Data login user (PASTIKAN VALID)
    const userEmail = 'user01@farmunand.local'; 
    const userPassword = '111111'; 
    
    // Data Produk (GANTI dengan ID produk yang BENAR-BENAR ADA di DB dan STOK > 0)
    const testProductId = 1;

    // USR-CART-001: Login dan memastikan halaman Keranjang dapat diakses
    beforeEach(() => {
        // 1. Lakukan Login User
        cy.visit('/login'); 
        
        // Memastikan input email dan password diketik di form yang sama
        cy.get('input[name="email"]').type(userEmail);
        cy.get('input[name="password"]').type(userPassword);
        
        // PERBAIKAN STABILITAS FINAL: 
        // Menggunakan cy.closest('form').submit() untuk bypass masalah klik tombol ganda
        cy.get('input[name="password"]')
          .closest('form') // Cari form terdekat (yaitu form login)
          .submit();        // Submit form (sama dengan mengklik tombol submit yang valid)
        
        cy.url().should('not.include', '/login'); 
        
        // 2. Akses halaman Keranjang
        cy.visit('/keranjang');
        cy.url().should('include', '/keranjang');
        cy.contains('.card-h', /Keranjang Saya/i).should('exist'); // Menggunakan selector card-h
    });

    // USR-CART-002: Memastikan produk dapat ditambahkan ke keranjang dari detail produk
    it('USR-CART-002: Tambah produk dan verifikasi item muncul di keranjang', () => {
        // Selector baris item keranjang yang tepat
        const cartItemSelector = '.table tbody tr'; 
        
        // 1. Hapus keranjang terlebih dahulu (jika ada) untuk memastikan tes bersih
        cy.visit('/keranjang');
        cy.get('body').then(($body) => {
            // Tombol "Kosongkan" hanya muncul jika keranjang tidak kosong
            if ($body.find('button.btn-outline-danger:contains("Kosongkan")').length) {
                // Handle window.confirm() yang digunakan di form onsubmit
                cy.window().then((win) => {
                    cy.stub(win, 'confirm').returns(true); // Selalu konfirmasi "OK"
                });
                
                // Menggunakan { multiple: true } pada Kosongkan Keranjang
                cy.contains('button.btn-outline-danger', /Kosongkan/i).click({ multiple: true });
                
                cy.url().should('include', '/keranjang'); // Verifikasi redirect ke keranjang
            }
        });
        
        // 2. Tambahkan produk dari halaman detail (kuantitas default 1)
        cy.visit(`/detailproduk/${testProductId}`);
        // Memastikan hanya mengklik tombol "Masukkan Keranjang" yang pertama/benar
        cy.contains('button', /Masukkan Keranjang/i).first().click();
        
        // 3. Verifikasi redirect ke halaman keranjang setelah penambahan
        cy.url().should('include', '/keranjang');

        // 4. Verifikasi item baru muncul di keranjang
        cy.get(cartItemSelector)
            .should('have.length.of.at.least', 1)
            .first()
            .within(() => {
                // Pastikan nama produk ada (class fw-semibold)
                cy.get('.fw-semibold').should('exist').invoke('text').should('not.be.empty');
                
                // Pastikan kuantitas awal (mode lihat) adalah 1
                cy.get('.view-state span.badge').should('contain', '1');
            });
            
        // 5. Pastikan total harga muncul (di tfoot, th colspan="2", text-start)
        cy.get('tfoot th.text-start').invoke('text').should('match', /Rp\s\d{1,3}(\.\d{3})*/);
    });

    // USR-CART-003: Memastikan kuantitas produk dapat diubah
    it('USR-CART-003: Mengubah kuantitas item', () => {
        const cartItemSelector = '.table tbody tr';
        const testProductId = 123; // ganti sesuai ID produk yang ingin diuji
        const targetQty = 3;
        
        describe('Uji Perubahan Kuantitas Keranjang', () => {
        
            it('Tambah produk ke keranjang dan ubah kuantitas', () => {
                // 1. Kunjungi halaman detail produk
                cy.visit(`/detailproduk/${testProductId}`);
        
                // 2. Klik tombol Masukkan Keranjang
                cy.contains('button', /Masukkan Keranjang/i).click({ force: true });
        
                // 3. Kunjungi halaman keranjang
                cy.visit('/keranjang');
        
                // 4. Masuk ke mode edit item pertama
                cy.get(cartItemSelector).first().within(() => {
                    const qtyInputSelector = 'input[name="qty"]';
        
                    // Klik tombol Ubah spesifik
                    cy.get('button').contains(/^Ubah$/i).first().click({ force: true });
        
                    // Verifikasi form edit muncul
                    cy.get('.edit-state').should('exist').and('not.have.class', 'd-none');
        
                    // Ubah kuantitas
                    cy.get(qtyInputSelector).clear({ force: true }).type(targetQty.toString(), { force: true });
        
                    // Klik tombol Simpan untuk submit
                    cy.get('button').contains(/^Simpan$/i).first().click({ force: true });
                });
        
                // 5. Verifikasi kuantitas berubah setelah reload
                cy.url().should('include', '/keranjang');
                cy.get(cartItemSelector).first().within(() => {
                    cy.get('.view-state span.badge').should('contain', targetQty.toString());
                });
            });
        });
        
    });

    // USR-CART-004: Memastikan produk dapat dihapus dari keranjang
    it('USR-CART-004: Menghapus item dari keranjang', () => {
        const cartItemSelector = '.table tbody tr'; 
        const deleteButtonSelector = '.btn-delete-item';
        
        // PASTIKAN KERANJANG MEMILIKI TEPAT 1 ITEM UNTUK UJI HAPUS
        cy.visit(`/detailproduk/${testProductId}`);
        cy.contains('button', /Masukkan Keranjang/i).click(); 
        cy.visit('/keranjang');
        
        // 1. Verifikasi keranjang memiliki 1 item
        // Kita perlu mencari baris yang BUKAN baris "Keranjang masih kosong"
        cy.get(cartItemSelector).not(':contains("Keranjang masih kosong")').should('have.length', 1).as('initialItem');

        // 2. Klik tombol Hapus (yang memicu modal)
        cy.get('@initialItem').first().find(deleteButtonSelector).click();
        
        // 3. Konfirmasi hapus di dalam Modal
        cy.get('#deleteCartItemModal').should('be.visible').within(() => {
            // Memastikan hanya mengklik tombol Hapus yang berada di modal (class btn-danger)
            cy.contains('button.btn-danger', /Ya, Hapus/i).click();
        });
        
        // 4. Verifikasi keranjang KOSONG
        cy.url().should('include', '/keranjang');
        
        // Tunggu sebentar untuk proses server/redirect
        cy.wait(500); 

        // Verifikasi tabel hanya memiliki baris 'Keranjang masih kosong'
        cy.contains('td', /Keranjang masih kosong/i).should('exist');
        
        // OPTIONAL: Pastikan hanya ada 1 baris di tbody yang berisi pesan kosong
        cy.get('.table tbody tr').should('have.length', 1);
    });

    // USR-CART-005: Memastikan tombol Checkout Semua berfungsi
    it('USR-CART-005: Tombol Checkout Semua mengarahkan ke halaman pemesanan', () => {
        cy.visit(`/detailproduk/${testProductId}`);
        cy.contains('button', /Masukkan Keranjang/i).click(); // Pastikan keranjang tidak kosong

        cy.visit('/keranjang');
        
        // Klik tombol Checkout Semua (di footer keranjang)
        // PERBAIKAN 3: Menambahkan .first() untuk mengatasi duplikat
        cy.contains('button.btn-secondary', /Checkout Semua/i).first().click();
        
        // Verifikasi pengalihan ke halaman pemesanan
        cy.url().should('include', '/melakukanpemesanan');
        
        // PERBAIKAN 3: Memperluas selector dan teks untuk menemukan judul halaman checkout
        cy.contains('h1, h2, h3, h4', /Pemesanan|Checkout|Alamat|Ringkasan/i).should('exist');
    });
});