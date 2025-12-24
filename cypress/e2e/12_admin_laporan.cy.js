/// <reference types="cypress" />

describe('Admin - Laporan Penjualan (Comprehensive Tests)', () => {
  const adminEmail = 'admin@farmunand.local';
  const adminPassword = '111111';

  // -----------------------------
  // LOGIN SEBELUM SETIAP TEST
  // -----------------------------
  beforeEach(() => {
    cy.visit('/login');

    // Intercept untuk memastikan login selesai sebelum melangkah ke fitur
    cy.intercept('POST', '**/auth/doLogin').as('loginReq');

    cy.get('input[name="email"]').clear().type(adminEmail);
    cy.get('input[name="password"]').clear().type(adminPassword);

    cy.get('button, input[type="submit"]').contains(/login/i).click();

    cy.wait('@loginReq');
    cy.url().should('include', '/dashboard');
    cy.contains(/dashboard/i).should('exist');
  });

  // -----------------------------
  // 1. Validasi Halaman & Struktur
  // -----------------------------
  it('LAP-001: Halaman laporan berhasil ditampilkan dengan struktur yang benar', () => {
    cy.visit('/melihatlaporan');

    // Cek Judul & Tabel
    cy.contains(/Laporan Penjualan|Laporan Transaksi/i).should('exist');
    cy.get('table, .table').should('exist');

    // Cek ketersediaan input filter
    cy.get('input[name="start"]').should('exist');
    cy.get('input[name="end"]').should('exist');
    cy.get('select[name="status"]').should('exist');

    // Validasi Header Tabel (LPR-009)
    cy.get('thead tr th').then(($th) => {
      const headers = $th.text();
      expect(headers).to.include('No');
      expect(headers).to.include('Nama Pembeli');
      expect(headers).to.include('Produk');
      expect(headers).to.include('Tanggal');
      expect(headers).to.include('Total');
      expect(headers).to.include('Status');
    });
  });

  // -----------------------------
  // 2. Filter Tanggal & Logika
  // -----------------------------
  it('LAP-002: Filter laporan berdasarkan range tanggal', () => {
    cy.visit('/melihatlaporan');

    const startDate = '2024-01-01';
    const endDate = '2024-12-31';

    cy.get('input[name="start"]').type(startDate);
    cy.get('input[name="end"]').type(endDate);

    cy.get('button, input[type="submit"]').filter(':contains("Filter")').click();

    cy.url().should('include', '/melihatlaporan');
    cy.get('table').should('exist');
  });

  it('LAP-003: Validasi filter jika tanggal akhir < tanggal awal', () => {
    cy.visit('/melihatlaporan');

    cy.get('input[name="start"]').type('2024-12-31');
    cy.get('input[name="end"]').type('2024-01-01');
    cy.get('button[type="submit"]').contains(/Filter/i).click();

    // Sistem harus menangani ini, biasanya menampilkan pesan kosong
    cy.contains(/Belum ada data laporan/i).should('exist');
  });

  // -----------------------------
  // 3. Filter Status & Kombinasi
  // -----------------------------
  it('LAP-004: Filter berdasarkan status dan validasi warna badge', () => {
    cy.visit('/melihatlaporan');

    const targetStatus = 'Selesai';
    cy.get('select[name="status"]').select(targetStatus);
    cy.get('button[type="submit"]').contains(/Filter/i).click();

    // Cek apakah data yang muncul sesuai status (LPR-003)
    // dan cek class CSS badge (LPR-010)
    cy.get('body').then(($body) => {
      if ($body.find('.status-pill, .badge').length > 0) {
        cy.get('.status-pill, .badge').each(($el) => {
          const txt = $el.text().trim();
          expect(txt).to.eq(targetStatus);
          // Validasi class CSS (Pill Selesai biasanya hijau)
          expect($el).to.have.class('pill-selesai');
        });
      } else {
        cy.log('Tidak ada data untuk status ini');
      }
    });
  });

  it('LAP-005: Kombinasi filter tanggal, status, dan pencarian kosong', () => {
    cy.visit('/melihatlaporan');

    // Tes filter kosong (LPR-004)
    cy.get('button[type="submit"]').contains(/Filter/i).click();
    cy.get('table').should('exist');

    // Tes kombinasi (LPR-005)
    cy.get('input[name="start"]').type('2024-01-01');
    cy.get('select[name="status"]').select('Dikirim');
    cy.get('button[type="submit"]').click();

    cy.get('table').should('exist');
  });

  // -----------------------------
  // 4. Export Data
  // -----------------------------
  it('LAP-006: Tombol Export Excel memiliki link yang benar dan dapat diklik', () => {
    cy.visit('/melihatlaporan');

    cy.contains('a, button', /Export Excel|Unduh Excel|Download Excel/i)
      .first()
      .should('exist')
      .and('have.attr', 'href')
      .and('include', 'export');

    // Simulasi klik export
    cy.contains(/Export Excel/i).click({ force: true });
    cy.wait(1000); // Tunggu trigger download
    cy.url().should('include', 'melihatlaporan');
  });

  // -----------------------------
  // 5. Penanganan Data Kosong
  // -----------------------------
  it('LAP-007: Filter menghasilkan data kosong (Tanggal jauh di masa lalu)', () => {
    cy.visit('/melihatlaporan');

    cy.get('input[name="start"]').type('1990-01-01');
    cy.get('input[name="end"]').type('1990-01-02');
    cy.get('button[type="submit"]').click();

    cy.contains(/Belum ada data laporan|Data tidak ditemukan/i).should('exist');
  });
});