<?php
namespace App\Controllers;

use App\Models\ProdukModel;
use CodeIgniter\Controller;

class Payments extends BaseController
{
    private function midtransInit(): void
    {
        // Pakai Composer
        \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION') === 'true';
        \Midtrans\Config::$serverKey    = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isSanitized  = true;
        \Midtrans\Config::$is3ds        = true;
    }

    public function create()
    {
        $idUser = session()->get('id_user');
        if (!$idUser) {
            return $this->response->setStatusCode(401)->setJSON(['success'=>false,'message'=>'Unauthorized']);
        }

        // Payload dari front-end
        // Contoh single item: { "id_alamat": 12, "items":[{"id_produk":55,"qty":2}] }
        $payload  = $this->request->getJSON(true);
        $idAlamat = (int)($payload['id_alamat'] ?? 0);
        $items    = $payload['items'] ?? [];

        if ($idAlamat <= 0 || empty($items) || !is_array($items)) {
            return $this->response->setJSON(['success'=>false,'message'=>'Payload invalid']);
        }

        // Hitung total
        $produkModel = new ProdukModel();
        $detailRows  = [];
        $grossAmount = 0;

        foreach ($items as $it) {
            $pid = (int)($it['id_produk'] ?? 0);
            $qty = max(1, (int)($it['qty'] ?? 0));
            $p   = $produkModel->find($pid);
            if (!$p || $qty <= 0) continue;

            $harga = (int)$p['harga'];
            $grossAmount += $harga * $qty;

            $detailRows[] = [
                'id_produk'     => $pid,
                'jumlah_produk' => $qty,
                'harga_produk'  => $harga,
                'nama_produk'   => $p['nama_produk'] ?? ('Produk-'.$pid),
            ];
        }

        if (!$detailRows) {
            return $this->response->setJSON(['success'=>false,'message'=>'Tidak ada item valid.']);
        }

        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $db->transStart();

        // 1) insert pembayaran (sementara)
        $db->table('pembayaran')->insert([
            'gateway'            => 'midtrans',
            'order_id'           => null,
            'payment_type'       => null,
            'gross_amount'       => $grossAmount,
            'transaction_status' => 'pending',
            'created_at'         => $now,
            'updated_at'         => $now,
        ]);
        $idPembayaran = $db->insertID();

        // 2) insert pemesanan (status: Belum Bayar)
        $db->table('pemesanan')->insert([
            'id_user'          => $idUser,
            'id_alamat'        => $idAlamat,
            'id_pembayaran'    => $idPembayaran,
            'status_pemesanan' => 'Belum Bayar',
            'total_harga'      => $grossAmount,
            'created_at'       => $now,
            'updated_at'       => $now,
        ]);
        $idPemesanan = $db->insertID();

        // 3) insert detail_pemesanan
        foreach ($detailRows as $d) {
            $db->table('detail_pemesanan')->insert([
                'id_pemesanan'  => $idPemesanan,
                'id_produk'     => $d['id_produk'],
                'jumlah_produk' => $d['jumlah_produk'],
                'harga_produk'  => $d['harga_produk'],
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }

        // 4) buat order_id unik & update pembayaran
        $orderId = 'ORD-'.$idPemesanan.'-'.time();
        $db->table('pembayaran')->where('id_pembayaran', $idPembayaran)->update([
            'order_id'   => $orderId,
            'updated_at' => $now
        ]);

        $db->transComplete();

        if (!$db->transStatus()) {
            return $this->response->setJSON(['success'=>false,'message'=>'Gagal membuat transaksi.']);
        }

        // 5) panggil Midtrans Snap
        // Composer:
        $this->midtransInit();

        $snapItems = array_map(function($d){
            return [
              'id'       => (string)$d['id_produk'],
              'price'    => (int)$d['harga_produk'],
              'quantity' => (int)$d['jumlah_produk'],
              'name'     => $d['nama_produk'],
            ];
        }, $detailRows);

        $params = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $grossAmount
            ],
            'item_details'       => $snapItems,
            'customer_details'   => [
                'first_name' => 'User-'.$idUser,
            ],
            'callbacks' => [
                'finish'   => base_url('payments/finish'),
                'unfinish' => base_url('payments/unfinish'),
                'error'    => base_url('payments/error'),
            ]
        ];

        $snapToken = \Midtrans\Snap::getSnapToken($params);

        return $this->response->setJSON([
            'success'      => true,
            'snapToken'    => $snapToken,
            'order_id'     => $orderId,
            'id_pemesanan' => (int)$idPemesanan
        ]);
    }

    public function webhook()
    {
        $serverKey   = env('MIDTRANS_SERVER_KEY');
        $raw         = $this->request->getBody();
        $notif       = json_decode($raw, true);

        $orderId     = $notif['order_id'] ?? '';
        $statusCode  = $notif['status_code'] ?? '';
        $grossAmount = $notif['gross_amount'] ?? '';
        $sig         = $notif['signature_key'] ?? '';
        $calc        = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        if (!hash_equals($calc, $sig)) {
            return $this->response->setStatusCode(401)->setBody('Invalid signature');
        }

        $statusMid = $notif['transaction_status'] ?? 'pending';
        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $db->transStart();

        // update pembayaran
        $pay = $db->table('pembayaran')->where('order_id', $orderId)->get()->getRowArray();
        if ($pay) {
            $db->table('pembayaran')->where('order_id', $orderId)->update([
                'transaction_status' => $statusMid,
                'payment_type'       => $notif['payment_type'] ?? null,
                'fraud_status'       => $notif['fraud_status'] ?? null,
                'va_number'          => $notif['va_numbers'][0]['va_number'] ?? ($notif['permata_va_number'] ?? null),
                'pdf_url'            => $notif['pdf_url'] ?? null,
                'payload'            => json_encode($notif),
                'updated_at'         => $now
            ]);

            // mapping status midtrans -> status pemesanan
            $newStatus = null;
            if (in_array($statusMid, ['capture','settlement'])) {
                $newStatus = 'Dikemas';
            } elseif (in_array($statusMid, ['cancel','deny','expire'])) {
                $newStatus = 'Dibatalkan';
            } elseif ($statusMid === 'pending') {
                $newStatus = 'Dikemas';
            }

            if ($newStatus) {
                $db->query("
                    UPDATE pemesanan p
                    JOIN pembayaran b ON b.id_pembayaran = p.id_pembayaran
                    SET p.status_pemesanan = ?, p.updated_at = ?
                    WHERE b.order_id = ?
                ", [$newStatus, $now, $orderId]);
            }
        }

        $db->transComplete();
        if (!$db->transStatus()) {
            return $this->response->setStatusCode(500)->setBody('DB error');
        }
        return $this->response->setStatusCode(200)->setBody('OK');
    }

    // Optional – landing pages
    public function finish(){ return view('payments/finish'); }
    public function unfinish(){ return view('payments/unfinish'); }
    public function error(){ return view('payments/error'); }
}
