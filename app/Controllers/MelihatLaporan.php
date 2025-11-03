<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use Config\Database;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MelihatLaporan extends BaseController
{
    public function index()
    {
        $db = Database::connect();

        $start  = $this->request->getGet('start');
        $end    = $this->request->getGet('end');
        $status = $this->request->getGet('status'); // dari dropdown

        // Padanan status (konsisten untuk index & export)
        $statusAliases = [
            'Belum Bayar' => ['Belum Bayar', 'Menunggu Pembayaran', 'Pending', 'Pending Payment'],
            'Dikemas'     => ['Dikemas', 'Dipacking'],
            'Dikirim'     => ['Dikirim', 'Dalam Perjalanan'],
            'Selesai'     => ['Selesai', 'Completed'],
            'Dibatalkan'  => ['Dibatalkan', 'Batal', 'Canceled'],
        ];

        $builder = $db->table('pemesanan p')
            ->select('
                p.id_pemesanan,
                u.nama AS nama_pembeli,
                pr.nama_produk,
                dp.jumlah_produk,
                dp.harga_produk,
                p.status_pemesanan,
                p.created_at
            ')
            ->join('users u', 'p.id_user = u.id_user', 'left')
            ->join('detail_pemesanan dp', 'p.id_pemesanan = dp.id_pemesanan', 'left')
            ->join('produk pr', 'dp.id_produk = pr.id_produk', 'left')
            ->orderBy('p.created_at', 'DESC');

        if ($start && $end) {
            $builder->where("DATE(p.created_at) >=", $start)
                    ->where("DATE(p.created_at) <=", $end);
        }

        if ($status !== null && $status !== '') {
            $values = $statusAliases[$status] ?? [$status];
            $builder->whereIn('p.status_pemesanan', $values);
        }

        $laporan = $builder->get()->getResultArray();

        $userId = session()->get('id_user');
        $user   = (new UserModel())->find($userId);

        return view('Admin/melihatlaporan', [
            'laporan' => $laporan,
            'user'    => $user,
            'start'   => $start,
            'end'     => $end,
            'status'  => $status,
        ]);
    }

    public function exportExcel()
    {
        $db     = Database::connect();
        $start  = $this->request->getGet('start');
        $end    = $this->request->getGet('end');
        $status = $this->request->getGet('status'); // ikut filter

        // Padanan status (konsisten dengan index)
        $statusAliases = [
            'Belum Bayar' => ['Belum Bayar', 'Menunggu Pembayaran', 'Pending', 'Pending Payment'],
            'Dikemas'     => ['Dikemas', 'Dipacking'],
            'Dikirim'     => ['Dikirim', 'Dalam Perjalanan'],
            'Selesai'     => ['Selesai', 'Completed'],
            'Dibatalkan'  => ['Dibatalkan', 'Batal', 'Canceled'],
        ];

        $builder = $db->table('pemesanan p')
            ->select('
                p.id_pemesanan,
                u.nama AS nama_pembeli,
                pr.nama_produk,
                dp.jumlah_produk,
                dp.harga_produk,
                p.status_pemesanan,
                p.created_at
            ')
            ->join('users u', 'p.id_user = u.id_user', 'left')
            ->join('detail_pemesanan dp', 'p.id_pemesanan = dp.id_pemesanan', 'left')
            ->join('produk pr', 'dp.id_produk = pr.id_produk', 'left')
            ->orderBy('p.created_at', 'DESC');

        // Filter tanggal (opsional)
        if ($start && $end) {
            $builder->where("DATE(p.created_at) >=", $start)
                    ->where("DATE(p.created_at) <=", $end);
        }

        // Filter status (opsional)
        if ($status !== null && $status !== '') {
            $values = $statusAliases[$status] ?? [$status];
            $builder->whereIn('p.status_pemesanan', $values);
        }

        $laporan = $builder->get()->getResultArray();

        // Ringkasan
        $totalPemasukan = 0;
        $totalItem      = 0;
        $orderIds       = [];
        foreach ($laporan as $r) {
            $jumlah = (int)($r['jumlah_produk'] ?? 0);
            $harga  = (float)($r['harga_produk'] ?? 0);
            $total  = $jumlah * $harga;

            $totalItem      += $jumlah;
            $totalPemasukan += $total;
            $orderIds[$r['id_pemesanan']] = true;
        }
        $totalTransaksi = count($orderIds);
        $periode = ($start && $end)
            ? (date('d-m-Y', strtotime($start)).' s/d '.date('d-m-Y', strtotime($end)))
            : 'Semua Tanggal';
        $statusTitle = ($status !== null && $status !== '') ? strtoupper($status) : 'SEMUA STATUS';

        // Spreadsheet (format TETAP)
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header summary (tidak mengubah format)
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'LAPORAN PENJUALAN (Status: ' . $statusTitle . ')');
        $sheet->getRowDimension('1')->setRowHeight(24);

        $sheet->setCellValue('A2', 'Periode');          $sheet->setCellValue('B2', $periode);
        $sheet->setCellValue('D2', 'Dibuat');           $sheet->setCellValue('E2', date('d-m-Y H:i'));

        $sheet->setCellValue('A3', 'Status');           $sheet->setCellValue('B3', ucwords(strtolower($statusTitle)));
        $sheet->setCellValue('D3', 'Total Transaksi');  $sheet->setCellValue('E3', $totalTransaksi);

        $sheet->setCellValue('A4', 'Total Item');       $sheet->setCellValue('B4', $totalItem);
        $sheet->setCellValue('D4', 'Total Pemasukan');  $sheet->setCellValue('E4', $totalPemasukan);

        // Styling summary (tetap)
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '198754']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('A2:E4')->applyFromArray([
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E9F7EF']],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
            'font' => ['bold' => false],
        ]);
        $sheet->getStyle('A2:A4')->getFont()->setBold(true);
        $sheet->getStyle('D2:D4')->getFont()->setBold(true);
        $sheet->getStyle('E4')->getNumberFormat()->setFormatCode('"Rp"#,##0');

        // Tabel data (tetap)
        $startRow = 6;
        $headers = ['No', 'Nama Pembeli', 'Produk', 'Tanggal', 'Jumlah', 'Harga Satuan', 'Total', 'Status'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col.$startRow, $header);
            $col++;
        }
        $sheet->getStyle('A'.$startRow.':H'.$startRow)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '198754']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ]);

        // Isi data (tetap)
        $row = $startRow + 1;
        $no  = 1;
        foreach ($laporan as $data) {
            $jumlah = (int)($data['jumlah_produk'] ?? 0);
            $harga  = (float)($data['harga_produk'] ?? 0);
            $total  = $jumlah * $harga;

            $sheet->setCellValue('A'.$row, $no++);
            $sheet->setCellValue('B'.$row, $data['nama_pembeli'] ?? '-');
            $sheet->setCellValue('C'.$row, $data['nama_produk'] ?? '-');
            $sheet->setCellValue('D'.$row, date('d-m-Y', strtotime($data['created_at'] ?? date('Y-m-d'))));
            $sheet->setCellValue('E'.$row, $jumlah);
            $sheet->setCellValue('F'.$row, $harga);
            $sheet->setCellValue('G'.$row, $total);
            $sheet->setCellValue('H'.$row, ucfirst($data['status_pemesanan'] ?? '-'));
            $row++;
        }
        $lastRow = $row - 1;

        // Footer totals (tetap)
        $footerRow = $lastRow + 1;
        $sheet->setCellValue('A'.$footerRow, 'TOTAL');
        $sheet->mergeCells('A'.$footerRow.':E'.$footerRow);
        $sheet->setCellValue('F'.$footerRow, 'Total Item');
        $sheet->setCellValue('G'.$footerRow, $totalItem);
        $sheet->setCellValue('H'.$footerRow, 'Total Pemasukan');

        $sheet->getStyle('A'.$footerRow.':H'.$footerRow)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8FFF0']],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ]);
        $sheet->getStyle('F'.($startRow+1).':F'.$lastRow)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('F'.$footerRow)->getNumberFormat()->setFormatCode('@');
        $sheet->getStyle('G'.$footerRow)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('G'.($startRow+1).':G'.$lastRow)->getNumberFormat()->setFormatCode('"Rp"#,##0');
        $sheet->getStyle('A'.$startRow.':H'.$lastRow)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ]);

        foreach (range('A','H') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_penjualan_'.date('Ymd_His').'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}
