import { chromium } from 'playwright';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';
import { writeFileSync, existsSync, mkdirSync } from 'fs';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ASSETS = join(__dirname, '..', '..', 'docs', 'manuales', 'assets');
const BASE = 'http://localhost:8000/admin';

const CREDENTIALS = {
  username: 'superadmin',
  password: 'Admin@2026!',
};

const VIEWPORT = { width: 1366, height: 768 };

if (!existsSync(ASSETS)) mkdirSync(ASSETS, { recursive: true });

async function capture(page, name, fullPage = true) {
  const path = join(ASSETS, name);
  await page.screenshot({ path, fullPage });
  console.log(`  ✔ ${name}`);
}

async function login(page) {
  await page.goto(`${BASE}/login`, { waitUntil: 'networkidle' });
  await page.waitForTimeout(2000);
  // Find inputs by label text (Filament uses label-adjacent inputs)
  await page.getByLabel('Nombre de Usuario').fill(CREDENTIALS.username);
  await page.getByLabel('Contraseña').fill(CREDENTIALS.password);
  await page.getByRole('button', { name: 'Iniciar Sesión' }).click();
  await page.waitForURL('**/admin', { timeout: 30000 });
  await page.waitForTimeout(2000);
}

// ──────────────────────────────────────────
async function run() {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ viewport: VIEWPORT });
  const page = await context.newPage();

  try {
    // 1. Login page + login
    console.log('1. Login page');
    await page.goto(`${BASE}/login`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(3000);
    await capture(page, 'login.png', false);

    console.log('   Iniciando sesión…');
    await login(page);

    // 2. Dashboard
    console.log('2. Dashboard');
    await page.goto(BASE, { waitUntil: 'networkidle' });
    await page.waitForTimeout(2000);
    await capture(page, 'dashboard.png', true);
    await capture(page, 'dashboard-widgets.png', false);

    // 3. Activity list
    console.log('3. Actividades');
    await page.goto(`${BASE}/actividades`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(2000);
    // Apply filters or show the table
    await capture(page, 'actividades-lista.png', true);

    // 4. Activity wizard (this was missing before)
    console.log('4. Wizard de actividad');
    await page.goto(`${BASE}/actividades/create`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(2000);
    await capture(page, 'actividad-wizard.png', false);

    // 5. Requisitions
    console.log('5. Requisiciones');
    await page.goto(`${BASE}/requisiciones`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(2000);
    await capture(page, 'requisiciones.png', true);

    // 6. Tickets
    console.log('6. Tickets');
    await page.goto(`${BASE}/tickets`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(2000);
    await capture(page, 'tickets.png', true);

    // 7. Contracts
    console.log('7. Contratos');
    await page.goto(`${BASE}/contratos`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(2000);
    await capture(page, 'contratos.png', true);

    // 8. Monthly closures
    console.log('8. Cierres mensuales');
    await page.goto(`${BASE}/cierre-mensuales`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(2000);
    await capture(page, 'cierre-mensuales.png', true);

    // 9. User profile
    console.log('9. Perfil de usuario');
    try {
      await page.goto(`${BASE}/my-profile`, { waitUntil: 'networkidle' });
      await page.waitForTimeout(2000);
    } catch {
      // Try alternative route
      await page.goto(`${BASE}/profile`, { waitUntil: 'networkidle' });
      await page.waitForTimeout(2000);
    }
    await capture(page, 'perfil.png', false);

    // 10. User management
    console.log('10. Usuarios');
    await page.goto(`${BASE}/users`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(2000);
    await capture(page, 'usuarios-lista.png', true);

    console.log('\n✅ Todas las capturas completadas en:', ASSETS);

  } catch (err) {
    console.error('❌ Error:', err.message);
    try {
      await capture(page, 'error.png', false);
      console.log('   Pantalla de error guardada');
    } catch {}
    process.exit(1);
  } finally {
    await browser.close();
  }
}

run();
