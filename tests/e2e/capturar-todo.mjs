import { chromium } from 'playwright';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';
import { existsSync, mkdirSync } from 'fs';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ASSETS = join(__dirname, '..', '..', 'docs', 'manuales', 'assets');
const BASE = 'http://localhost:8000/admin';

const ADMIN = { username: 'superadmin', password: 'Admin@2026!' };
const VIEWPORT = { width: 1366, height: 768 };

if (!existsSync(ASSETS)) mkdirSync(ASSETS, { recursive: true });

let step = 0;
async function shot(page, name, fullPage = true) {
  const path = join(ASSETS, name);
  await page.screenshot({ path, fullPage });
  step++;
  console.log(`  ${step}. ${name}`);
}

async function login(page) {
  await page.goto(`${BASE}/login`, { waitUntil: 'networkidle' });
  await page.waitForTimeout(2000);
  await page.getByLabel('Nombre de Usuario').fill(ADMIN.username);
  await page.getByLabel('Contraseña').fill(ADMIN.password);
  await page.getByRole('button', { name: 'Iniciar Sesión' }).click();
  await page.waitForURL('**/admin', { timeout: 30000 });
  await page.waitForTimeout(2000);
}

async function go(page, url, wait = 3000) {
  await page.goto(url, { waitUntil: 'networkidle' });
  await page.waitForTimeout(wait);
}

async function run() {
  console.log('🚀 Iniciando captura completa de pantallas…\n');
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ viewport: VIEWPORT });
  const page = await context.newPage();

  try {
    // ── LOGIN ──
    console.log('── Acceso al Sistema ──');
    await go(page, `${BASE}/login`);
    await shot(page, 'login.png', false);

    await login(page);

    // ── DASHBOARD ──
    console.log('\n── Dashboard ──');
    await go(page, BASE);
    await shot(page, 'dashboard.png', true);
    await shot(page, 'dashboard-widgets.png', false);

    // ── ACTIVIDADES ──
    console.log('\n── Actividades ──');
    await go(page, `${BASE}/actividades`);
    await shot(page, 'actividades-lista.png', true);

    // Wizard paso 1
    await go(page, `${BASE}/actividades/create`);
    await page.waitForTimeout(1000);
    await shot(page, 'wizard-paso1.png', false);

    // Try to go to step 2 if there's a wizard navigation
    try {
      const nextBtn = page.locator('button:has-text("Siguiente"), button:has-text("Próximo"), button:has-text("Next")');
      if (await nextBtn.count() > 0) {
        await nextBtn.first().click();
        await page.waitForTimeout(1500);
        await shot(page, 'wizard-paso2.png', false);
      }
    } catch {}

    // ── REQUISICIONES ──
    console.log('\n── Requisiciones ──');
    await go(page, `${BASE}/requisiciones`);
    await shot(page, 'requisiciones.png', true);

    // ── TICKETS ──
    console.log('\n── Tickets ──');
    await go(page, `${BASE}/tickets`);
    await shot(page, 'tickets.png', true);

    // ── CONTRATOS ──
    console.log('\n── Contratos ──');
    await go(page, `${BASE}/contratos`);
    await shot(page, 'contratos.png', true);

    // ── CIERRES MENSUALES ──
    console.log('\n── Cierres Mensuales ──');
    await go(page, `${BASE}/cierre-mensuales`);
    await shot(page, 'cierres-mensuales.png', true);

    // ── PERFIL ──
    console.log('\n── Perfil ──');
    try {
      await go(page, `${BASE}/my-profile`);
    } catch {
      await go(page, `${BASE}/profile`);
    }
    await shot(page, 'perfil.png', false);

    // ── USUARIOS (admin) ──
    console.log('\n── Usuarios ──');
    await go(page, `${BASE}/users`);
    await shot(page, 'usuarios-lista.png', true);

    // ── DEPARTAMENTALES (admin) ──
    console.log('\n── Departamentales ──');
    await go(page, `${BASE}/departamentales`);
    await shot(page, 'departamentales.png', true);

    // ── ROLES ──
    console.log('\n── Roles ──');
    await go(page, `${BASE}/shield/roles`);
    await shot(page, 'roles.png', true);

    console.log(`\n✅ ${step} capturas completadas en: ${ASSETS}`);
  } catch (err) {
    console.error('❌ Error:', err.message);
    try { await shot(page, 'error.png', false); } catch {}
    process.exit(1);
  } finally {
    await browser.close();
  }
}

run();
