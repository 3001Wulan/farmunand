<?php

namespace App\Controllers;

use Config\Database;
use App\Models\ProdukModel;
use App\Models\AlamatModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class MelakukanPemesanan extends BaseController
{
    protected $produkModel;
    protected $alamatModel;
    protected $userModel;

    public function __construct()
    {
        $this->produkModel = new ProdukModel();
        $this->alamatModel = new AlamatModel();
        $this->userModel   = new UserModel();
    }

    // Halaman checkout
    public function index($idProdukFromSegment = null)
    {
        $session = session();

        $idUser = $session->get('id_user');
        if (!$idUser) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $idProduk = $this->request->getPost('id_produk')
                ?? $this->request->getGet('id_produk')
                ?? $idProdukFromSegment;

        $qty = (int)($this->request->getPost('qty') ?? $this->request->getGet('qty') ?? 1);
        if ($qty < 1) $qty = 1;

        $checkout = null;          // single item
        $checkoutMulti = null;     // multi items

        if ($idProduk) {
            // === Single item (seperti sebelumnya) ===
            $produk = $this->produkModel->find($idProduk);
            if (!$produk) return redirect()->back()->with('error', 'Produk tidak ditemukan.');

            $stok = (int)($produk['stok'] ?? 0);
            if ($stok <= 0) return redirect()->back()->with('error', 'Stok produk habis.');
            if ($qty > $stok) { $qty = $stok; $session->setFlashdata('info', 'Jumlah melebihi stok, disesuaikan.'); }

            $checkout = [
                'id_produk'   => (int)$produk['id_produk'],
                'nama_produk' => $produk['nama_produk'],
                'deskripsi'   => $produk['deskripsi'] ?? '',
                'foto'        => $produk['foto'] ?? 'default.png',
                'harga'       => (float)$produk['harga'],
                'qty'         => $qty,
                'subtotal'    => (float)$produk['harga'] * $qty,
            ];
            $session->set('checkout_data', $checkout);

        } else {
            // === Multi item (hasil dari Keranjang::checkoutAll) ===
            $batch = $session->get('checkout_all');

            if (is_array($batch) && !empty($batch)) {
                $items = [];
                $grandTotal = 0;
                $adjusted = false;

                foreach ($batch as $row) {
                    $pid = (int)($row['id_produk'] ?? 0);
                    $qty = (int)($row['qty'] ?? 0);
                    if ($pid <= 0 || $qty <= 0) continue;

                    $produk = $this->produkModel->find($pid);
                    if (!$produk) continue;

                    $stok = (int)($produk['stok'] ?? 0);
                    if ($stok <= 0) continue;
                    if ($qty > $stok) { $qty = $stok; $adjusted = true; }

                    $harga = (float)$produk['harga'];
                    $subtotal = $harga * $qty;

                    $items[] = [
                        'id_produk'   => (int)$produk['id_produk'],
                        'nama_produk' => $produk['nama_produk'],
                        'foto'        => $produk['foto'] ?? 'default.png',
                        'harga'       => $harga,
                        'qty'         => $qty,
                        'subtotal'    => $subtotal,
                    ];
                    $grandTotal += $subtotal;
                }

                if (empty($items)) {
                    // tidak ada item valid
                    $session->remove('checkout_all');
                    return redirect()->to('/keranjang')->with('error', 'Tidak ada item valid untuk checkout.');
                }

                if ($adjusted) {
                    $session->setFlashdata('info', 'Sebagian jumlah menyesuaikan stok tersedia.');
                }

                $checkoutMulti = [
                    'items'      => $items,
                    'grandTotal' => $grandTotal,
                ];

                // persist supaya bisa dipakai step berikutnya (pembayaran)
                $session->set('checkout_data_multi', $checkoutMulti);

            } else {
                // fallback ke session single (mis. user balik dari pilih alamat)
                $saved = $session->get('checkout_data');
                if (is_array($saved) && !empty($saved['id_produk'])) {
                    // refresh single seperti sebelumnya
                    $produk = $this->produkModel->find($saved['id_produk']);
                    if ($produk) {
                        $stok = (int)($produk['stok'] ?? 0);
                        $qtySaved = (int)($saved['qty'] ?? 1);
                        if ($stok <= 0) return redirect()->to('/keranjang')->with('error', 'Stok produk habis.');
                        if ($qtySaved > $stok) { $qtySaved = $stok; $session->setFlashdata('info', 'Jumlah melebihi stok, disesuaikan.'); }

                        $checkout = [
                            'id_produk'   => (int)$produk['id_produk'],
                            'nama_produk' => $produk['nama_produk'],
                            'deskripsi'   => $produk['deskripsi'] ?? '',
                            'foto'        => $produk['foto'] ?? 'default.png',
                            'harga'       => (float)$produk['harga'],
                            'qty'         => $qtySaved,
                            'subtotal'    => (float)$produk['harga'] * $qtySaved,
                        ];
                        $session->set('checkout_data', $checkout);
                    } else {
                        $session->remove('checkout_data');
                        return redirect()->to('/keranjang')->with('error', 'Produk tidak tersedia.');
                    }
                } else {
                    // tidak ada context apapun
                    return redirect()->to('/keranjang')->with('error', 'Data pesanan tidak ditemukan.');
                }
            }
        }

        // Ambil alamat user (aktif duluan)
        $alamat = $this->alamatModel
                        ->where('id_user', $idUser)
                        ->orderBy('aktif', 'DESC')
                        ->orderBy('id_alamat', 'DESC')
                        ->findAll();

        return view('pembeli/melakukanpemesanan', [
            'checkout'       => $checkout,       // single (null kalau multi)
            'checkout_multi' => $checkoutMulti,  // multi (null kalau single)
            'alamat'         => $alamat,
            'user'           => $this->userModel->find($idUser),
        ]);
    }



    // 🧩 Simpan pesanan ke database
    public function simpan()
    {
        $session = session();
        $idUser  = (int) $session->get('id_user');
        if (!$idUser) {
            return $this->response->setJSON(['success' => false, 'message' => 'User belum login']);
        }

        // Ambil input
        $idProduk = (int) $this->request->getPost('id_produk');
        $idAlamat = (int) $this->request->getPost('id_alamat');
        $qty      = max(1, (int) $this->request->getPost('qty'));
        $metode   = strtolower(trim((string) $this->request->getPost('metode')) ?: 'cod');

        if ($idProduk <= 0 || $idAlamat <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data pesanan tidak lengkap.']);
        }

        // Ambil produk dari DB (jangan percaya harga dari client)
        $produk = $this->produkModel->find($idProduk);
        if (!$produk) {
            return $this->response->setJSON(['success' => false, 'message' => 'Produk tidak ditemukan.']);
        }

        $stok  = (int) ($produk['stok'] ?? 0);
        $harga = (float) ($produk['harga'] ?? 0);

        if ($stok <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Stok produk habis.']);
        }
        if ($qty > $stok) {
            // Boleh hard-fail, atau auto-sesuaikan. Di sini hard-fail biar jelas.
            return $this->response->setJSON(['success' => false, 'message' => 'Jumlah melebihi stok tersedia.']);
        }

        $isCOD  = ($metode === 'cod');
        $status = $isCOD ? 'Dikemas' : 'Menunggu Pembayaran';
        $total  = $harga * $qty;

        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $db->transStart();

        // 1) pembayaran (khusus non-COD)
        $idPembayaran = null;
        if (!$isCOD) {
            $db->table('pembayaran')->insert([
                'metode'       => ucfirst($metode),   // mis. Transfer/Ewallet
                'status_bayar' => 'Belum Bayar',
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
            $idPembayaran = $db->insertID();
        }

        // 2) pemesanan (header)
        $db->table('pemesanan')->insert([
            'id_user'          => $idUser,
            'id_alamat'        => $idAlamat,
            'id_pembayaran'    => $idPembayaran,
            'status_pemesanan' => $status,
            'total_harga'      => $total,
            'created_at'       => $now,
            'updated_at'       => $now,
        ]);
        $idPemesanan = (int) $db->insertID();

        // 3) detail_pemesanan
        $db->table('detail_pemesanan')->insert([
            'id_pemesanan'  => $idPemesanan,
            'id_produk'     => $idProduk,
            'jumlah_produk' => $qty,
            'harga_produk'  => $harga,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        // 4) pengurangan stok ATOMIK (guard race condition)
        $db->query(
            "UPDATE produk SET stok = stok - ? WHERE id_produk = ? AND stok >= ?",
            [$qty, $idProduk, $qty]
        );
        if ($db->affectedRows() === 0) {
            // stok berubah sebelum commit
            $db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Stok berubah, silakan ulangi checkout.'
            ]);
        }

        $db->transComplete();
        if (!$db->transStatus()) {
            $err = $db->error();
            return $this->response->setJSON([
                'success'  => false,
                'message'  => 'Gagal menyimpan pesanan.',
                'db_error' => $err['message'] ?? null
            ]);
        }

        // === BERESKAN KERANJANG & CONTEXT CHECKOUT ===
        // Hapus item ini dari keranjang user (jika ada)
        $cartKey  = 'cart_u_' . $idUser;
        $countKey = 'cart_count_u_' . $idUser;
        $cart     = $session->get($cartKey) ?? [];

        if (isset($cart[$idProduk])) {
            unset($cart[$idProduk]);

            // Simpan kembali cart & hitung ulang badge count
            $session->set($cartKey, $cart);
            $count = 0;
            foreach ($cart as $row) {
                $count += (int)($row['qty'] ?? 0);
            }
            $session->set($countKey, $count);

            // Jika keranjang kosong, bersihkan key sekalian
            if ($count === 0) {
                $session->remove([$cartKey, $countKey]);
            }
        }

        // Bersihkan context checkout single
        $session->remove(['checkout_data']);

        return $this->response->setJSON([
            'success'      => true,
            'status'       => $status,
            'id_pemesanan' => $idPemesanan,
            'total'        => $total,
            // optional hint ke FE kalau perlu redirect ke halaman status
            // 'redirect'   => $isCOD ? base_url('pesanandikemas') : base_url('pesananbelumbayar'),
        ]);
    }

    public function simpanBatch()
    {
        $session = session();
        $idUser  = (int) $session->get('id_user');
        if (!$idUser) {
            return $this->response->setStatusCode(401)
                ->setJSON(['success' => false, 'message' => 'Silakan login.']);
        }

        // Payload JSON: { id_alamat, metode, items:[{id_produk, qty}, ...] }
        $payload  = $this->request->getJSON(true);
        $idAlamat = (int)($payload['id_alamat'] ?? 0);
        $metode   = strtolower(trim($payload['metode'] ?? 'cod'));
        $items    = $payload['items'] ?? [];

        if ($idAlamat <= 0 || empty($items) || !is_array($items)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Payload tidak valid.']);
        }

        // Gabungkan qty per id_produk
        $wanted = [];
        foreach ($items as $it) {
            $pid = (int)($it['id_produk'] ?? 0);
            $qty = (int)($it['qty'] ?? 0);
            if ($pid > 0 && $qty > 0) {
                $wanted[$pid] = ($wanted[$pid] ?? 0) + $qty;
            }
        }
        if (!$wanted) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak ada item valid.']);
        }

        $db = Database::connect();

        // Ambil produk yang dibutuhkan
        $produkList = $this->produkModel->whereIn('id_produk', array_keys($wanted))->findAll();
        if (!$produkList) {
            return $this->response->setJSON(['success' => false, 'message' => 'Produk tidak ditemukan.']);
        }

        // Index by id
        $byId = [];
        foreach ($produkList as $p) $byId[(int)$p['id_produk']] = $p;

        // Validasi stok + hitung total
        $detailRows = [];
        $grandTotal = 0;
        foreach ($wanted as $pid => $qty) {
            if (!isset($byId[$pid])) continue;

            $p     = $byId[$pid];
            $stok  = (int)($p['stok'] ?? 0);
            $harga = (float)($p['harga'] ?? 0);

            if ($stok <= 0) {
                return $this->response->setJSON(['success' => false, 'message' => "Stok habis untuk produk ID $pid."]);
            }
            if ($qty > $stok) {
                return $this->response->setJSON(['success' => false, 'message' => "Qty melebihi stok untuk produk ID $pid."]);
            }

            $subtotal    = $harga * $qty;
            $grandTotal += $subtotal;

            $detailRows[] = [
                'id_produk'     => (int)$pid,
                'jumlah_produk' => (int)$qty,
                'harga_produk'  => $harga,
            ];
        }
        if (!$detailRows) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak ada item valid untuk diproses.']);
        }

        // Tentukan status sesuai metode
        $isCOD  = ($metode === 'cod');
        $status = $isCOD ? 'Dikemas' : 'Menunggu Pembayaran';

        // Transaksi simpan
        $db->transStart();
        $now = date('Y-m-d H:i:s');

        // 1) pembayaran — hanya untuk non-COD
        $idPembayaran = null;
        if (!$isCOD) {
            $db->table('pembayaran')->insert([
                'metode'       => ucfirst($metode),   // Transfer / Ewallet / dsb
                'referensi'    => null,
                'status_bayar' => 'Belum Bayar',
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
            $idPembayaran = $db->insertID();
        }

        // 2) pemesanan (header)
        $db->table('pemesanan')->insert([
            'id_user'          => $idUser,
            'id_alamat'        => $idAlamat,
            'id_pembayaran'    => $idPembayaran,
            'status_pemesanan' => $status,     // <- DISESUAIKAN DGN simpan()
            'total_harga'      => $grandTotal,
            'created_at'       => $now,
            'updated_at'       => $now,
        ]);
        $idPemesanan = $db->insertID();

        // 3) detail_pemesanan + pengurangan stok atomik
        foreach ($detailRows as $d) {
            $db->table('detail_pemesanan')->insert([
                'id_pemesanan'  => $idPemesanan,
                'id_produk'     => $d['id_produk'],
                'jumlah_produk' => $d['jumlah_produk'],
                'harga_produk'  => $d['harga_produk'],
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);

            // guard stok
            $db->query(
                "UPDATE produk SET stok = stok - ? WHERE id_produk = ? AND stok >= ?",
                [$d['jumlah_produk'], $d['id_produk'], $d['jumlah_produk']]
            );
            if ($db->affectedRows() === 0) {
                $db->transRollback();
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Stok berubah, silakan ulangi checkout.'
                ]);
            }
        }

        $db->transComplete();
        if (!$db->transStatus()) {
            $err = $db->error();
            return $this->response->setJSON([
                'success'  => false,
                'message'  => 'Gagal menyimpan pesanan.',
                'db_error' => $err['message'] ?? null
            ]);
        }

        // Bereskan keranjang & context batch
        $cartKey  = 'cart_u_' . $idUser;
        $countKey = 'cart_count_u_' . $idUser;
        $session->remove([$cartKey, $countKey, 'checkout_all', 'checkout_data_multi', 'checkout_data']);

        return $this->response->setJSON([
            'success'       => true,
            'status'        => $status,       // <- balikan status konsisten
            'id_pemesanan'  => $idPemesanan,
            'total'         => $grandTotal
        ]);
    }
}
