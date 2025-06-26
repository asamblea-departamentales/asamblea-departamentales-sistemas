<<<<<<< HEAD
Hello Sprint 0 ST
=======
# ⚡ Setup rápido

Guía completa para levantar el entorno de desarrollo en Laravel en ≤15 minutos.

---

## ✅ Requisitos previos

Antes de comenzar, asegúrate de tener instalado lo siguiente:

- PHP ≥ 8.1
- Composer → https://getcomposer.org/
- Node.js v18.x → https://nodejs.org/dist/latest-v18.x/
- npm (incluido con Node.js)
- Git
- MySQL o MariaDB (puede ser con XAMPP o standalone)
- phpMyAdmin (opcional, para gestionar la base de datos desde el navegador)

---

## ℹ️ Introducción breve a Laravel, PHP y Vite

- **PHP** es el lenguaje backend ejecutado por Laravel.
- **Laravel** es un framework MVC moderno con rutas, controladores, modelos y vistas.
- **Composer** maneja librerías PHP, **npm** las de frontend.
- **Vite** es el bundler que Laravel usa para compilar JS y CSS (reemplaza Laravel Mix).
- Estructura común de carpetas:
  - `routes/web.php`: rutas principales
  - `app/Http/Controllers`: lógica del backend
  - `resources/views`: vistas Blade (HTML)
  - `resources/js`, `resources/css`: frontend (Vite)
  - `.env`: configuración de entorno, credenciales, base de datos, etc.

---

## 🚀 Pasos de instalación

1. Clonar el repositorio
```bash
git clone git@github.com:HenryBo6/sandbox-pasantes.git
cd sandbox-pasantes
```

2. Copiar el archivo de entorno y generar clave
```bash
cp .env.example .env
php artisan key:generate
```

3. Instalar dependencias de PHP
```bash
composer install
```

4. Instalar dependencias de Node
```bash
npm install
```

5. Iniciar Vite en modo desarrollo
```bash
npm run dev
```

> 💡 Deja esta terminal abierta durante el desarrollo para que Laravel cargue JS/CSS dinámicamente con Vite.

6. Configurar conexión a base de datos en `.env`
```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sandbox
DB_USERNAME=root
DB_PASSWORD=
```

7. Crear base de datos manualmente (si no existe)
### Usando MySQL desde terminal:
```bash
mysql -u root -p
# Luego en el prompt de MySQL:
CREATE DATABASE sandbox;
exit;
```

### Usando phpMyAdmin:
1. Abrir en navegador: `http://localhost/phpmyadmin`
2. Click en "Nueva"
3. Nombre: `sandbox`, luego "Crear"

8. Ejecutar migraciones
```bash
php artisan migrate
```

9. (Opcional) Ejecutar seeders
```bash
php artisan db:seed
```

10. Iniciar servidor de Laravel
```bash
php artisan serve
```

11. Abrir en el navegador
```
http://localhost:8000
```

---

## 🛠 Troubleshooting común

### Errores generales

- Autoload roto:
```bash
composer dump-autoload
```

- Permisos en Linux/macOS:
```bash
sudo chmod -R 775 storage bootstrap/cache
```

- Verificar versión de Node:
```bash
node -v
```

- Cambiar a Node 18 si es necesario:
```bash
npm install -g n
sudo n 18
```

- Assets no cargan:
```bash
npm run dev
```

---

### ⚠️ XAMPP y MySQL 8.4.4

#### Problema: Puerto en uso o conflictos
MySQL 8.4.4 puede entrar en conflicto con otros servicios. Para cambiar el puerto:

1. Ir a `C:/xampp/mysql/bin/my.ini`
2. Cambiar:
```
port=3306
```
por
```
port=3307
```

3. En `.env` de Laravel:
```dotenv
DB_PORT=3307
```

4. Reiniciar MySQL desde el panel de XAMPP.

#### Problema: phpMyAdmin no carga o da error

- XAMPP puede bloquear phpMyAdmin si Laravel se ejecuta en otro contexto.
- Solución: descarga phpMyAdmin desde https://www.phpmyadmin.net/
  - Descomprime en una carpeta aparte (ej: `C:/phpmyadmin-standalone`)
  - Correlo desde un servidor web externo o configura Apache para apuntar ahí.

#### Abrir MySQL y administrar base de datos desde consola

```bash
cd C:/xampp/mysql/bin
mysql -u root -p
```

---

## 🧪 Extras

- Ejecutar pruebas de Laravel
```bash
php artisan test
```

- Compilar assets para producción
```bash
npm run build
```

---

✅ ¡Listo! Tu entorno Laravel con Vite debería estar funcionando correctamente.  
¿Algo falló? Revisa el `.env`, asegúrate que los puertos estén bien, y prueba los comandos desde terminal.
>>>>>>> eaf3ff7 (docs: update README)
