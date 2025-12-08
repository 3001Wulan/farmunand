// cypress/e2e/12_admin_laporan.cy.js

describe('Sheet 10 - Admin Laporan Penjualan', () => {
  const adminEmail = 'admin@farmunand.local';
  const adminPassword = '111111';

  beforeEach(() => {
    // Login admin dulu
    cy.visit('/login');

    cy.get('input[name="email"]').clear().type(adminEmail);
    cy.get('input[name="password"]').clear().type(adminPassword);

    cy.contains('button, input[type="submit"]', /login/i).click();

    // Pastikan masuk ke dashboard admin
    cy.url().should('include', '/dashboard');
  });

  // ------------------------------------------------------------------
  // LAP-001: Halaman laporan bisa dibuka dan menampilkan filter
  // ------------------------------------------------------------------
  it('LAP-001: Halaman laporan bisa dibuka dan menampilkan filter', () => {
    cy.visit('/melihatlaporan');

    // Judul / label laporan (bebas tag, yang penting teksnya ada)
    cy.contains(/Laporan Penjualan|Laporan Transaksi/i).should('exist');

    // Cari input tanggal (biasanya type="date")
    cy.get('body').then(($body) => {
      const dateInputs = $body.find('input[type="date"]');

      expect(dateInputs.length, 'minimal 1 input tanggal di halaman laporan')
        .to.be.greaterThan(0);
    });

    // Minimal ada tabel / area hasil laporan
    cy.get('table, .table').should('exist');
  });

  // ------------------------------------------------------------------
  // LAP-002: Filter tanggal dapat diterapkan
  // ------------------------------------------------------------------
  it('LAP-002: Filter tanggal dapat diterapkan', () => {
    cy.visit('/melihatlaporan');

    const start = '2025-01-01';
    const end = '2025-12-31';

    cy.get('body').then(($body) => {
      const $dateInputs = $body.find('input[type="date"]');

      if (!$dateInputs.length) {
        // Kalau tidak ada input tanggal sama sekali, ini sebenarnya bug,
        // tapi supaya tes tidak merah karena selector, kita tulis log yang jelas.
        cy.log('Tidak ditemukan input[type="date"] di halaman laporan.');
        expect($dateInputs.length, 'input tanggal di halaman laporan').to.be.greaterThan(0);
        return;
      }

      // Isi tanggal mulai & selesai (kalau ada 2 input)
      cy.wrap($dateInputs.eq(0))
        .clear({ force: true })
        .type(start, { force: true });

      if ($dateInputs.length > 1) {
        cy.wrap($dateInputs.eq(1))
          .clear({ force: true })
          .type(end, { force: true });
      }

      // Cari tombol Filter / Tampilkan / Cari di halaman
      cy.get('button, input[type="submit"], a')
        .filter((_, el) => {
          const txt = (el.innerText || el.value || '').trim();
          return /Filter|Terapkan|Tampilkan|Cari/i.test(txt);
        })
        .first()
        .click({ force: true });

      // Setelah submit, tetap di halaman laporan
      cy.url().should('include', 'melihatlaporan');

      // dan tabel laporan tampil
      cy.get('table, .table').should('exist');
    });
  });

  // ------------------------------------------------------------------
  // LAP-003: Export Excel dapat dipanggil dan berjalan
  // ------------------------------------------------------------------
  it('LAP-003: Export Excel dapat dipanggil dan berhasil', () => {
    cy.visit('/melihatlaporan');

    // Pastikan tombol/link Export Excel ada
    cy.contains('a, button', /Export Excel|Unduh Excel|Download Excel/i)
      .first()
      .should('exist')
      .then(($btn) => {
        const href = $btn.attr('href') || '';

        // Href mengarah ke route export (tidak harus persis, cukup mengandung kata export)
        expect(
          /export/i.test(href),
          `href tombol export mengandung 'export', actual: ${href}`
        ).to.be.true;

        // Klik untuk trigger download
        cy.wrap($btn).click({ force: true });
      });

    // Beri waktu browser memproses download
    cy.wait(1500);

    // Pastikan tetap di halaman laporan (bukan redirect ke halaman lain)
    cy.url().should('include', 'melihatlaporan');
  });
});
