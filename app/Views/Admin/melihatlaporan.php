<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Laporan Penjualan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body{
      background:#f8f9fa;
      font-family:"Segoe UI",Tahoma,Geneva,Verdana,sans-serif;
    }
    .main-content{
      margin-left:250px; /* sesuaikan lebar sidebar */
      padding:30px;
    }
    /* Header hijau gradient */
    .page-header{
      background:linear-gradient(135deg,#198754,#28a745);
      color:#fff;
      border-radius:12px 12px 0 0;
      padding:20px 30px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:12px;
    }
    .page-header h3{
      margin:0;
      font-weight:700;
      font-size:22px;
      display:flex;
      align-items:center;
      gap:10px;
    }
    /* Kartu konten */
    .card-container{
      background:#fff;
      border-radius:0 0 12px 12px;
      box-shadow:0 4px 12px rgba(0,0,0,.08);
      padding:20px;
    }

    /* Filter */
    .filter-card .form-label{font-weight:500;}

    /* Tabel */
    .table thead th{
      background:#198754;
      color:#fff;
      border:none;
      text-align:center;
    }
    .table td,.table th{
      vertical-align:middle;
      text-align:center;
      padding:12px;
    }
    .table tbody tr:hover{background:#f1fdf6;}
    .table .col-no{width:70px}
    .table .col-aksi{width:160px}

    /* Badge status */
    .status-badge{
      padding:6px 12px;
      border-radius:12px;
      font-size:13px;
      font-weight:600;
    }
    .status-success{background:#d1f7e3;color:#198754;}
    .status-pending{background:#fff3cd;color:#856404;}
    .status-cancel{background:#fde2e4;color:#a71d2a;}

    /* Fokus keyboard (aksesibilitas) */
    .btn:focus-visible, a:focus-visible, input:focus-visible, select:focus-visible{
      outline:3px solid #0d6efd;
      outline-offset:2px;
    }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <?= $this->include('layout/sidebarAdmin') ?>

  <div class="main-content">
    <!-- Header -->
    <div class="page-header">
      <h3><i class="bi bi-clipboard-data"></i>🧾 Laporan Penjualan</h3>
      <div>
        <a href="<?= base_url('melihatlaporan/exportExcel?start='.($start ?? '').'&end='.($end ?? '')) ?>"
          class="btn btn-light btn-sm fw-semibold">
          <i class="bi bi-file-earmark-excel"></i> Export Excel
        </a>
      </div>
      <!-- ruang aksi header (export/print) bila diperlukan -->
    </div>

    <!-- Container -->
    <div class="card-container">

      <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success" role="alert">
          <?= esc(session()->getFlashdata('success')) ?>
        </div>
      <?php endif; ?>
      <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger" role="alert">
          <?= esc(session()->getFlashdata('error')) ?>
        </div>
      <?php endif; ?>

      <!-- Filter -->
      <div class="card mb-3 filter-card">
        <div class="card-body">
          <form class="row g-3" onsubmit="return false;">
            <div class="col-md-4">
              <label for="startDate" class="form-label">Dari Tanggal</label>
              <input type="date" id="startDate" class="form-control" aria-label="Dari tanggal">
            </div>
            <div class="col-md-4">
              <label for="endDate" class="form-label">Sampai Tanggal</label>
              <input type="date" id="endDate" class="form-control" aria-label="Sampai tanggal">
            </div>
            <div class="col-md-4 d-flex align-items-end">
              <button type="button" id="filterBtn" class="btn btn-success w-100" aria-label="Terapkan filter tanggal">
                Filter
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Tabel Laporan -->
      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered align-middle">
              <thead>
                <tr>
                  <th class="col-no">No</th>
                  <th>Nama Pembeli</th>
                  <th>Produk</th>
                  <th>Tanggal</th>
                  <th>Total</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody id="laporanTable">
                <?php if (!empty($laporan)): ?>
                  <?php $no = 1; foreach ($laporan as $row): ?>
                    <tr>
                      <td><?= $no++; ?></td>
                      <td><?= esc($row['nama_pembeli'] ?? 'Tidak ada'); ?></td>
                      <td><?= esc($row['nama_produk'] ?? '-'); ?></td>
                      <td>
                        <?php
                          $tgl = $row['created_at'] ?? date('Y-m-d');
                          echo date('d-m-Y', strtotime($tgl));
                        ?>
                      </td>
                      <td>
                        Rp <?= number_format(($row['harga_produk'] ?? 0) * (int)($row['jumlah_produk'] ?? 1), 0, ',', '.'); ?>
                      </td>
                      <td>
                        <?php
                          $status = strtolower(trim((string)($row['status_pemesanan'] ?? '')));

                          switch ($status) {
                            // Sukses / selesai
                            case 'sukses':
                            case 'selesai':
                            case 'dibayar': // kalau dianggap sudah lunas
                              $cls = 'status-success';
                              $label = ucfirst($status);
                              break;

                            // Masih berjalan
                            case 'pending':
                            case 'diproses':
                            case 'dikirim':
                            case 'belum bayar':
                              $cls = 'status-pending';
                              // rapikan label: khusus 'belum bayar' biar kapitalisasi enak
                              $label = ($status === 'belum bayar') ? 'Belum Bayar' : ucfirst($status);
                              break;

                            // Batal / lainnya
                            default:
                              $cls = 'status-cancel';
                              $label = $status ? ucfirst($status) : 'Batal';
                              break;
                          }
                          ?>
                        <span class="status-badge <?= $cls ?>"><?= esc($label) ?></span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6" class="text-center text-muted">Belum ada data laporan</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div><!-- /.card-container -->
  </div><!-- /.main-content -->

  <script>
    // Filter baris by tanggal (client-side, format sel tabel: dd-mm-yyyy)
    document.getElementById("filterBtn").addEventListener("click", function () {
      const startDate = document.getElementById("startDate").value; // yyyy-mm-dd
      const endDate   = document.getElementById("endDate").value;   // yyyy-mm-dd
      const rows = document.querySelectorAll("#laporanTable tr");

      rows.forEach(row => {
        const tanggalCell = row.cells[3];
        if (!tanggalCell) return;

        const tanggalText = tanggalCell.textContent.trim(); // dd-mm-yyyy
        const parts = tanggalText.split("-");
        const formatted = `${parts[2]}-${parts[1]}-${parts[0]}`; // yyyy-mm-dd
        const rowDate = new Date(formatted);

        let show = true;
        if (startDate && rowDate < new Date(startDate)) show = false;
        if (endDate && rowDate > new Date(endDate)) show = false;

        row.style.display = show ? "" : "none";
      });
    });
  </script>
</body>
</html>
