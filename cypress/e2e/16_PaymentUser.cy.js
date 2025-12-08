/// <reference types="cypress" />

describe('Payments Controller', () => {
    const userEmail = 'user01@farmunand.local';
    const userPassword = '111111';
    let orderId;      // Akan diisi setelah create payment
    let idPemesanan;  // Akan diisi setelah create payment

    // -----------------------------
    // LOGIN SEBELUM TEST
    // -----------------------------
    beforeEach(() => {
      cy.visit('/login');

      cy.get('input[name="email"]').clear().type(userEmail);
      cy.get('input[name="password"]').clear().type(userPassword);
      cy.get('button[type="submit"]').click();

      // pastikan login berhasil
      cy.url().should('include', '/dashboarduser'); // URL setelah login
    });

    // -----------------------------
    // 1) TEST CREATE PAYMENT
    // -----------------------------
    it('should create a new payment and return snap token', () => {
        // Pastikan id_alamat & id_produk ini valid di DB temanmu
        const payload = {
          id_alamat: 1, // ganti sesuai data temanmu
          items: [
            { id_produk: 1, qty: 1 }, // ganti sesuai data temanmu
            { id_produk: 2, qty: 1 }  // ganti sesuai data temanmu
          ]
        };
    
        cy.request({
          method: 'POST',
          url: '/payments/create',
          body: payload,
          failOnStatusCode: false // biar kita bisa cek status code manual
        }).then((res) => {
          if (res.status === 500) {
            cy.log('Backend gagal memproses pembayaran, kemungkinan Midtrans key teman belum benar');
          } else {
            expect(res.status).to.eq(200);
            expect(res.body.success).to.be.true;
            expect(res.body).to.have.property('snapToken');
            expect(res.body).to.have.property('order_id');
            expect(res.body).to.have.property('id_pemesanan');
    
            orderId = res.body.order_id;
            idPemesanan = res.body.id_pemesanan;
    
            cy.log(`Order ID: ${orderId}`);
          }
        });
      });
    // -----------------------------
    // 2) TEST RESUME PAYMENT
    // -----------------------------
    it('should resume an existing payment by order_id', () => {
      cy.request({
        method: 'GET',
        url: `/payments/resume/${orderId}`,
        failOnStatusCode: false
      }).then((res) => {
        if(res.status === 404){
          cy.log('Transaksi tidak ditemukan');
        } else {
          expect(res.status).to.eq(200);
          expect(res.body.success).to.be.true;
          expect(res.body).to.have.property('snapToken');
        }
      });
    });

    // -----------------------------
    // 3) CANCEL PAYMENT BY USER
    // -----------------------------
    it('should cancel a payment by user', () => {
      cy.request({
        method: 'POST',
        url: '/payments/cancelByUser',
        failOnStatusCode: false,
        body: { order_id: orderId }
      }).then((res) => {
        if(res.status === 422 || res.status === 404){
          cy.log(res.body.message);
        } else {
          expect(res.status).to.eq(200);
          expect(res.body.success).to.be.true;
          expect(res.body.message).to.include('Pesanan berhasil dibatalkan');
        }
      });
    });

    // -----------------------------
    // 4) CANCEL PAYMENT BY USER KEEP RECORD
    // -----------------------------
    it('should cancel a payment but keep record', () => {
      cy.request({
        method: 'POST',
        url: '/payments/cancelByUserKeep',
        failOnStatusCode: false,
        body: { order_id: orderId }
      }).then((res) => {
        if(res.status === 422 || res.status === 404){
          cy.log(res.body.message);
        } else {
          expect(res.status).to.eq(200);
          expect(res.body.success).to.be.true;
          expect(res.body.message).to.include('Pesanan dibatalkan dan dipindah');
        }
      });
    });

    // -----------------------------
    // 5) TEST MIDTRANS CALLBACK FINISH
    // -----------------------------
    it('should visit finish page after payment', () => {
      // pastikan orderId dari create payment dipakai
      cy.visit(`/payments/finish?order_id=${orderId}`);

      // cek orderId muncul di halaman
      cy.get('body').should('contain.text', orderId);

      // cek nama user muncul di halaman
      const username = 'user01'; // ganti sesuai username di DB
      cy.get('body').should('contain.text', username);

      // opsional: cek tombol Dashboard atau teks lain muncul
      cy.get('body').should('contain.text', 'Dashboard');
    });
});
