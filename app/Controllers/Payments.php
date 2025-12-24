<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProdukModel;
use Config\Database;

class Payments extends BaseController
{
    private function midtransInit(): void
    {
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

        $orderId = 'ORD-' . $idPemesanan . '-' . time();
        $db->table('pembayaran')->where('id_pembayaran', $idPembayaran)->update([
            'order_id'   => $orderId,
            'updated_at' => $now
        ]);

        $db->transComplete();
        if (!$db->transStatus()) {
            return $this->response->setJSON(['success'=>false,'message'=>'Gagal membuat transaksi.']);
        }

        $session  = session();
        $cartKey  = 'cart_u_' . $idUser;
        $countKey = 'cart_count_u_' . $idUser;
        $session->remove([$cartKey, $countKey, 'checkout_all', 'checkout_data_multi', 'checkout_data']);

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
            'item_details'      => $snapItems,
            'customer_details'  => ['first_name' => 'User-' . $idUser],
            'callbacks'         => [
                'finish'   => base_url('payments/finish'),
                'unfinish' => base_url('payments/unfinish'),
                'error'    => base_url('payments/error'),
            ],
        ];

        $snap        = \Midtrans\Snap::createTransaction($params);
        $snapToken   = $snap->token ?? null;
        $redirectUrl = $snap->redirect_url ?? null;

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
            'redirect'     => base_url('pesananbelumbayar?order='.$orderId.'&autopay=1'),
        ]);
    }

    public function resume(string $orderId)
    {
        $db = Database::connect();
        $row = $db->table('pembayaran b')
            ->select('b.id_pembayaran, b.snap_token, b.order_id, p.id_pemesanan')
            ->join('pemesanan p', 'p.id_pembayaran = b.id_pembayaran', 'left')
            ->where('b.order_id', $orderId)
            ->get()->getRowArray();

        if (!$row) return $this->response->setStatusCode(404)->setJSON(['success'=>false,'message'=>'Transaksi tidak ditemukan']);

        if (!empty($row['snap_token'])) {
            return $this->response->setJSON(['success'=>true,'snapToken'=>$row['snap_token']]);
        }

        $retry = $this->regenerateToken((int)$row['id_pemesanan'], $orderId);
        if (!$retry['success']) return $this->response->setStatusCode(500)->setJSON($retry);

        return $this->response->setJSON(['success'=>true,'snapToken'=>$retry['snapToken'],'order_id'=>$retry['order_id']]);
    }

    public function tokenByOrder(int $idPemesanan)
    {
        $db = Database::connect();
        $row = $db->table('pemesanan p')
            ->select('b.snap_token, b.order_id, b.id_pembayaran')
            ->join('pembayaran b', 'b.id_pembayaran = p.id_pembayaran', 'left')
            ->where('p.id_pemesanan', $idPemesanan)
            ->get()->getRowArray();

        if (!$row) return $this->response->setStatusCode(404)->setJSON(['success'=>false,'message'=>'Pesanan tidak ditemukan']);

        if (!empty($row['snap_token'])) return $this->response->setJSON(['success'=>true,'snapToken'=>$row['snap_token']]);

        $new = $this->regenerateToken($idPemesanan, $row['order_id'] ?: null);
        if (!$new['success']) return $this->response->setStatusCode(500)->setJSON($new);

        return $this->response->setJSON(['success'=>true,'snapToken'=>$new['snapToken']]);
    }

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

        $gross = 0; $snapItems = [];
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
            'transaction_details' => [
                'order_id'     => $newOrderId,
                'gross_amount' => $gross
            ],
            'item_details' => $snapItems
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

    public function webhook()
    {
        $raw   = $this->request->getBody();
        $notif = json_decode($raw, true);

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

        $statusMid   = $notif['transaction_status'] ?? 'pending'; 
        $paymentType = $notif['payment_type']       ?? null;
        $fraud      =  $notif['fraud_status']       ?? null;
        $va         =  $notif['va_numbers'][0]['va_number'] ?? ($notif['permata_va_number'] ?? null);
        $pdfUrl     =  $notif['pdf_url'] ?? null;

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

        // 👉 Tambahan penting:
        // Kalau status gagal/expired → langsung restock + hapus pesanan dari "Belum Bayar"
        if (in_array($statusMid, ['cancel','deny','expire'], true)) {
            $this->cancelOrderAndRestock($orderId);
        }

        return $this->response->setStatusCode(200)->setBody('OK');
    }

    /* ------------ util: batalkan & kembalikan stok ------------ */
    private function cancelOrderAndRestock(string $orderId): void
    {
        $db = Database::connect();
        $now = date('Y-m-d H:i:s');

        $db->transStart();

        // Ambil id_pemesanan & id_pembayaran
        $row = $db->table('pembayaran b')
            ->select('b.id_pembayaran, p.id_pemesanan')
            ->join('pemesanan p', 'p.id_pembayaran = b.id_pembayaran', 'left')
            ->where('b.order_id', $orderId)
            ->get()->getRowArray();

        if ($row && $row['id_pemesanan']) {
            $details = $db->table('detail_pemesanan')
                ->where('id_pemesanan', $row['id_pemesanan'])
                ->get()->getResultArray();

            // kembalikan stok
            foreach ($details as $d) {
                $db->query("UPDATE produk SET stok = stok + ? WHERE id_produk = ?",
                    [(int)$d['jumlah_produk'], (int)$d['id_produk']]);
            }

            // hapus detail & pesanan (supaya tidak nongol lagi di Belum Bayar)
            $db->table('detail_pemesanan')->where('id_pemesanan', $row['id_pemesanan'])->delete();
            $db->table('pemesanan')->where('id_pemesanan', $row['id_pemesanan'])->delete();
        }

        // tandai pembayaran cancel (jika belum)
        if (!empty($row['id_pembayaran'])) {
            $db->table('pembayaran')->where('id_pembayaran', $row['id_pembayaran'])->update([
                'transaction_status' => 'cancel',
                'updated_at'         => $now
            ]);
        }

        $db->transComplete();
        // (sengaja tidak return pesan; bila gagal akan ada log DB)
    }

    /* ------------ 4) Landing callbacks dari Midtrans ------------ */
    // --- helper kecil biar ga duplikatif ---
    private function currentUserForView(): array
    {
        return [
            'id_user'  => session()->get('id_user'),
            'username' => session()->get('username') ?? 'Pengguna',
            'role'     => session()->get('role') ?? 'User',
            'foto'     => session()->get('foto') ?? 'default.jpeg',
        ];
    }

    /**
     * Sukses bayar (settlement/capture).
     * Midtrans akan redirect ke URL ini. Kita tampilkan view finish milik kita sendiri.
     * Catatan: status di DB tetap di-update oleh webhook; di sini hanya tampilan.
     */
    public function finish()
    {
        $user    = $this->currentUserForView();
        // kadang Midtrans mengirim ?order_id=..., kita teruskan ke view kalau mau ditampilkan
        $orderId = $this->request->getGet('order_id');

        return view('payments/finish', [
            'user'     => $user,
            'order_id' => $orderId,
        ]);
    }

    /**
     * Transaksi belum selesai / ditutup user (onClose atau unfinish).
     * Tampilkan halaman unfinish (kalau sudah punya), atau pakai view sederhana.
     */
    public function unfinish()
    {
        $user    = $this->currentUserForView();
        $orderId = $this->request->getGet('order_id');

        // jika kamu belum punya view 'payments/unfinish', buat cepatnya mirip 'error' tapi dengan pesan "belum selesai"
        return view('payments/unfinish', [
            'user'     => $user,
            'order_id' => $orderId,
            'message'  => 'Transaksi belum selesai. Kamu bisa lanjutkan dari menu "Belum Bayar".'
        ]);
    }

    /**
     * Error saat proses pembayaran (onError).
     * Tampilkan halaman error milik kita.
     */
    public function error()
    {
        $user    = $this->currentUserForView();
        $orderId = $this->request->getGet('order_id');

        return view('payments/error', [
            'user'     => $user,
            'order_id' => $orderId,
            'message'  => 'Terjadi kesalahan saat memproses pembayaran. Coba lagi ya.'
        ]);
    }

    public function cancelByUser()
    {
        $idUser = session()->get('id_user');
        if (!$idUser) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        // Ambil order_id dari JSON body atau form
        $payload  = $this->request->getJSON(true) ?: $this->request->getPost();
        $orderId  = trim((string)($payload['order_id'] ?? ''));

        if ($orderId === '') {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'order_id diperlukan']);
        }

        $db = Database::connect();

        // Ambil data pemesanan + pembayaran untuk verifikasi kepemilikan & status
        $row = $db->table('pembayaran b')
            ->select('p.id_pemesanan, p.id_user, p.status_pemesanan, b.transaction_status')
            ->join('pemesanan p', 'p.id_pembayaran = b.id_pembayaran', 'left')
            ->where('b.order_id', $orderId)
            ->get()->getRowArray();

        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Pesanan tidak ditemukan']);
        }

        // Verifikasi pemilik
        if ((int)$row['id_user'] !== (int)$idUser) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Tidak diizinkan']);
        }

        // Hanya boleh batal jika status pesanan masih "Belum Bayar"
        if (($row['status_pemesanan'] ?? '') !== 'Belum Bayar') {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Pesanan tidak dapat dibatalkan (bukan Menunggu Pembayaran).']);
        }

        // (opsional) pastikan payment masih pending
        $trx = $row['transaction_status'] ?? 'pending';
        if (!in_array($trx, ['pending', 'challenge', null, ''], true)) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => 'Transaksi tidak bisa dibatalkan pada tahap ini.']);
        }

        // Jalankan util yang sudah ada untuk restock + hapus pesanan + tandai cancel
        $this->cancelOrderAndRestock($orderId);

        return $this->response->setJSON(['success' => true, 'message' => 'Pesanan berhasil dibatalkan.']);
    }

    // ====== TAMBAHAN BARU: endpoint batal tapi simpan record ======
    public function cancelByUserKeep()
    {
        $idUser = session()->get('id_user');
        if (!$idUser) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false, 'message' => 'Unauthorized'
            ]);
        }

        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $orderId = trim((string)($payload['order_id'] ?? ''));

        if ($orderId === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false, 'message' => 'order_id diperlukan'
            ]);
        }

        $db = \Config\Database::connect();

        // Verifikasi kepemilikan & status
        $row = $db->table('pembayaran b')
            ->select('p.id_pemesanan, p.id_user, p.status_pemesanan, b.transaction_status')
            ->join('pemesanan p', 'p.id_pembayaran = b.id_pembayaran', 'left')
            ->where('b.order_id', $orderId)
            ->get()->getRowArray();

        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false, 'message' => 'Pesanan tidak ditemukan'
            ]);
        }
        if ((int)$row['id_user'] !== (int)$idUser) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false, 'message' => 'Tidak diizinkan'
            ]);
        }
        if (($row['status_pemesanan'] ?? '') !== 'Belum Bayar') {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false, 'message' => 'Pesanan bukan Menunggu Pembayaran'
            ]);
        }

        // Jalankan versi "keep record"
        $ok = $this->cancelOrderAndMarkKeep($orderId);
        if (!$ok) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false, 'message' => 'Gagal membatalkan pesanan.'
            ]);
        }

        return $this->response->setJSON([
            'success' => true, 'message' => 'Pesanan dibatalkan dan dipindah ke tab Dibatalkan.'
        ]);
    }

    // ====== TAMBAHAN BARU: util batal + restock TANPA menghapus record ======
    private function cancelOrderAndMarkKeep(string $orderId): bool
{
    $db  = \Config\Database::connect();
    $now = date('Y-m-d H:i:s');

    $db->transStart();

    // Ambil pemesanan & pembayaran
    $row = $db->table('pembayaran b')
        ->select('b.id_pembayaran, p.id_pemesanan')
        ->join('pemesanan p', 'p.id_pembayaran = b.id_pembayaran', 'left')
        ->where('b.order_id', $orderId)
        ->get()->getRowArray();

    if (!$row || empty($row['id_pemesanan'])) {
        log_message('error', 'cancelOrderAndMarkKeep: order not found for {orderId}', ['orderId' => $orderId]);
        $db->transRollback();
        return false;
    }

    // Kembalikan stok
    $details = $db->table('detail_pemesanan')
        ->where('id_pemesanan', $row['id_pemesanan'])
        ->get()->getResultArray();

    foreach ($details as $d) {
        $db->query(
            "UPDATE produk SET stok = stok + ? WHERE id_produk = ?",
            [(int)$d['jumlah_produk'], (int)$d['id_produk']]
        );
    }

    // Tandai pembayaran cancel (tanpa kolom status_bayar)
    if (!empty($row['id_pembayaran'])) {
        $db->table('pembayaran')->where('id_pembayaran', $row['id_pembayaran'])->update([
            'transaction_status' => 'cancel',
            'updated_at'         => $now,
        ]);
    }

    // Update status pemesanan → Dibatalkan (TIDAK dihapus)
    $db->table('pemesanan')->where('id_pemesanan', $row['id_pemesanan'])->update([
        'status_pemesanan' => 'Dibatalkan',
        'updated_at'       => $now,
    ]);

    $db->transComplete();

    if (!$db->transStatus()) {
        log_message('error', 'cancelOrderAndMarkKeep: DB transaction failed for {orderId}', ['orderId' => $orderId]);
        return false;
    }

    return true;
}


}
