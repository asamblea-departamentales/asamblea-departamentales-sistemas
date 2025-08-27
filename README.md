# ⚡ Setup rápido

Guía completa para levantar el entorno de desarrollo en Laravel en ≤15 minutos.

---

## ✅ Requisitos previos

Antes de comenzar, asegúrate de tener instalado lo siguiente:

**Requisitos obligatorios:**
- **PHP ≥ 8.1** (verifica con `php -v`)
- **Composer** → https://getcomposer.org/
- **Node.js v18.x** → https://nodejs.org/dist/latest-v18.x/
- **npm** (incluido con Node.js)
- **Git**
- **MySQL o MariaDB**

**Opciones de servidor local (elige uno):**
- XAMPP (Windows/Mac/Linux)
- Laragon (Windows)
- WAMP (Windows) 
- MAMP (Mac)
- MySQL standalone + phpMyAdmin

---

## ℹ️ Introducción breve a Laravel, PHP y Vite

- **PHP** es el lenguaje backend ejecutado por Laravel
- **Laravel** es un framework MVC moderno con rutas, controladores, modelos y vistas
- **Composer** maneja librerías PHP, **npm** las de frontend
- **Vite** es el bundler que Laravel usa para compilar JS y CSS (reemplaza Laravel Mix)

**Estructura común de carpetas:**
- `routes/web.php`: rutas principales
- `app/Http/Controllers`: lógica del backend
- `resources/views`: vistas Blade (HTML)
- `resources/js`, `resources/css`: frontend (Vite)
- `.env`: configuración de entorno, credenciales, base de datos, etc.

---

## 🚀 Instalación

### 1. Clonar el repositorio
```bash
git clone git@github.com:HenryBo6/sandbox-pasantes.git
cd sandbox-pasantes
```

### 2. Configurar archivo de entorno
```bash
cp .env.example .env
php artisan key:generate
```
> ⚠️ **Importante:** Nunca subas el archivo `.env` al repositorio. Usa siempre `.env.example` como plantilla de referencia.

### 3. Instalar dependencias
```bash
# Dependencias de PHP
composer install

# Dependencias de Node.js
npm install
```

### 4. Configurar base de datos

Edita el archivo `.env` con tus credenciales locales:

```dotenv
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sandbox
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Crear base de datos

**Opción A - MySQL desde terminal:**
```bash
mysql -u root -p
CREATE DATABASE sandbox;
exit;
```

**Opción B - phpMyAdmin:**
1. Abrir: `http://localhost/phpmyadmin`
2. Click en "Nueva"
3. Nombre: `sandbox`, luego "Crear"

### 6. Ejecutar migraciones y seeders
```bash
php artisan migrate
php artisan db:seed  # (opcional)
```

---

## 🚀 Ejecución local

### 1. Iniciar Vite (assets)
```bash
npm run dev
```
> 💡 **Importante:** Deja esta terminal abierta durante el desarrollo para que Laravel cargue JS/CSS dinámicamente.

### 2. Iniciar servidor de Laravel
```bash
php artisan serve
```

### 3. Abrir en el navegador
```
http://localhost:8000
```

---

## ☁️ Despliegue a Cloudways desde Git

### Flujo general de despliegue:

#### 1. Conectar repositorio
- Vincula tu repositorio de GitHub en la plataforma Cloudways mediante "Deploy via Git"
- Selecciona la rama a desplegar (normalmente `main` o `master`)

#### 2. Ejecutar despliegue
Cloudways descargará automáticamente el código. Después del deploy, ejecuta los pasos post-despliegue:

```bash
# Instalar dependencias
composer install --optimize-autoloader --no-dev
npm install

# Configurar entorno
cp .env.example .env
# Editar .env con valores de producción
php artisan key:generate

# Base de datos
php artisan migrate --force

# Compilar assets para producción
npm run build

# Optimizar Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### 3. Verificación
- Accede a la URL de la aplicación en Cloudways
- Verifica que el login funcione correctamente
- Si usas Filament, confirma que el panel de administración carga sin errores

> 💡 **Nota:** Ejecuta todos los comandos desde la consola de Cloudways. **Nunca incluyas credenciales sensibles en el repositorio.**

---

## 🛠 Troubleshooting común

### Errores generales

**Autoload roto:**
```bash
composer dump-autoload
```

**Permisos en Linux/macOS:**
```bash
sudo chmod -R 775 storage bootstrap/cache
```

**Verificar versión de Node:**
```bash
node -v
# Si no es v18.x:
npm install -g n
sudo n 18
```

**Assets no cargan:**
```bash
npm run dev
```

### ⚠️ XAMPP y MySQL 8.4.4

#### Problema: Puerto en uso
1. Editar `C:/xampp/mysql/bin/my.ini`
2. Cambiar `port=3306` por `port=3307`
3. En `.env`: `DB_PORT=3307`
4. Reiniciar MySQL desde XAMPP

#### Problema: phpMyAdmin no carga
- Descarga phpMyAdmin standalone desde https://www.phpmyadmin.net/
- Descomprímelo en carpeta separada
- Configura Apache o usa servidor web externo

#### MySQL desde consola:
```bash
cd C:/xampp/mysql/bin
mysql -u root -p
```

---

## 🧪 Comandos adicionales

**Ejecutar pruebas:**
```bash
php artisan test
```

**Compilar para producción:**
```bash
npm run build
```

**Limpiar caché:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## 🔐 Seguridad

- ⚠️ **Nunca subas tu archivo `.env` al repositorio**
- Usa `.env.example` como plantilla de configuración
- No incluyas credenciales reales en commits, issues o pull requests
- Consulta el archivo `SECURITY.md` para más lineamientos

---

## 📸 Evidencia de funcionamiento

Una vez completado el setup, deberías poder ver:

- **Página de bienvenida de Laravel** en `http://localhost:8000`
- **Panel de login** funcionando correctamente
- **Filament admin panel** (si aplica) cargando sin errores
- **Assets compilados** (CSS/JS) mediante Vite

### Capturas requeridas:
- [ ] Pantalla de login del proyecto
- [ ] Dashboard o página principal funcionando
- [ ] Panel de Filament (si aplica)

---

✅ **¡Listo!** Tu entorno Laravel con Vite debería estar funcionando correctamente.  

¿Algo falló? Revisa el `.env`, asegúrate que los puertos estén correctos, y ejecuta los comandos desde terminal.
