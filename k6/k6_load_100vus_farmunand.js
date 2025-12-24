import http from 'k6/http';
import { check, sleep } from 'k6';

const BASE_URL = 'https://farmunand.space';

export const options = {
  stages: [
    // Naik pelan-pelan ke 100 VU (beban normal/target)
    { duration: '1m', target: 20 },
    { duration: '2m', target: 50 },
    { duration: '3m', target: 100 }, // puncak beban normal
    { duration: '3m', target: 100 }, // tahan di 100 VU
    { duration: '2m', target: 0 },   // turunkan kembali
  ],
  thresholds: {
    // Sama seperti script sebelumnya, biar bisa dibandingkan
    'http_req_duration{kind:page}': ['p(95)<3000'],        // halaman utama < 3 detik
    'http_req_duration{kind:transaction}': ['p(95)<5000'], // transaksi < 5 detik
    http_req_failed: ['rate<0.01'],                        // error < 1%
  },
};

export default function () {
  // 1) Buka halaman login (simulasi user mulai dari sini)
  const loginPageRes = http.get(`${BASE_URL}/index.php/login`, {
    tags: { kind: 'page' },
  });

  check(loginPageRes, {
    'login page status < 400': (r) => r.status < 400,
  });

  // 2) Buka dashboard user (kalau belum login kemungkinan redirect, tetap kita ukur)
  const dashboardRes = http.get(`${BASE_URL}/dashboarduser`, {
    tags: { kind: 'page' },
  });

  check(dashboardRes, {
    'dashboard status < 400': (r) => r.status < 400,
  });

  // 3) Buka detail salah satu produk (ID 1 contoh, silakan sesuaikan kalau perlu)
  const detailRes = http.get(`${BASE_URL}/detailproduk/1`, {
    tags: { kind: 'transaction' },
  });

  check(detailRes, {
    'detail status < 400': (r) => r.status < 400,
  });

  // 4) Buka halaman keranjang
  const cartRes = http.get(`${BASE_URL}/keranjang`, {
    tags: { kind: 'transaction' },
  });

  check(cartRes, {
    'keranjang status < 400': (r) => r.status < 400,
  });

  // jeda kecil antar iterasi (biar user simulasi nggak terlalu "robot banget")
  sleep(1);
}
