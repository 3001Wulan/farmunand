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

        $start = $this->request->getGet('start');
        $end   = $this->request->getGet('end');

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

        $laporan = $builder->get()->getResultArray();

        // Data user admin untuk sidebar
        $userId = session()->get('id_user');
        $user   = (new UserModel())->find($userId);

        return view('Admin/melihatlaporan', [
            'laporan' => $laporan,
            'user'    => $user,
            'start'   => $start,
            'end'     => $end,
        ]);
    }

    public function exportExcel()
    {
        $db = Database::connect();
        $start = $this->request->getGet('start');
        $end   = $this->request->getGet('end');

        $builder = $db->table('pemesanan p')
            ->select('
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

        $laporan = $builder->get()->getResultArray();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $headers = ['No', 'Nama Pembeli', 'Produk', 'Tanggal', 'Total', 'Status'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col.'1', $header);
            $col++;
        }

        // Isi data
        $row = 2;
        $no  = 1;
        foreach ($laporan as $data) {
            $total = ($data['harga_produk'] ?? 0) * ($data['jumlah_produk'] ?? 1);

            $sheet->setCellValue('A'.$row, $no++);
            $sheet->setCellValue('B'.$row, $data['nama_pembeli'] ?? '-');
            $sheet->setCellValue('C'.$row, $data['nama_produk'] ?? '-');
            $sheet->setCellValue('D'.$row, date('d-m-Y', strtotime($data['created_at'] ?? date('Y-m-d'))));
            $sheet->setCellValue('E'.$row, $total);
            $sheet->setCellValue('F'.$row, ucfirst($data['status_pemesanan'] ?? '-'));

            $row++;
        }

        // Style header
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '198754']
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ]);

        // Style kolom Total (E) → format mata uang
        $lastRow = $row - 1;
        $sheet->getStyle('E2:E'.$lastRow)->getNumberFormat()
            ->setFormatCode('"Rp"#,##0');

        // Border seluruh tabel
        $sheet->getStyle('A1:F'.$lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Auto width kolom
        foreach (range('A','F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Export file
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_penjualan_'.date('Ymd_His').'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

}
