<?php

namespace App\Controllers;

use App\Models\ProdukModel;
use App\Models\PesananModel;
use App\Models\UserModel;
use Config\Database;

class DashboardUser extends BaseController
{
    protected $userModel;
    protected $pesananModel;

    public function __construct()
    {
        $this->userModel    = new UserModel();
        $this->pesananModel = new PesananModel();
    }
    
    public function index()
    {
        $userId = session()->get('id_user');
        if (!$userId) {
            return redirect()->to('/login')->with('error', 'Silakan login dulu.');
        }

        $userModel    = new UserModel();
        $pesananModel = new PesananModel();
        $db           = Database::connect();

        $user = $userModel->find($userId);

        // ===== METRIK =====
        // Sukses = Selesai
        $pesananSukses = (new PesananModel())
            ->where('id_user', $userId)
            ->where('status_pemesanan', 'Selesai')
            ->countAllResults();

        // Pending = Dikemas + Dikirim + Belum Bayar
        $pendingStatuses = ['Dikemas', 'Dikirim', 'Belum Bayar'];
        $pesananPending = (new PesananModel())
            ->where('id_user', $userId)
            ->whereIn('status_pemesanan', $pendingStatuses)
            ->countAllResults();

        // Dibatalkan
        $pesananBatal = (new PesananModel())
            ->where('id_user', $userId)
            ->where('status_pemesanan', 'Dibatalkan')
            ->countAllResults();

        // ===== PRODUK + RATING =====
        $produk = $db->table('produk pr')
            ->select("
                pr.*,
                COALESCE(AVG(CASE WHEN dp.user_rating > 0 THEN dp.user_rating END), 0) AS avg_rating,
                SUM(CASE WHEN dp.user_rating > 0 THEN 1 ELSE 0 END)               AS rating_count
            ")
            ->join('detail_pemesanan dp', 'dp.id_produk = pr.id_produk', 'left')
            ->groupBy('pr.id_produk')
            ->orderBy('pr.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'title'          => 'Dashboard User',
            'username'       => $user['username'] ?? '',
            'role'           => $user['role'] ?? '',
            'foto'           => $user['foto'] ?? null,
            'pesanan_sukses' => $pesananSukses,
            'pending'        => $pesananPending,
            'batal'          => $pesananBatal,
            'produk'         => $produk,
            'user'           => $user,
        ];

        return view('Pembeli/dashboarduser', $data);
    }
}
