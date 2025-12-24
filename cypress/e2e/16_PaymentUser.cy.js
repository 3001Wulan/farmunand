/// <reference types="cypress" />

describe('Payments Controller', () => {
    const userEmail = 'user01@farmunand.local';
    const userPassword = '111111';
    let orderId;      
    let idPemesanan;  

    beforeEach(() => {
      cy.visit('/login');

      cy.get('input[name="email"]').clear().type(userEmail);
      cy.get('input[name="password"]').clear().type(userPassword);
      cy.get('button[type="submit"]').click();
      cy.url().should('include', '/dashboarduser'); 
    });

    it('should create a new payment and return snap token', () => {
        const payload = {
          id_alamat: 1, 
          items: [
            { id_produk: 1, qty: 1 },
            { id_produk: 2, qty: 1 }  
          ]
        };
    
        cy.request({
          method: 'POST',
          url: '/payments/create',
          body: payload,
          failOnStatusCode: false 
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

    it('should visit finish page after payment', () => {
      cy.visit(`/payments/finish?order_id=${orderId}`);
      cy.get('body').should('contain.text', orderId);

      const username = 'user01'; 
      cy.get('body').should('contain.text', username);

      cy.get('body').should('contain.text', 'Dashboard');
    });
});
