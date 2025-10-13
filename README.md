# CodeIgniter 4 Application Starter

## What is CodeIgniter?

CodeIgniter is a PHP full-stack web framework that is light, fast, flexible and secure.
More information can be found at the [official site](https://codeigniter.com).

This repository holds a composer-installable app starter.
It has been built from the
[development repository](https://github.com/codeigniter4/CodeIgniter4).

More information about the plans for version 4 can be found in [CodeIgniter 4](https://forum.codeigniter.com/forumdisplay.php?fid=28) on the forums.

You can read the [user guide](https://codeigniter.com/user_guide/)
corresponding to the latest version of the framework.

## Installation & updates

`composer create-project codeigniter4/appstarter` then `composer update` whenever
there is a new release of the framework.

When updating, check the release notes to see if there are any changes you might need to apply
to your `app` folder. The affected files can be copied or merged from
`vendor/codeigniter4/framework/app`.

## Setup
# FarmUnand — Aplikasi CodeIgniter 4

Aplikasi e-commerce sederhana berbasis **CodeIgniter 4** dengan fitur:

* Pembayaran online via **Midtrans Snap**
* Email development (lupa password) via **Mailtrap**
* Utilitas **Dompdf** & **PhpSpreadsheet** (opsional)

## 1) Kebutuhan Sistem

* **PHP 8.1+** dengan ekstensi: `intl`, `mbstring`, `json`, `curl`, dan (jika MySQL) `mysqlnd`
* **Composer**
* **MySQL/MariaDB**
* **ngrok** (opsional, untuk menerima notifikasi Midtrans di lokal)

> Paket composer inti yang digunakan: `codeigniter4/framework`, `midtrans/midtrans-php`, `dompdf/dompdf`, `phpoffice/phpspreadsheet` (+ beberapa dev packages untuk testing).

---

## 2) Instalasi

```bash
# clone repo
git clone <URL-REPO-ANDA>
cd <nama-folder-repo>

# install dependency PHP
composer install
```

---

## 3) Konfigurasi Lingkungan (.env)

Salin file contoh lalu sesuaikan:

```bash
cp env .env
```

Edit `.env` sesuai lingkungan Anda. Contoh yang penting:

**URL Aplikasi / Port**

```
app.baseURL = 'http://localhost:8080/'
```

**Database**

```
database.default.hostname = localhost
database.default.database = farmunand
database.default.username = root
database.default.password =
database.default.DBDriver  = MySQLi
database.default.port      = 3306
```

**Midtrans (Sandbox)**

```
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SERVER_KEY=Mid-server-xxxxxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxx
```

**Mail (Mailtrap)**

```
email.protocol   = smtp
email.SMTPHost   = sandbox.smtp.mailtrap.io
email.SMTPUser   = xxxxxxxx
email.SMTPPass   = "yyyyyyyy"
email.SMTPPort   = 2525
email.SMTPCrypto = tls
email.fromEmail  = noreply@farmunand.dev
email.fromName   = "FarmUnand App"
email.mailType   = html
```

> Tip: untuk lokal, biarkan `CI_ENVIRONMENT = development`.

---

## 4) Database

1. Buat database sesuai nama di `.env` (misal `farmunand`).
2. Jalankan migration/seeder jika ada, atau impor dump SQL milik Anda.

---

## 5) Menjalankan Aplikasi (Lokal)

```bash
# akan berjalan di http://localhost:8080
php spark serve --port 8080
```

---

## 6) Midtrans (Sandbox)

Aplikasi memanggil **Midtrans Snap** via `midtrans/midtrans-php` menggunakan key dari `.env`.

### Rute yang digunakan

* Buat transaksi (online): **POST** `/payments/create`
  (membuat `pemesanan`, `pembayaran`, panggil Snap, simpan `snap_token` & `redirect_url`)
* Lanjutkan pembayaran berdasarkan **order_id**: **POST** `/payments/resume/{order_id}`
* Webhook/Notifikasi: **POST** `/payments/webhook`
* Halaman redirect hasil Snap:

  * `/payments/finish`  → redirect ke daftar **Dikemas**
  * `/payments/unfinish` → redirect ke **Belum Bayar**
  * `/payments/error`   → redirect ke **Belum Bayar**

### Notification URL (WAJIB diisi)

Pada **Midtrans Dashboard** → *Settings* → *Configuration* → **Payment Notification URL**, set ke:

```
https://<URL-PUBLIK-ANDA>/payments/webhook
```

> Saat lokal, gunakan **ngrok** untuk URL publik (lihat bagian ngrok).

---

## 7) Mailtrap (Email Lupa Password)

1. Buat **Inbox** di Mailtrap (Email Testing).
2. Salin kredensial SMTP (User/Pass/Host/Port) ke `.env` (bagian email di atas).
3. Saat mengirim email lupa password, pesan akan tertangkap di Inbox Mailtrap (tidak terkirim ke email real).

---

## 8) Membuka Akses Lokal untuk Webhook Midtrans (ngrok)

**Install ngrok**: [https://ngrok.com/download](https://ngrok.com/download)
Login & set auth token:

```bash
ngrok config add-authtoken <AUTH_TOKEN_ANDA>
```

Jalankan tunnel ke server lokal:

```bash
# jika app berjalan di http://localhost:8080
ngrok http 8080
# atau:
ngrok http http://localhost:8080
```

Anda akan memperoleh URL publik, misalnya:

```
https://nama-acak.ngrok-free.app -> http://localhost:8080
```

Salin URL **https** tersebut ke **Payment Notification URL** Midtrans:

```
https://nama-acak.ngrok-free.app/payments/webhook
```

> Biarkan terminal ngrok tetap terbuka saat pengujian. Jika URL ngrok berubah, jangan lupa update lagi di Midtrans.

---

## 9) Alur Checkout Singkat

1. User menambahkan produk ke **Keranjang**.
2. Halaman **Pemesanan**:

   * **COD** → buat pesanan langsung (stok berkurang, keranjang dibersihkan).
   * **Bayar Online (Midtrans)** → server membuat transaksi Midtrans, kirim balik `snapToken`, Snap popup dibuka.
3. Di Snap:

   * **Berhasil** → redirect ke **/pesanandikemas** (status order “Dikemas” setelah webhook masuk).
   * **Pending/Unfinish** → tetap di **Belum Bayar**, pengguna bisa klik **Lanjutkan Pembayaran**.
   * **Error/Expired** → status jadi **Dibatalkan**, (opsional) arahkan ke **Dashboard** untuk order ulang.

---

## 10) Catatan Produksi

* Ubah ke **Production**:

  ```
  MIDTRANS_IS_PRODUCTION=true
  MIDTRANS_SERVER_KEY=Mid-server-<prod>
  MIDTRANS_CLIENT_KEY=Mid-client-<prod>
  ```
* Gunakan domain **HTTPS** & set `app.baseURL` ke domain produksi.
* Konfigurasi web server agar document root mengarah ke folder **/public**.

---

## 11) Troubleshooting

* **Webhook Midtrans tidak masuk**

  * Pastikan ngrok aktif & URL di Midtrans sesuai.
  * Cek ngrok inspector: [http://127.0.0.1:4040](http://127.0.0.1:4040)

* **Lambat saat ngrok aktif**

  * ngrok hanya diperlukan saat menguji webhook. Matikan ngrok saat tidak diperlukan.

* **Stok/Keranjang tidak sinkron**

  * Jalur **COD** & **Online** sama-sama mengurangi stok dan membersihkan keranjang saat create order.

---

## 12) Testing & Skrip Composer

Menjalankan test (bila disiapkan):

```bash
composer test
```

---

## 13) Ringkasan Cepat (TL;DR)

```bash
# 1) Install dep
composer install

# 2) Konfigurasi
cp env .env
# edit DB, MIDTRANS_* dan Mailtrap

# 3) Jalankan server lokal
php spark serve --port 8080

# 4) Buka akses webhook
ngrok http 8080
# lalu set: https://<ngrok>/payments/webhook di Midtrans

# 5) Coba transaksi sandbox
# lakukan checkout online, selesaikan di Snap, periksa DB berubah lewat webhook
```

---

**Lisensi:** MIT (mengikuti CodeIgniter App Starter)
