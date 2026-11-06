import http from 'k6/http';
import { check, sleep } from 'k6';

// Konfigurasi Target URL
const TARGET_URL = __ENV.TARGET_URL || 'https://tugasukk.web.id';

export const options = {
  stages: [
    { duration: '1m', target: 200 },   // Ramp-up ke 200 concurrent users
    { duration: '2m', target: 500 },   // Beban stabil 500 concurrent users
    { duration: '2m', target: 1000 },  // Puncak stress test 1.000 concurrent users
    { duration: '1m', target: 0 },     // Ramp-down pendinginan
  ],
  thresholds: {
    http_req_duration: ['p(95)<500'],  // 95% request wajib selesai di bawah 500ms
    http_req_failed: ['rate<0.01'],    // Error rate maksimal di bawah 1% (99% success)
  },
};

export default function () {
  // Test 1: Hit Root Route (Ekspektasi: 302 Redirect ke /login)
  const resRoot = http.get(`${TARGET_URL}/`, { redirects: 0 });
  check(resRoot, {
    'root responds with 302 redirect': (r) => r.status === 302,
  });

  // Test 2: Akses Halaman Login Presensi UKK (Ekspektasi: 200 OK)
  const resLogin = http.get(`${TARGET_URL}/login`);
  check(resLogin, {
    'login page status is 200': (r) => r.status === 200,
    'login page contains form': (r) => r.body && r.body.includes('password'),
  });

  sleep(1);
}

