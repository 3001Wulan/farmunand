<?php

namespace App\Controllers;

use App\Models\PesananModel;

class KonfirmasiPesanan extends BaseController
{
    protected $pesananModel;

    public function __construct()
    {
        $this->pesananModel = new PesananModel();
    }

    public function index()
{
    $id_user = session()->get('id_user') ?? 1;

    // Ambil semua pesanan user lewat model
    $pesanan = $this->pesananModel->getPesananWithProduk($id_user);

    // Filter hanya yang status "Dikirim"
    $data['pesanan'] = array_filter($pesanan, function($p) {
        return $p['status_pemesanan'] === 'dikirim';
    });

    return view('pembeli/konfirmasipesanan', $data);
}

    public function selesai($id_pemesanan)
    {
        // Update status jadi 'Selesai'
        $this->pesananModel->update($id_pemesanan, [
            'status_pemesanan' => 'Selesai'
        ]);

        // Redirect ke halaman konfirmasi
        return redirect()->to('/konfirmasipesanan')
                         ->with('success', 'Pesanan berhasil dikonfirmasi!');
    }
}
