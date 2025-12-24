// k6_volume_20vus_farmunand.js
import http from 'k6/http';
import { check, sleep } from 'k6';

const BASE_URL = 'https://farmunand.space';

export const options = {
  // Volume test: VU stabil (20) dengan durasi cukup panjang (~30 menit)
  stages: [
    { duration: '2m', target: 20 },  // ramp-up ke 20 VU
    { duration: '26m', target: 20 }, // tahan di 20 VU (beban volume besar)
    { duration: '2m', target: 0 },   // ramp-down
  ],
  thresholds: {
    // Batas kinerja yang sama dengan skenario lain
    'http_req_duration{kind:page}': ['p(95)<3000'],
    'http_req_duration{kind:transaction}': ['p(95)<5000'],
    http_req_failed: ['rate<0.01'], // maksimal 1% request gagal
  },
};

export default function () {
  // 1) Halaman login
  const loginRes = http.get(`${BASE_URL}/index.php/login`, {
    tags: { kind: 'page' },
  });
  check(loginRes, {
    'login page status < 400': (r) => r.status < 400,
  });

  // 2) Dashboard user
  const dashboardRes = http.get(`${BASE_URL}/dashboarduser`, {
    tags: { kind: 'page' },
  });
  check(dashboardRes, {
    'dashboard status < 400': (r) => r.status < 400,
  });

  // 3) Detail produk (contoh: id=1)
  const detailRes = http.get(`${BASE_URL}/detailproduk/1`, {
    tags: { kind: 'transaction' },
  });
  check(detailRes, {
    'detail status < 400': (r) => r.status < 400,
  });

  // 4) Halaman keranjang
  const cartRes = http.get(`${BASE_URL}/keranjang`, {
    tags: { kind: 'transaction' },
  });
  check(cartRes, {
    'keranjang status < 400': (r) => r.status < 400,
  });

  // Jeda kecil antar iterasi
  sleep(1);
}
