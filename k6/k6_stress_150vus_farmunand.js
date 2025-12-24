// k6_stress_farmunand.js
// Stress & load test Aplikasi FarmUnand (hosted)

import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  stages: [
    { duration: '1m', target: 20 },   // warm up
    { duration: '2m', target: 50 },   // beban menengah
    { duration: '2m', target: 100 },  // beban target KAK (100 user)
    { duration: '2m', target: 150 },  // stress di atas target
    { duration: '1m', target: 0 },    // turunkan beban
  ],
  thresholds: {
    'http_req_duration{kind:page}': ['p(95)<3000'],
    'http_req_duration{kind:transaction}': ['p(95)<5000'],
    http_req_failed: ['rate<0.01'],
  },
};

const BASE_URL = 'https://farmunand.space';

const endpoints = {
  dashboard: '/dashboarduser',
  detailProduk: '/detailproduk/1',
  keranjang: '/keranjang',
};

export default function () {
  const resDashboard = http.get(`${BASE_URL}${endpoints.dashboard}`, {
    tags: { kind: 'page', name: 'dashboard' },
  });

  check(resDashboard, {
    'dashboard status < 400': (r) => r.status < 400,
  });

  const resDetail = http.get(`${BASE_URL}${endpoints.detailProduk}`, {
    tags: { kind: 'transaction', name: 'detail_produk' },
  });

  check(resDetail, {
    'detail status < 400': (r) => r.status < 400,
  });

  const resCart = http.get(`${BASE_URL}${endpoints.keranjang}`, {
    tags: { kind: 'transaction', name: 'keranjang' },
  });

  check(resCart, {
    'keranjang status < 400': (r) => r.status < 400,
  });

  sleep(1);
}
