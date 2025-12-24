describe('Sheet 08 - Keranjang Belanja', () => {
  const userEmail = 'user01@farmunand.local';
  const userPassword = '111111';
  const testProductId = 1;

  const cartItemSelector = '.table tbody tr';

  beforeEach(() => {
    cy.visit('/login');

    cy.get('input[name="email"]').clear().type(userEmail);
    cy.get('input[name="password"]').clear().type(userPassword);

    cy.get('input[name="password"]').closest('form').submit();

    cy.url().should('not.include', '/login');

    cy.visit('/keranjang');
    cy.url().should('include', '/keranjang');
    cy.contains('.card-h', /Keranjang Saya/i).should('exist');
  });

  function emptyCartIfAny() {
    cy.visit('/keranjang');

    cy.get('body').then(($body) => {
      const hasClearBtn = $body.find(
        'button.btn-outline-danger:contains("Kosongkan")'
      ).length;

      if (hasClearBtn) {
        cy.window().then((win) => {
          cy.stub(win, 'confirm').returns(true);
        });

        cy.contains('button.btn-outline-danger', /Kosongkan/i).click({
          multiple: true,
        });

        cy.url().should('include', '/keranjang');
        cy.contains('td', /Keranjang masih kosong/i).should('exist');
      }
    });
  }

  function ensureCartHasSingleItem() {
    emptyCartIfAny();

    cy.visit(`/detailproduk/${testProductId}`);
    cy.contains('button', /Masukkan Keranjang/i).first().click();

    cy.visit('/keranjang');
    cy.url().should('include', '/keranjang');

    cy.get(cartItemSelector)
      .not(':contains("Keranjang masih kosong")')
      .should('have.length', 1);
  }

  it('USR-CART-002: Tambah produk dan verifikasi item muncul di keranjang', () => {
    emptyCartIfAny();

    cy.visit(`/detailproduk/${testProductId}`);
    cy.contains('button', /Masukkan Keranjang/i).first().click();

    cy.url().should('include', '/keranjang');

    cy.get(cartItemSelector)
      .should('have.length.of.at.least', 1)
      .first()
      .within(() => {
        cy.get('.fw-semibold')
          .should('exist')
          .invoke('text')
          .should('not.be.empty');

        cy.get('.view-state span.badge').should('contain', '1');
      });

    cy.get('tfoot th.text-start')
    .invoke('text')
    .then((text) => {
        expect(text.trim()).to.match(/Rp\s?\d{1,3}(\.\d{3})*/);
    });

  });

it('USR-CART-003: Mengubah kuantitas item', () => {
  const targetQty = 3;

  ensureCartHasSingleItem();

  cy.get(cartItemSelector)
    .not(':contains("Keranjang masih kosong")')
    .first()
    .within(() => {
      cy.contains('button', /Ubah/i)
        .filter(':visible')
        .first()
        .click({ force: true });

      cy.get('.edit-state')
        .should('exist')
        .and('not.have.class', 'd-none')
        .within(() => {
          cy.get('input[name="qty"][type="number"]')
            .clear({ force: true })
            .type(String(targetQty), { force: true });
          cy.contains('button', /Simpan/i)
            .filter(':visible')
            .first()
            .click({ force: true });
        });
    });
  cy.url().should('include', '/keranjang');
  cy.get(cartItemSelector)
    .not(':contains("Keranjang masih kosong")')
    .first()
    .within(() => {
      cy.get('.view-state span.badge').should('contain', String(targetQty));
    });
});

  it('USR-CART-004: Menghapus item dari keranjang', () => {
    const deleteButtonSelector = '.btn-delete-item';

    ensureCartHasSingleItem();

    cy.get(cartItemSelector)
      .not(':contains("Keranjang masih kosong")')
      .first()
      .as('initialItem');

    cy.get('@initialItem').find(deleteButtonSelector).click();

    cy.get('#deleteCartItemModal')
      .should('be.visible')
      .within(() => {
        cy.contains('button.btn-danger', /Ya, Hapus/i).click();
      });

    cy.url().should('include', '/keranjang');
    cy.wait(500);

    cy.contains('td', /Keranjang masih kosong/i).should('exist');
    cy.get(cartItemSelector).should('have.length', 1);
  });

  it('USR-CART-005: Tombol Checkout Semua mengarahkan ke halaman pemesanan', () => {
    ensureCartHasSingleItem();

    cy.contains('button.btn-secondary', /Checkout Semua/i)
      .first()
      .click();

    cy.url().should('include', '/melakukanpemesanan');

    cy.contains('h1, h2, h3, h4', /Pemesanan|Checkout|Alamat|Ringkasan/i)
      .should('exist');
  });

  it('USR-CART-006: Tombol Checkout 1 produk mengarahkan ke halaman pemesanan', () => {
    ensureCartHasSingleItem();

    cy.get(cartItemSelector)
      .not(':contains("Keranjang masih kosong")')
      .first()
      .within(() => {
        cy.contains('button', /^Checkout$/i).click();
      });

    cy.url().should('include', '/melakukanpemesanan');

    cy.contains('h1, h2, h3, h4', /Pemesanan|Checkout|Alamat|Ringkasan/i)
      .should('exist');
  });

  it('USR-CART-007: Tombol Kosongkan mengosongkan keranjang', () => {
    ensureCartHasSingleItem();

    cy.window().then((win) => {
      cy.stub(win, 'confirm').returns(true);
    });

    cy.contains('button.btn-outline-danger', /Kosongkan/i).click();

    cy.url().should('include', '/keranjang');
    cy.contains('td', /Keranjang masih kosong/i).should('exist');
  });

it('USR-CART-008: Tombol Lanjut Belanja mengarahkan ke Dashboard User', () => {
  cy.contains('a.btn-outline-secondary', /Lanjut Belanja/i).click();

  cy.url().should('include', '/dashboarduser');
  cy.url().should('not.include', '/login');
});


});