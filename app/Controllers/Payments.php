<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProdukModel;
use Config\Database;

class Payments extends BaseController
{
    /* ------------ Midtrans bootstrap ------------ */
    private function midtransInit(): void
    {
        \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION') === 'true';
        \Midtrans\Config::$serverKey    = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isSanitized  = true;
        \Midtrans\Config::$is3ds        = true;
    }

    /* ------------ 1) Create transaksi baru ------------ */
    public function create()
    {
        $idUser = session()->get('id_user');
        if (!$idUser) {
            return $this->response->setStatusCode(401)->setJSON(['success'=>false,'message'=>'Unauthorized']);
        }

        // Payload: { id_alamat, items:[{id_produk, qty}, ...] }
        $payload  = $this->request->getJSON(true);
        $idAlamat = (int)($payload['id_alamat'] ?? 0);
        $itemsIn  = $payload['items'] ?? [];

        if ($idAlamat <= 0 || empty($itemsIn) || !is_array($itemsIn)) {
            return $this->response->setJSON(['success'=>false,'message'=>'Payload invalid']);
        }

        $produkModel = new ProdukModel();
        $detailRows  = [];
        $grossAmount = 0;

        foreach ($itemsIn as $it) {
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

        $db  = Database::connect();
        $now = date('Y-m-d H:i:s');

        $db->transStart();

        // (A) pembayaran (pending)
        $db->table('pembayaran')->insert([
            'gateway'            => 'midtrans',
            'order_id'           => null,
            'payment_type'       => null,
            'gross_amount'       => $grossAmount,
            'transaction_status' => 'pending',
            'snap_token'         => null,
            'redirect_url'       => null,
            'created_at'         => $now,
            'updated_at'         => $now,
        ]);
        $idPembayaran = $db->insertID();

        // (B) pemesanan (Belum Bayar)
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

        // (C1) detail_pemesanan
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

        // (C2) KURANGI STOK (guarded, sama seperti COD)
        foreach ($detailRows as $d) {
            $db->query(
                "UPDATE produk SET stok = stok - ? WHERE id_produk = ? AND stok >= ?",
                [$d['jumlah_produk'], $d['id_produk'], $d['jumlah_produk']]
            );
            if ($db->affectedRows() === 0) {
                $db->transRollback();
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Stok berubah/habis. Silakan ulangi checkout.'
                ]);
            }
        }

        // (D) order_id unik
        $orderId = 'ORD-' . $idPemesanan . '-' . time();
        $db->table('pembayaran')->where('id_pembayaran', $idPembayaran)->update([
            'order_id'   => $orderId,
            'updated_at' => $now
        ]);

        $db->transComplete();
        if (!$db->transStatus()) {
            return $this->response->setJSON(['success'=>false,'message'=>'Gagal membuat transaksi.']);
        }

        // [BERSIHKAN KERANJANG] – sama seperti jalur COD
        $session  = session();
        $cartKey  = 'cart_u_' . $idUser;
        $countKey = 'cart_count_u_' . $idUser;
        $session->remove([$cartKey, $countKey, 'checkout_all', 'checkout_data_multi', 'checkout_data']);
        // Jika keranjang disimpan di DB, hapus juga di sini.

        // (E) Panggil Snap
        $this->midtransInit();
        $snapItems = array_map(static function($d){
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
            'item_details'     => $snapItems,
            'customer_details' => ['first_name' => 'User-' . $idUser],
            'callbacks'        => [
                'finish'   => base_url('payments/finish'),
                'unfinish' => base_url('payments/unfinish'),
                'error'    => base_url('payments/error'),
            ],
        ];

        $snap        = \Midtrans\Snap::createTransaction($params);
        $snapToken   = $snap->token ?? null;
        $redirectUrl = $snap->redirect_url ?? null;

        // simpan token utk resume
        $db->table('pembayaran')->where('id_pembayaran', $idPembayaran)->update([
            'snap_token'   => $snapToken,
            'redirect_url' => $redirectUrl,
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'success'      => true,
            'snapToken'    => $snapToken,
            'redirect_url' => $redirectUrl,
            'order_id'     => $orderId,
            'id_pemesanan' => (int)$idPemesanan,
            // agar halaman Belum Bayar auto-open Snap
            'redirect'     => base_url('pesananbelumbayar?order='.rawurlencode($orderId).'&autopay=1'),
        ]);
    }

    /* ------------ 2a) Resume by ORDER ID (untuk tombol “Lanjutkan Pembayaran”) ------------ */
    public function resume(string $orderId)
    {
        $db = Database::connect();

        $row = $db->table('pembayaran b')
            ->select('b.id_pembayaran, b.snap_token, b.order_id, p.id_pemesanan')
            ->join('pemesanan p', 'p.id_pembayaran = b.id_pembayaran', 'left')
            ->where('b.order_id', $orderId)
            ->get()->getRowArray();

        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON([
                'success'=>false,'message'=>'Transaksi tidak ditemukan'
            ]);
        }

        if (!empty($row['snap_token'])) {
            return $this->response->setJSON(['success'=>true,'snapToken'=>$row['snap_token']]);
        }

        // token kosong/expired → generate token retry
        $retry = $this->regenerateToken((int)$row['id_pemesanan'], $orderId);
        if (!$retry['success']) {
            return $this->response->setStatusCode(500)->setJSON($retry);
        }
        return $this->response->setJSON([
            'success'=>true,'snapToken'=>$retry['snapToken'],'order_id'=>$retry['order_id']
        ]);
    }

    /* ------------ 2b) Kompatibilitas lama (by id_pemesanan) ------------ */
    public function tokenByOrder(int $idPemesanan)
    {
        $db = Database::connect();

        $row = $db->table('pemesanan p')
            ->select('b.snap_token, b.order_id, b.id_pembayaran')
            ->join('pembayaran b', 'b.id_pembayaran = p.id_pembayaran', 'left')
            ->where('p.id_pemesanan', $idPemesanan)
            ->get()->getRowArray();

        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON(['success'=>false,'message'=>'Pesanan tidak ditemukan']);
        }

        if (!empty($row['snap_token'])) {
            return $this->response->setJSON(['success'=>true,'snapToken'=>$row['snap_token']]);
        }

        $new = $this->regenerateToken($idPemesanan, $row['order_id'] ?: null);
        if (!$new['success']) {
            return $this->response->setStatusCode(500)->setJSON($new);
        }
        return $this->response->setJSON(['success'=>true,'snapToken'=>$new['snapToken']]);
    }

    /** Buat token ulang */
    private function regenerateToken(int $idPemesanan, ?string $baseOrderId = null): array
    {
        $db = Database::connect();

        $pay = $db->table('pemesanan p')
            ->select('b.id_pembayaran, b.order_id')
            ->join('pembayaran b', 'b.id_pembayaran = p.id_pembayaran', 'left')
            ->where('p.id_pemesanan', $idPemesanan)
            ->get()->getRowArray();
        if (!$pay) return ['success'=>false,'message'=>'Pembayaran tidak ditemukan.'];

        $detail = $db->table('detail_pemesanan')->where('id_pemesanan', $idPemesanan)->get()->getResultArray();
        if (!$detail) return ['success'=>false,'message'=>'Detail item kosong.'];

        $produkIds = array_column($detail, 'id_produk');
        $produkMap = [];
        if ($produkIds) {
            $rows = $db->table('produk')->whereIn('id_produk', $produkIds)->get()->getResultArray();
            foreach ($rows as $r) $produkMap[$r['id_produk']] = $r['nama_produk'] ?? ('Produk-'.$r['id_produk']);
        }

        $this->midtransInit();

        $gross = 0;
        $snapItems = [];
        foreach ($detail as $d) {
            $harga  = (int)$d['harga_produk'];
            $qty    = (int)$d['jumlah_produk'];
            $gross += $harga * $qty;
            $snapItems[] = [
                'id'       => (string)$d['id_produk'],
                'price'    => $harga,
                'quantity' => $qty,
                'name'     => $produkMap[$d['id_produk']] ?? ('Produk-'.$d['id_produk']),
            ];
        }

        $root       = $baseOrderId ?: ($pay['order_id'] ?: 'ORD-'.$idPemesanan);
        $newOrderId = $root . '-R' . time();

        $params = [
            'transaction_details' => ['order_id'=>$newOrderId, 'gross_amount'=>$gross],
            'item_details'        => $snapItems
        ];

        $snap        = \Midtrans\Snap::createTransaction($params);
        $snapToken   = $snap->token ?? null;
        $redirectUrl = $snap->redirect_url ?? null;

        $db->table('pembayaran')->where('id_pembayaran', $pay['id_pembayaran'])->update([
            'order_id'           => $newOrderId,
            'transaction_status' => 'pending',
            'snap_token'         => $snapToken,
            'redirect_url'       => $redirectUrl,
            'updated_at'         => date('Y-m-d H:i:s'),
        ]);

        return ['success'=>true,'snapToken'=>$snapToken,'order_id'=>$newOrderId];
    }

    /* ------------ 3) Webhook Midtrans ------------ */
    public function webhook()
    {
        helper('text');
        $raw   = $this->request->getBody();
        $notif = json_decode($raw, true);

        // Signature validation (longgar utk sandbox)
        $serverKey   = env('MIDTRANS_SERVER_KEY');
        $orderId     = $notif['order_id']     ?? '';
        $statusCode  = $notif['status_code']  ?? '';
        $grossAmount = $notif['gross_amount'] ?? '';
        $sig         = $notif['signature_key']?? '';

        $calc1 = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);
        $calc2 = hash('sha512', $orderId.'200'.($grossAmount ?: '0').$serverKey);
        if (!hash_equals($calc1, $sig) && !hash_equals($calc2, $sig)) {
            log_message('warning', '[MIDTRANS] Signature mismatch', compact('orderId','statusCode','grossAmount'));
        }

        $statusMid  = $notif['transaction_status'] ?? 'pending';
        $paymentType= $notif['payment_type']       ?? null;
        $fraud      = $notif['fraud_status']       ?? null;
        $va         = $notif['va_numbers'][0]['va_number'] ?? ($notif['permata_va_number'] ?? null);
        $pdfUrl     = $notif['pdf_url'] ?? null;

        $db  = Database::connect();
        $now = date('Y-m-d H:i:s');

        $db->transStart();

        $pay = $db->table('pembayaran')->where('order_id', $orderId)->get()->getRowArray();
        if (!$pay) {
            $db->transComplete();
            return $this->response->setStatusCode(404)->setBody('order not found');
        }

        $db->table('pembayaran')->where('order_id', $orderId)->update([
            'transaction_status' => $statusMid,
            'payment_type'       => $paymentType,
            'fraud_status'       => $fraud,
            'va_number'          => $va,
            'pdf_url'            => $pdfUrl,
            'payload'            => json_encode($notif),
            'updated_at'         => $now,
        ]);

        // mapping status → status_pemesanan
        $newStatus = null;
        if (in_array($statusMid, ['capture','settlement'], true)) {
            $newStatus = 'Dikemas';
        } elseif ($statusMid === 'pending') {
            $newStatus = 'Belum Bayar';
        } elseif (in_array($statusMid, ['cancel','deny','expire'], true)) {
            $newStatus = 'Dibatalkan';
        }

        if ($newStatus) {
            $db->query("
                UPDATE pemesanan p
                   JOIN pembayaran b ON b.id_pembayaran = p.id_pembayaran
                   SET p.status_pemesanan = ?, p.updated_at = ?
                 WHERE b.order_id = ?
            ", [$newStatus, $now, $orderId]);
        }

        $db->transComplete();
        if (!$db->transStatus()) {
            return $this->response->setStatusCode(500)->setBody('DB error');
        }
        return $this->response->setStatusCode(200)->setBody('OK');
    }

    /* ------------ Callback landing pages ------------ */

    /** sukses → arahkan ke Dikemas */
    public function finish()
    {
        return redirect()->to('/pesanandikemas')
            ->with('success', 'Pembayaran berhasil. Jika daftar belum berubah, tunggu 1–2 detik sampai notifikasi masuk.');
    }

    /**
     * belum selesai / ditutup / expired
     * - kembalikan stok & hapus pesanan pending
     * - arahkan ke dashboarduser
     */
    public function unfinish()
    {
        $orderId = (string) $this->request->getGet('order_id'); // Midtrans mengirim ?order_id=
        if ($orderId !== '') {
            $this->cancelAndRestock($orderId);
        }
        return redirect()->to('/pesananbelumbayar')
            ->with('info', 'Transaksi tidak selesai/expired. Pesanan dibatalkan dan stok dikembalikan.');
    }

    /** error → kembali ke Belum Bayar */
    public function error()
    {
        return redirect()->to('/pesananbelumbayar')
            ->with('error', 'Terjadi kesalahan saat memproses pembayaran. Coba lagi ya.');
    }

    /**
     * Hapus pesanan “Belum Bayar” + kembalikan stok (idempoten aman dipanggil berulang)
     */
    private function cancelAndRestock(string $orderId): void
    {
        $db = Database::connect();
        $db->transStart();

        $row = $db->table('pemesanan p')
            ->select('p.id_pemesanan, p.status_pemesanan, b.id_pembayaran')
            ->join('pembayaran b','b.id_pembayaran = p.id_pembayaran','left')
            ->where('b.order_id',$orderId)->get()->getRowArray();

        if ($row && $row['status_pemesanan'] === 'Belum Bayar') {
            $detail = $db->table('detail_pemesanan')->where('id_pemesanan',$row['id_pemesanan'])->get()->getResultArray();
            foreach ($detail as $d) {
                $db->query("UPDATE produk SET stok = stok + ? WHERE id_produk = ?", [(int)$d['jumlah_produk'], (int)$d['id_produk']]);
            }
            // hapus detail & header + baris pembayaran
            $db->table('detail_pemesanan')->where('id_pemesanan',$row['id_pemesanan'])->delete();
            $db->table('pemesanan')->where('id_pemesanan',$row['id_pemesanan'])->delete();
            if (!empty($row['id_pembayaran'])) {
                $db->table('pembayaran')->where('id_pembayaran',$row['id_pembayaran'])->delete();
            }
        }

        $db->transComplete();
    }
}
