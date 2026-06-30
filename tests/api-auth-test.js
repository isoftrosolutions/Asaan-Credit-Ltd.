const axios = require('axios');

const API = 'http://localhost/assan/api';

const api = axios.create({
  baseURL: API,
  timeout: 10000,
  headers: { 'Content-Type': 'application/json' },
});

const color = {
  reset: '\x1b[0m', bold: '\x1b[1m',
  red: '\x1b[31m', green: '\x1b[32m', yellow: '\x1b[33m', cyan: '\x1b[36m', gray: '\x1b[90m',
};

let passed = 0, failed = 0;
let totalTime = 0;
const slowThreshold = 800; // ms — flag requests slower than this

function ok(label, ms) {
  passed++;
  const timeTag = ms !== undefined ? ` ${color.gray}(${ms}ms)${color.reset}` : '';
  const slowTag = ms > slowThreshold ? ` ${color.yellow}SLOW${color.reset}` : '';
  console.log(`  ${color.green}\u2713${color.reset} ${label}${timeTag}${slowTag}`);
}

function fail(label, err, ms) {
  failed++;
  const msg = err?.response?.data?.error || err?.message || err;
  const status = err?.response?.status ? ` (HTTP ${err.response.status})` : '';
  const timeTag = ms !== undefined ? ` ${color.gray}(${ms}ms)${color.reset}` : '';
  console.log(`  ${color.red}\u2717${color.reset} ${label}: ${color.red}${msg}${status}${timeTag}${color.reset}`);
}

function section(title) {
  console.log(`\n${color.bold}${color.cyan}\u2550\u2550\u2550 ${title} \u2550\u2550\u2550${color.reset}\n`);
}

async function timed(label, fn) {
  const start = Date.now();
  try {
    await fn();
    const ms = Date.now() - start;
    ok(label, ms);
    totalTime += ms;
  } catch (e) {
    const ms = Date.now() - start;
    fail(label, e, ms);
    totalTime += ms;
  }
}

// ─── Tests ────────────────────────────────────────────────────────
async function run() {
  console.log(`${color.bold}${color.yellow}
  \u2554\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2557
  \u2551   Asaan Capital — Auth API Performance Test              \u2551
  \u2551   Base: ${(API + '                ').slice(0, 44)}\u2551
  \u2551   Slow threshold: ${(slowThreshold + 'ms          ').slice(0, 42)}\u2551
  \u255a\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u255d${color.reset}\n`);

  const TEST_EMAIL = `authtest_${Date.now()}@example.com`;
  const TEST_PASS = 'Test@1234';
  let token = null, userId = null;

  // ── 1. Auth Happy Path ────────────────────────────────────
  section('1. Auth Happy Path');

  await timed('POST /auth/register — create test user', async () => {
    const { data } = await api.post('/auth/register', {
      name: 'Auth Test User',
      email: TEST_EMAIL,
      password: TEST_PASS,
      role: 'investor',
      phone: '9800000001',
      province: 'Bagmati',
      district: 'Kathmandu',
    });
    if (!data.success) throw new Error(data.error);
    if (!data.data?.token) throw new Error('No token returned');
    if (!data.data?.user?.id) throw new Error('No user id');
    token = data.data.token;
    userId = data.data.user.id;
    if (data.data.user.email !== TEST_EMAIL) throw new Error('Email mismatch');
  });

  await timed('POST /auth/login — login with correct credentials', async () => {
    const { data } = await api.post('/auth/login', { email: TEST_EMAIL, password: TEST_PASS });
    if (!data.success) throw new Error(data.error);
    if (!data.data?.token) throw new Error('No token');
    if (data.data.user.email !== TEST_EMAIL) throw new Error('Email mismatch');
    token = data.data.token; // refresh token
  });

  await timed('GET /auth/me (with Bearer token) — get profile', async () => {
    const { data } = await api.get('/auth/me', { headers: { Authorization: `Bearer ${token}` } });
    if (!data.success) throw new Error(data.error);
    if (!data.data?.id) throw new Error('No user data');
    if (data.data.email !== TEST_EMAIL) throw new Error('Wrong user');
  });

  await timed('GET /auth/me (no token) — should return 401', async () => {
    try {
      await api.get('/auth/me');
      throw new Error('Expected 401');
    } catch (e) {
      if (e.response?.status !== 401) throw e;
    }
  });

  await timed('PUT /auth/me — update phone number', async () => {
    const { data } = await api.put('/auth/me', { phone: '9812345678' }, {
      headers: { Authorization: `Bearer ${token}` },
    });
    if (!data.success) throw new Error(data.error);
  });

  await timed('GET /auth/me — verify updated phone persisted', async () => {
    const { data } = await api.get('/auth/me', { headers: { Authorization: `Bearer ${token}` } });
    if (!data.success) throw new Error(data.error);
    if (data.data.phone !== '9812345678') throw new Error('Phone not updated');
  });

  // ── 2. Auth Error Handling ────────────────────────────────
  section('2. Auth Error Handling');

  await timed('POST /auth/register (duplicate email) — should fail', async () => {
    try {
      await api.post('/auth/register', {
        name: 'Duplicate', email: TEST_EMAIL, password: TEST_PASS,
      });
      throw new Error('Expected 409/400');
    } catch (e) {
      if (e.response?.status < 400 || e.response?.status >= 500) throw e;
    }
  });

  await timed('POST /auth/login (wrong password) — should fail', async () => {
    try {
      await api.post('/auth/login', { email: TEST_EMAIL, password: 'WrongPass1!' });
      throw new Error('Expected 401');
    } catch (e) {
      if (e.response?.status !== 401) throw e;
    }
  });

  await timed('POST /auth/login (non-existent email) — should fail', async () => {
    try {
      await api.post('/auth/login', { email: 'nobody_' + Date.now() + '@x.com', password: 'Test@1234' });
      throw new Error('Expected 401');
    } catch (e) {
      if (e.response?.status !== 401) throw e;
    }
  });

  await timed('POST /auth/login (empty body) — should fail', async () => {
    try {
      await api.post('/auth/login', {});
      throw new Error('Expected 400');
    } catch (e) {
      if (e.response?.status < 400 || e.response?.status >= 500) throw e;
    }
  });

  await timed('POST /auth/forgot-password (non-existent email) — returns generic message', async () => {
    const { data } = await api.post('/auth/forgot-password', { email: 'doesnotexist_' + Date.now() + '@x.com' });
    if (!data.success) throw new Error(data.error);
    if (!data.data?.message) throw new Error('Expected message');
  });

  await timed('POST /auth/reset-password (invalid OTP) — should fail', async () => {
    try {
      await api.post('/auth/reset-password', {
        email: TEST_EMAIL, otp: '000000', password: 'NewPass@123',
      });
      throw new Error('Expected 400/401');
    } catch (e) {
      if (e.response?.status < 400 || e.response?.status >= 500) throw e;
    }
  });

  await timed('POST /auth/verify-email (invalid OTP) — should fail', async () => {
    try {
      await api.post('/auth/verify-email', { email: TEST_EMAIL, otp: '000000' });
      throw new Error('Expected 400/401');
    } catch (e) {
      if (e.response?.status < 400 || e.response?.status >= 500) throw e;
    }
  });

  await timed('PUT /auth/me (no token) — should return 401', async () => {
    try {
      await api.put('/auth/me', { name: 'Hacker' });
      throw new Error('Expected 401');
    } catch (e) {
      if (e.response?.status !== 401) throw e;
    }
  });

  // ── 3. Login Latency Profile ──────────────────────────────
  section('3. Login Latency Profile');

  // Cold login (first login after register — may be slower due to session init)
  await timed('Cold login performance', async () => {
    const { data } = await api.post('/auth/login', { email: TEST_EMAIL, password: TEST_PASS });
    if (!data.success) throw new Error(data.error);
    token = data.data.token;
  });

  // Warm login (subsequent — should be faster)
  await timed('Warm login performance', async () => {
    const { data } = await api.post('/auth/login', { email: TEST_EMAIL, password: TEST_PASS });
    if (!data.success) throw new Error(data.error);
    token = data.data.token;
  });

  // Token reuse — does /auth/me work quickly with an existing token?
  await timed('GET /auth/me (token reuse) — should be fast', async () => {
    const { data } = await api.get('/auth/me', { headers: { Authorization: `Bearer ${token}` } });
    if (!data.success) throw new Error(data.error);
  });

  // ── 4. Forgot Password Flow ───────────────────────────────
  section('4. Forgot Password Flow');

  await timed('POST /auth/forgot-password — send OTP', async () => {
    const { data } = await api.post('/auth/forgot-password', { email: TEST_EMAIL });
    if (!data.success) throw new Error(data.error);
  });

  await timed('POST /auth/resend-otp (type=password) — resend', async () => {
    try {
      const { data } = await api.post('/auth/resend-otp', { email: TEST_EMAIL, type: 'password' });
      if (!data.success) throw new Error(data.error);
    } catch (e) {
      // Rate-limited is OK as long as it's not a server error
      if (e.response?.status === 429) return;
      throw e;
    }
  });

  // ── 5. Logout + Token Invalidation ────────────────────────
  section('5. Logout & Token Invalidation');

  await timed('POST /auth/logout — log out', async () => {
    const { data } = await api.post('/auth/logout', {}, {
      headers: { Authorization: `Bearer ${token}` },
    });
    if (!data.success) throw new Error(data.error);
  });

  await timed('GET /auth/me (after logout — token invalidated) — should 401', async () => {
    try {
      await api.get('/auth/me', { headers: { Authorization: `Bearer ${token}` } });
      throw new Error('Expected 401');
    } catch (e) {
      if (e.response?.status !== 401) throw e;
    }
  });

  // ── 6. Register Again (test new user with different role) ──
  section('6. Cross-role Auth');

  const bizEmail = `biz_${Date.now()}@example.com`;
  let bizToken = null;

  await timed('POST /auth/register (business_owner role)', async () => {
    const { data } = await api.post('/auth/register', {
      name: 'Biz Owner', email: bizEmail, password: TEST_PASS, role: 'business_owner',
    });
    if (!data.success) throw new Error(data.error);
    if (data.data.user.role !== 'business_owner') throw new Error('Wrong role');
    bizToken = data.data.token;
  });

  await timed('GET /auth/me (business_owner) — verify role', async () => {
    const { data } = await api.get('/auth/me', { headers: { Authorization: `Bearer ${bizToken}` } });
    if (!data.success) throw new Error(data.error);
    if (data.data.role !== 'business_owner') throw new Error('Role mismatch');
  });

  // ── Summary ───────────────────────────────────────────────
  const total = passed + failed;
  const avg = total > 0 ? Math.round(totalTime / total) : 0;
  console.log(`\n${color.bold}${color.yellow}\u2550\u2550\u2550 RESULTS \u2550\u2550\u2550${color.reset}`);
  console.log(`  ${color.green}Passed: ${passed}${color.reset}`);
  console.log(`  ${color.red}Failed: ${failed}${color.reset}`);
  console.log(`  Total:  ${total}`);
  console.log(`  Rate:   ${Math.round(passed / total * 100)}%`);
  console.log(`  Total time: ${totalTime}ms  |  Avg: ${avg}ms/request`);

  // Report slow endpoints
  process.exit(failed > 0 ? 1 : 0);
}

run().catch(e => {
  console.error(`${color.red}Fatal error:${color.reset}`, e.message);
  process.exit(1);
});
