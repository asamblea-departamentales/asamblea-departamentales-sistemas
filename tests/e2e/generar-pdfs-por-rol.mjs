import { chromium } from 'playwright';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';
import { readFileSync, writeFileSync, statSync, existsSync, mkdirSync, readdirSync } from 'fs';

const __dirname = dirname(fileURLToPath(import.meta.url));
const MANUALES = join(__dirname, '..', '..', 'docs', 'manuales');
const ASSETS = join(MANUALES, 'assets');
const PDF_DIR = join(MANUALES, 'pdf');

const ROLES = {
  super_admin: { label: 'Super Admin', desc: 'Acceso total al sistema' },
  coordinador: { label: 'Coordinador', desc: 'Gestión de su oficina. Aprueba solicitudes departamentales.' },
  asistente_tecnico: { label: 'Usuario Final', desc: 'Crea solicitudes, tickets y actividades. Operativo.' },
};

const ALL_MANUALS = [
  { file: 'manual-usuario-final.html', title: 'Manual de Usuario Final', key: 'manual-usuario-final.html' },
  { file: 'manual-administracion.html', title: 'Manual de Administración', key: 'manual-administracion.html' },
  { file: 'manual-reportes.html', title: 'Manual de Reportes y Estadísticas', key: 'manual-reportes.html' },
  { file: 'manual-referencia-rapida.html', title: 'Referencia Rápida', key: 'manual-referencia-rapida.html' },
  { file: 'glosario.html', title: 'Glosario', key: 'glosario.html' },
];

const roleManuals = {
  super_admin: ALL_MANUALS,
  coordinador: [
    { file: 'manual-usuario-final.html', title: 'Manual de Usuario Final', key: 'manual-usuario-final.html' },
    { file: 'manual-reportes.html', title: 'Manual de Reportes y Estadísticas', key: 'manual-reportes.html' },
    { file: 'manual-referencia-rapida.html', title: 'Referencia Rápida', key: 'manual-referencia-rapida.html' },
  ],
  asistente_tecnico: [
    { file: 'manual-usuario-final.html', title: 'Manual de Usuario Final', key: 'manual-usuario-final.html' },
    { file: 'manual-reportes.html', title: 'Manual de Reportes y Estadísticas', key: 'manual-reportes.html' },
    { file: 'manual-referencia-rapida.html', title: 'Referencia Rápida', key: 'manual-referencia-rapida.html' },
  ],
};

const sections = {
  'manual-usuario-final.html': {
    'Control de documento': ['super_admin'],
    'Objetivo del Manual': ['super_admin','coordinador','asistente_tecnico'],
    'Requisitos Técnicos': ['super_admin','coordinador','asistente_tecnico'],
    'Acceso al Sistema': [],
    'Descripción General': ['super_admin','coordinador','asistente_tecnico'],
    'Panel Principal (Dashboard)': ['super_admin','coordinador','asistente_tecnico'],
    'Gestión de Actividades': ['super_admin','coordinador','asistente_tecnico'],
    'Requisiciones (Solicitud de Insumos)': ['super_admin','coordinador','asistente_tecnico'],
    'Tickets (Reportes de Soporte)': ['super_admin','coordinador','asistente_tecnico'],
    'Contratos': ['super_admin','coordinador'],
    'Cierres Mensuales': ['super_admin','coordinador'],
    'Notificaciones': ['super_admin','coordinador','asistente_tecnico'],
    'Perfil de Usuario': ['super_admin','coordinador','asistente_tecnico'],
    'Administración de Departamentales': ['super_admin'],
    'Administración de Usuarios': ['super_admin'],
    'Roles y Permisos': ['super_admin'],
    'Configuración del Sistema': ['super_admin'],
    'Reportes Power BI': ['super_admin','coordinador','asistente_tecnico'],
    'Preguntas Frecuentes': ['super_admin','coordinador','asistente_tecnico'],
    'Solución de Problemas': ['super_admin','coordinador','asistente_tecnico'],
    'Glosario': ['super_admin','coordinador','asistente_tecnico'],
    'Conclusión': ['super_admin','coordinador','asistente_tecnico'],
  },
  'manual-administracion.html': {
    'Control de documento': ['super_admin'],
    'Gestión de Departamentales': ['super_admin'],
    'Gestión de Usuarios': ['super_admin'],
    'Roles y Permisos (Filament Shield)': ['super_admin'],
    'Cierre Mensual': ['super_admin'],
    'Configuración del Sistema': ['super_admin'],
    'Seguridad': ['super_admin'],
    'Preguntas Frecuentes': ['super_admin'],
  },
  'manual-reportes.html': {
    'Control de documento': ['super_admin'],
    'Dashboard de Actividades': ['super_admin','coordinador','asistente_tecnico'],
    'Cierres Mensuales': ['super_admin','coordinador'],
    'Exportaciones': ['super_admin','coordinador','asistente_tecnico'],
    'Visualizaciones del Dashboard': ['super_admin','coordinador','asistente_tecnico'],
    'Notificaciones y Alertas': ['super_admin','coordinador','asistente_tecnico'],
    'Buenas Prácticas': ['super_admin','coordinador','asistente_tecnico'],
    'Preguntas Frecuentes': ['super_admin','coordinador','asistente_tecnico'],
  },
  'manual-referencia-rapida.html': {
    'Control de documento': ['super_admin'],
    'Acceso al Sistema': [],
    'Actividades': ['super_admin','coordinador','asistente_tecnico'],
    'Requisiciones': ['super_admin','coordinador','asistente_tecnico'],
    'Tickets': ['super_admin','coordinador','asistente_tecnico'],
    'Contratos': ['super_admin','coordinador'],
    'Roles del Sistema': ['super_admin'],
    'Cierres Mensuales': ['super_admin','coordinador'],
    'Exportación de Datos': ['super_admin','coordinador','asistente_tecnico'],
  },
  'glosario.html': {},
};

if (!existsSync(PDF_DIR)) mkdirSync(PDF_DIR, { recursive: true });

function assetAbs(path) {
  return `file:///${ASSETS.replace(/\\/g, '/')}/${path}`;
}

function coverPage(roleInfo) {
  return `<div class="cover-page" style="
    display:flex;flex-direction:column;justify-content:center;align-items:center;
    min-height:90vh;text-align:center;page-break-after:always;break-after:page;
    padding:2rem;">
    <img src="${assetAbs('logo-asamblea.png')}" alt="Logo" style="height:100px;margin-bottom:2rem;">
    <h1 style="font-size:2.2rem;color:#0a2c65;margin:0 0 1rem 0;font-family:Georgia,serif;">
      Sistema de Gestión de<br>Oficinas Departamentales
    </h1>
    <h2 style="font-size:1.6rem;color:#c8a950;margin:0 0 1.5rem 0;font-family:Georgia,serif;">
      ${roleInfo.label}
    </h2>
    <p style="font-size:1rem;color:#666;max-width:400px;margin:0 0 2rem 0;">${roleInfo.desc}</p>
    <p style="font-size:0.9rem;color:#999;">Versión 1.0 — Julio 2026</p>
  </div>`;
}

function extractBody(file) {
  const html = readFileSync(join(MANUALES, file), 'utf-8');
  const startTag = '<div class="page">';
  const startIdx = html.indexOf(startTag);
  if (startIdx === -1) return { content: '<p>Error</p>', style: '' };

  let depth = 0, endIdx = startIdx + startTag.length;
  const re = /<\/?div[\s>]/gi;
  re.lastIndex = endIdx;
  let match;
  while ((match = re.exec(html)) !== null) {
    if (html[match.index + 1] === '/') depth--;
    else depth++;
    if (depth < 0) { endIdx = match.index; break; }
  }

  let content = html.slice(startIdx + startTag.length, endIdx);
  content = content.replace(/<script[\s\S]*?<\/script>/gi, '');
  content = content.replace(/<a href="#" class="back-top[\s\S]*?<\/a>/gi, '');
  content = content.replace(/<footer class="doc-footer">[\s\S]*?<\/footer>/gi, '');
  content = content.replace(/<div class="print-footer">[\s\S]*?<\/div>/gi, '');
  content = content.replace(/class="no-print"/gi, 'class="no-print" style="display:none"');
  content = content.replace(/src="assets\//g, `src="file:///${ASSETS.replace(/\\/g, '/')}/`);

  const styleMatch = html.match(/<style>([\s\S]*?)<\/style>/);
  const style = styleMatch ? styleMatch[1] : '';
  return { content, style };
}

function getHeadingText(h2) {
  const rest = h2.slice(h2.indexOf('>') + 1);
  return rest.replace(/<[^>]+>/g, '').trim();
}

function filterSections(content, role, secMap) {
  if (role === 'super_admin' || !secMap) return content;
  const parts = content.split(/(<h2[^>]*>.*?<\/h2>)/);
  const keep = [''];
  for (let i = 1; i < parts.length; i += 2) {
    const h2 = parts[i];
    const body = parts[i + 1] || '';
    const heading = getHeadingText(h2);
    const allowed = secMap[heading];
    if (allowed && allowed.includes(role)) keep.push(h2, body);
  }
  return keep.join('');
}

function buildRolePDF(role) {
  const roleInfo = ROLES[role];
  const label = roleInfo.label.normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/\s+/g, '-').toLowerCase();
  console.log(`\n📄 ${roleInfo.label}…`);

  const allStyle = [];
  const allContent = [coverPage(roleInfo)];
  const manuals = roleManuals[role] || [];

  manuals.forEach((m, idx) => {
    const { content, style } = extractBody(m.file);
    const secMap = m.key ? sections[m.key] : null;
    let filtered = filterSections(content, role, secMap);
    if (!filtered.trim()) return;
    if (idx > 0 || allContent.length > 1) {
      allContent.push(`<h2 style="text-align:center;color:#0a2c65;font-family:Georgia,serif;margin:2rem 0;page-break-before:always;">— ${m.title} —</h2>`);
    }
    allContent.push(filtered);
    if (style) allStyle.push(style);
  });

  if (allContent.join('').trim().length < 100) return null;

  const html = `<!DOCTYPE html>
<html lang="es"><head>
<meta charset="UTF-8">
<title>Manual ${roleInfo.label}</title>
<style>
${readFileSync(join(MANUALES, 'styles.css'), 'utf-8')}
${allStyle.join('\n')}
.img-wrap { page-break-inside:avoid; break-inside:avoid; max-width:70%; margin:0 auto; }
.img-wrap img { max-width:100% !important; height:auto !important; display:block; }
.img-caption { text-align:center; font-size:0.8rem; color:#666; margin-top:0.3rem; }
.wiki-sidebar, .sidebar-toggle-btn, .sidebar-overlay, .no-print { display:none !important; }
.wiki-content { margin-left:0 !important; }
.wiki-content .page { padding:0 !important; max-width:100%; box-shadow:none; }
.cover { min-height:60vh !important; height:auto !important; }
</style>
</head><body>
<div class="wiki-wrapper"><main class="wiki-content">
<div class="page">
${allContent.join('\n')}
</div>
</main></div>
</body></html>`;

  const tmpFile = join(PDF_DIR, `temp-${role}.html`);
  writeFileSync(tmpFile, html, 'utf-8');
  return tmpFile;
}

async function generatePDF(htmlFile, role, pdfName) {
  const roleInfo = ROLES[role];
  const label = pdfName || (roleInfo.label.normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/\s+/g, '-').toLowerCase());
  const pdfPath = join(PDF_DIR, `manual-${label}.pdf`);

  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1024, height: 768 } });

  try {
    const url = 'file:///' + htmlFile.replace(/\\/g, '/');
    await page.goto(url, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForTimeout(3000);

    await page.pdf({
      path: pdfPath,
      format: 'Letter',
      margin: { top: '2cm', bottom: '2.5cm', left: '2cm', right: '2cm' },
      printBackground: true,
      displayHeaderFooter: true,
      headerTemplate: '<div></div>',
      footerTemplate: `<div style="font-size:8px;text-align:center;width:100%;color:#666;border-top:0.5pt solid #bbb;padding-top:3px;">
        ${roleInfo.label} — Sistema de Gestión de Oficinas Departamentales — Pág. <span class="pageNumber"/>
      </div>`,
    });

    const size = (statSync(pdfPath).size / 1024).toFixed(0);
    console.log(`   ✅ ${pdfPath} — ${size} KB`);
  } finally {
    await browser.close();
    try { writeFileSync(htmlFile, ''); } catch {}
  }
}

function buildCompletePDF() {
  console.log(`\n📄 Manual Completo (wiki completa)…`);
  const allStyle = [];
  const allContent = [coverPage({ label: 'Manual Completo', desc: 'Wiki completa del Sistema de Gestión de Oficinas Departamentales' })];

  ALL_MANUALS.forEach((m) => {
    const { content, style } = extractBody(m.file);
    if (!content.trim()) return;
    allContent.push(`<h2 style="text-align:center;color:#0a2c65;font-family:Georgia,serif;margin:2rem 0;page-break-before:always;">— ${m.title} —</h2>`);
    allContent.push(content);
    if (style) allStyle.push(style);
  });

  const html = `<!DOCTYPE html>
<html lang="es"><head>
<meta charset="UTF-8">
<title>Manual Completo — Sistema de Gestión de Oficinas Departamentales</title>
<style>
${readFileSync(join(MANUALES, 'styles.css'), 'utf-8')}
${allStyle.join('\n')}
.img-wrap { page-break-inside:avoid; break-inside:avoid; max-width:70%; margin:0 auto; }
.img-wrap img { max-width:100% !important; height:auto !important; display:block; }
.img-caption { text-align:center; font-size:0.8rem; color:#666; margin-top:0.3rem; }
.wiki-sidebar, .sidebar-toggle-btn, .sidebar-overlay, .no-print { display:none !important; }
.wiki-content { margin-left:0 !important; }
.wiki-content .page { padding:0 !important; max-width:100%; box-shadow:none; }
.cover { min-height:60vh !important; height:auto !important; }
</style>
</head><body>
<div class="wiki-wrapper"><main class="wiki-content">
<div class="page">
${allContent.join('\n')}
</div>
</main></div>
</body></html>`;

  const tmpFile = join(PDF_DIR, 'temp-completo.html');
  writeFileSync(tmpFile, html, 'utf-8');
  return tmpFile;
}

async function run() {
  console.log('📚 Generando PDFs…\n');

  const tmpFile2 = buildCompletePDF();
  if (tmpFile2) await generatePDF(tmpFile2, 'super_admin', 'completo');

  for (const role of Object.keys(ROLES)) {
    const tmpFile = buildRolePDF(role);
    if (tmpFile) await generatePDF(tmpFile, role);
  }

  readdirSync(PDF_DIR).filter(f => f.startsWith('temp-')).forEach(f => {
    try { writeFileSync(join(PDF_DIR, f), ''); } catch {}
  });

  console.log('\n✅ PDFs generados:');
  readdirSync(PDF_DIR).filter(f => f.endsWith('.pdf')).forEach(f => {
    const s = (statSync(join(PDF_DIR, f)).size / 1024).toFixed(0);
    console.log(`   📕 ${f} (${s} KB)`);
  });
}

run();
