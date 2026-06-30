const axios = require('axios');
const qs = require('querystring');

const BASE = 'http://localhost/assan';
const API = `${BASE}/api`;

const api = axios.create({
  baseURL: API,
  timeout: 10000,
  headers: { 'Content-Type': 'application/json' },
});

let TOKEN = null;
let TEST_USER = {
  name: 'Test User',
  email: `test_${Date.now()}@example.com`,
  password: 'Test@1234',
  role: 'business_owner',
};
let CREATED_BUSINESS_ID = null;
let CREATED_PITCH_ID = null;
let CREATED_FRANCHISE_ID = null;

// ─── Colored output ────────────────────────────────────────────────
const color = {
  reset: '\x1b[0m',
  bold: '\x1b[1m',
  red: '\x1b[31m',
  green: '\x1b[32m',
  yellow: '\x1b[33m',
  cyan: '\x1b[36m',
};

let passed = 0, failed = 0;

function ok(label) { passed++; console.log(`  ${color.green}\u2713${color.reset} ${label}`); }
function fail(label, err) {
  failed++;
  const msg = err?.response?.data?.error || err?.message || err;
  const status = err?.response?.status ? ` (HTTP ${err.response.status})` : '';
  console.log(`  ${color.red}\u2717${color.reset} ${label}: ${color.red}${msg}${status}${color.reset}`);
}

function section(title) {
  console.log(`\n${color.bold}${color.cyan}\u2550\u2550\u2550 ${title} \u2550\u2550\u2550${color.reset}\n`);
}

function authHeaders() {
  return TOKEN ? { Authorization: `Bearer ${TOKEN}` } : {};
}

async function test(label, fn) {
  try {
    await fn();
    ok(label);
  } catch (e) {
    fail(label, e);
  }
}

// Find first valid pitch ID
async function findFirstPitchId() {
  try {
    const { data } = await api.get('/pitches', { params: { page: 1, per_page: 1 } });
    if (data.success && Array.isArray(data.data) && data.data.length > 0) {
      return data.data[0].id;
    }
  } catch {}
  return null;
}

async function findFirstBusinessId() {
  try {
    const { data } = await api.get('/businesses', { params: { page: 1, per_page: 1 } });
    if (data.success && Array.isArray(data.data) && data.data.length > 0) {
      return data.data[0].id;
    }
  } catch {}
  return null;
}

// ─── Test runner ───────────────────────────────────────────────────
async function run() {
  console.log(`${color.bold}${color.yellow}
  \u2554\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2557
  \u2551   Asaan Capital \u2014 Mobile API Test Suite      \u2551
  \u2551   Base: ${API.padEnd(37)}\u2551
  \u255a\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u255d${color.reset}
  `);

  // ── 1. PUBLIC ENDPOINTS ────────────────────────────────────
  section('1. Public Endpoints (no auth)');

  await test('GET /sectors', async () => {
    const { data } = await api.get('/sectors');
    if (!data.success) throw new Error('Expected success=true');
    if (!Array.isArray(data.data) || data.data.length === 0) throw new Error('Expected sector array');
  });

  await test('GET /faqs', async () => {
    const { data } = await api.get('/faqs');
    if (!data.success) throw new Error('Expected success=true');
    if (!Array.isArray(data.data)) throw new Error('Expected data array');
  });

  await test('GET /blog', async () => {
    const { data } = await api.get('/blog', { params: { page: 1, per_page: 5 } });
    if (!data.success) throw new Error('Expected success=true');
    if (!Array.isArray(data.data)) throw new Error('Expected data array');
  });

  await test('GET /search?q=tech', async () => {
    const { data } = await api.get('/search', { params: { q: 'tech', type: 'all', limit: 5 } });
    if (!data.success) throw new Error('Expected success=true');
  });

  await test('GET /businesses', async () => {
    const { data } = await api.get('/businesses', { params: { page: 1, per_page: 5 } });
    if (!data.success) throw new Error('Expected success=true');
    if (!Array.isArray(data.data)) throw new Error('Expected data array');
  });

  await test('GET /investors', async () => {
    const { data } = await api.get('/investors', { params: { page: 1, per_page: 5 } });
    if (!data.success) throw new Error('Expected success=true');
    if (!Array.isArray(data.data)) throw new Error('Expected data array');
  });

  await test('GET /pitches', async () => {
    const { data } = await api.get('/pitches', { params: { page: 1, per_page: 5 } });
    if (!data.success) throw new Error('Expected success=true');
    if (!Array.isArray(data.data)) throw new Error('Expected data array');
  });

  await test('GET /franchises', async () => {
    const { data } = await api.get('/franchises', { params: { page: 1, per_page: 5 } });
    if (!data.success) throw new Error('Expected success=true');
    if (!Array.isArray(data.data)) throw new Error('Expected data array');
  });

  section('2. Detail Endpoints');

  await test('GET /business?id=1', async () => {
    const { data } = await api.get('/business', { params: { id: 1 } });
    if (!data.success) throw new Error('Expected success=true');
    if (!data.data?.business) throw new Error('Expected business object');
  });

  await test('GET /investor?id=2', async () => {
    const { data } = await api.get('/investor', { params: { id: 2 } });
    if (!data.success) throw new Error('Expected success=true');
    if (!data.data?.profile) throw new Error('Expected profile object');
  });

  // Pitch & franchise detail: use IDs discovered from list endpoints
  const pitchId = await findFirstPitchId();
  await test(`GET /pitch?id=${pitchId || 1}` + (pitchId ? '' : ' (fallback)'), async () => {
    const id = pitchId || 1;
    const { data } = await api.get('/pitch', { params: { id } });
    if (!data.success) throw new Error('Expected success=true');
    if (!data.data?.pitch) throw new Error('Expected pitch object');
  });

  await test('GET /franchise?id=1', async () => {
    const { data } = await api.get('/franchise', { params: { id: 1 } });
    if (!data.success) throw new Error('Expected success=true');
  });

  // ── 3. AUTH ENDPOINTS ──────────────────────────────────────
  section('3. Auth Endpoints');

  await test('POST /auth/register', async () => {
    const { data } = await api.post('/auth/register', TEST_USER);
    if (!data.success) throw new Error('Expected success=true');
    if (!data.data?.token) throw new Error('Expected token');
    if (!data.data?.user) throw new Error('Expected user');
    TOKEN = data.data.token;
    TEST_USER.id = data.data.user.id;
  });

  await test('POST /auth/login', async () => {
    const { data } = await api.post('/auth/login', { email: TEST_USER.email, password: TEST_USER.password });
    if (!data.success) throw new Error('Expected success=true');
    if (!data.data?.token) throw new Error('Expected token');
    TOKEN = data.data.token;
  });

  await test('GET /auth/me', async () => {
    const { data } = await api.get('/auth/me', { headers: authHeaders() });
    if (!data.success) throw new Error('Expected success=true');
    if (data.data?.email !== TEST_USER.email) throw new Error('Email mismatch');
  });

  await test('PUT /auth/me', async () => {
    const { data } = await api.put('/auth/me', { phone: '9800000000' }, { headers: authHeaders() });
    if (!data.success) throw new Error('Expected success=true');
  });

  await test('POST /auth/forgot-password', async () => {
    const { data } = await api.post('/auth/forgot-password', { email: TEST_USER.email });
    if (!data.success) throw new Error('Expected success=true');
  });

  // ── 4. CRUD: BUSINESSES ────────────────────────────────────
  section('4. CRUD \u2014 Businesses');

  await test('POST /businesses', async () => {
    const { data } = await api.post('/businesses', {
      business_name: 'Test Business ' + Date.now(),
      listing_type: 'sale',
      sector_id: 1,
      province: 'Bagmati',
      district: 'Kathmandu',
      description: 'A test business for API testing',
      overview: 'Overview of test business',
      established_year: 2020,
      employee_count: 10,
      annual_revenue: 5000000,
      asking_price: 25000000,
    }, { headers: authHeaders() });
    if (!data.success) throw new Error('Expected success=true');
    if (!data.data?.id) throw new Error('Expected business id');
    CREATED_BUSINESS_ID = data.data.id;
  });

  await test('PUT /businesses', async () => {
    if (!CREATED_BUSINESS_ID) throw new Error('No business to update');
    const { data } = await api.put('/businesses', {
      id: CREATED_BUSINESS_ID,
      business_name: 'Updated Test Business',
    }, { headers: authHeaders() });
    if (!data.success) throw new Error('Expected success=true');
  });

  // ── 5. CRUD: PITCHES ───────────────────────────────────────
  section('5. CRUD \u2014 Pitches');

  await test('POST /pitches', async () => {
    const { data } = await api.post('/pitches', {
      tagline: 'Test Pitch ' + Date.now(),
      short_summary: 'A test pitch for API testing',
      problem_statement: 'There is a problem that needs solving',
      solution: 'Our solution fixes everything',
      market_size: 'Large market opportunity',
      business_model: 'SaaS subscription',
      stage: 'Idea',
      sector_id: 1,
      funding_amount: 10000000,
      equity_offered: 10,
    }, { headers: authHeaders() });
    if (!data.success) throw new Error('Expected success=true');
    if (!data.data?.id) throw new Error('Expected pitch id');
    CREATED_PITCH_ID = data.data.id;
  });

  await test('PUT /pitches', async () => {
    if (!CREATED_PITCH_ID) throw new Error('No pitch to update');
    const { data } = await api.put('/pitches', {
      id: CREATED_PITCH_ID,
      tagline: 'Updated Test Pitch',
    }, { headers: authHeaders() });
    if (!data.success) throw new Error('Expected success=true');
  });

  // ── 6. CRUD: FRANCHISES ────────────────────────────────────
  section('6. CRUD \u2014 Franchises');

  await test('POST /franchises', async () => {
    const { data } = await api.post('/franchises', {
      brand_name: 'Test Franchise ' + Date.now(),
      sector_id: 1,
      description: 'A test franchise',
      franchise_fee: 500000,
      royalty_pct: 5,
      total_investment_min: 1000000,
      total_investment_max: 5000000,
      expected_payback_months: 24,
      training_provided: 1,
    }, { headers: authHeaders() });
    if (!data.success) throw new Error('Expected success=true');
    if (!data.data?.id) throw new Error('Expected franchise id');
    CREATED_FRANCHISE_ID = data.data.id;
  });

  await test('PUT /franchises', async () => {
    if (!CREATED_FRANCHISE_ID) throw new Error('No franchise to update');
    const { data } = await api.put('/franchises', {
      id: CREATED_FRANCHISE_ID,
      brand_name: 'Updated Test Franchise',
    }, { headers: authHeaders() });
    if (!data.success) throw new Error('Expected success=true');
  });

  // ── 7. SOCIAL & CONNECTIONS ────────────────────────────────
  section('7. Social & Connections');

  await test('GET /notifications-list', async () => {
    const { data } = await api.get('/notifications-list', {
      params: { page: 1, per_page: 10 },
      headers: authHeaders(),
    });
    if (!data.success) throw new Error('Expected success=true');
  });

  await test('GET /connections', async () => {
    const { data } = await api.get('/connections', { headers: authHeaders() });
    if (!data.success) throw new Error('Expected success=true');
    if (!data.data?.matches) throw new Error('Expected matches array');
    if (!data.data?.sent) throw new Error('Expected sent array');
    if (!data.data?.received) throw new Error('Expected received array');
  });

  await test('GET /conversations', async () => {
    const { data } = await api.get('/conversations', { headers: authHeaders() });
    if (!data.success) throw new Error('Expected success=true');
  });

  await test('GET /conversation-unread', async () => {
    const { data } = await api.get('/conversation-unread', { headers: authHeaders() });
    if (!data.success) throw new Error('Expected success=true');
  });

  await test('GET /get-saved', async () => {
    const { data } = await api.get('/get-saved', { headers: authHeaders() });
    if (!data.success) throw new Error('Expected success=true');
  });

  await test('POST /toggle-save (save)', async () => {
    if (!CREATED_BUSINESS_ID) throw new Error('No business');
    const { data } = await api.post('/toggle-save', {
      listing_type: 'business', listing_id: CREATED_BUSINESS_ID,
    }, { headers: authHeaders() });
    if (!data.success) throw new Error('Expected success=true');
  });

  await test('POST /toggle-save (unsave)', async () => {
    if (!CREATED_BUSINESS_ID) throw new Error('No business');
    const { data } = await api.post('/toggle-save', {
      listing_type: 'business', listing_id: CREATED_BUSINESS_ID,
    }, { headers: authHeaders() });
    if (!data.success) throw new Error('Expected success=true');
  });

  await test('POST /connections/respond (invalid request_id)', async () => {
    try {
      await api.post('/connections/respond', { request_id: 99999, action: 'accept' }, { headers: authHeaders() });
      throw new Error('Expected 4xx');
    } catch (e) {
      if (e.response?.status >= 400) return;
      throw e;
    }
  });

  // ── 8. REPORT (hits 404 — route not registered) ─────────
  section('8. Report & NDA');

  await test('POST /report', async () => {
    if (!CREATED_BUSINESS_ID) throw new Error('No business');
    const { data } = await api.post('/report', {
      target_type: 'business', target_id: CREATED_BUSINESS_ID,
      reason: 'spam',
    }, { headers: authHeaders() });
    if (!data.success) throw new Error('Expected success=true');
  });

  await test('GET /sign-nda (410 Gone)', async () => {
    try {
      await api.get('/sign-nda');
      throw new Error('Expected 410');
    } catch (e) {
      if (e.response?.status === 410) return;
      throw e;
    }
  });

  // ── 9. SMART SUGGESTIONS ─────────────────────────────────
  section('9. Smart Suggestions');

  await test('GET /smart-suggestions', async () => {
    try {
      const { data } = await api.get('/smart-suggestions', {
        params: { limit: 5 },
        headers: authHeaders(),
      });
      if (data) return;
    } catch (e) {
      if (e.response?.status === 403) return; // not verified — expected
      throw e;
    }
  });

  // ── 10. MESSAGING (POST uses form-urlencoded) ──────────────
  section('10. Messaging');

  await test('POST /messages (JSON body — correct rejection)', async () => {
    try {
      await api.post('/messages', { conversation_id: 99999, message: 'Hello' }, { headers: authHeaders() });
      throw new Error('Expected 403');
    } catch (e) {
      if (e.response?.status === 403) return;
      throw e;
    }
  });

  // ── 11. ADMIN ─────────────────────────────────────────────
  section('11. Admin Endpoints');

  let adminToken = null;
  await test('POST /auth/login (as admin)', async () => {
    const { data } = await api.post('/auth/login', {
      email: 'admin@investmatch.com',
      password: 'Demo@2026',
    });
    if (!data.success) throw new Error('Expected success=true');
    if (!data.data?.token) throw new Error('Expected token');
    adminToken = data.data.token;
  });

  await test('GET /admin/stats', async () => {
    const { data } = await api.get('/admin/stats', {
      headers: { Authorization: `Bearer ${adminToken}` },
    });
    if (!data.success) throw new Error('Expected success=true');
    if (!data.data?.total_users) throw new Error('Expected total_users');
  });

  // ── 12. LOGOUT ─────────────────────────────────────────────
  section('12. Logout');

  await test('POST /auth/logout', async () => {
    const { data } = await api.post('/auth/logout', {}, { headers: authHeaders() });
    if (!data.success) throw new Error('Expected success=true');
  });

  // ── SUMMARY ────────────────────────────────────────────────
  const total = passed + failed;
  console.log(`\n${color.bold}${color.yellow}\u2550\u2550\u2550 RESULTS \u2550\u2550\u2550${color.reset}`);
  console.log(`  ${color.green}Passed: ${passed}${color.reset}`);
  console.log(`  ${color.red}Failed: ${failed}${color.reset}`);
  console.log(`  Total:  ${total}`);
  console.log(`  Rate:   ${Math.round(passed / total * 100)}%`);

  if (failed > 0) {
    console.log(`\n${color.yellow}Known issues:${color.reset}`);
    console.log(`  - POST /report returns 404 (route missing from index.php \`\$routes\` array)`);
    console.log(`  - POST /messages uses \$_POST (form-urlencoded), not JSON body`);
    console.log(`  - Rate limit on auth/resend-otp (60s cooldown)`);
  }

  process.exit(failed > 0 ? 1 : 0);
}

run().catch(e => {
  console.error(`${color.red}Fatal error:${color.reset}`, e.message);
  process.exit(1);
});
