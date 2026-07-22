# Guia de Despliegue -- Sistema de Oficinas Departamentales

**IMPORTANTE -- Revision Julio 2026:**
Esta guia ha sido actualizada para resolver:
- Colisiones de puertos con otros sistemas en el servidor (Cambiado de 6001 a 6002).
- Error interno de SSL al emitir notificaciones desde PHP (Resuelto con bypass interno).
- Cierre de sesion (Error 419) al limpiar caches.

**Fecha:** Julio 2026
**Servidor destino:** Servidor Institucional (Asamblea Legislativa)
**Requiere acceso:** SSH al servidor + permisos de sudo

---

## IMPORTANTE -- Ajustar segun el servidor

Los valores de este documento son **ejemplos de referencia**. Cada servidor puede tener:
- Rutas de proyecto diferentes
- Puertos diferentes
- IPs o dominios diferentes
- Llaves diferentes

**Siempre verificar** la configuracion real del servidor antes de aplicar cambios.

---

## Resumen de Cambios

Esta version incluye:
- Correccion de migracion `user_id` (nullable + FK segura)
- Notificacion a TI cuando un usuario solicita cambio de contrasena
- Tipo de ticket dedicado `CAMBIO_CONTRASENA`
- Transiciones de estado validadas en Tickets y Requisiciones
- Aislamiento departamental en badges de Contratos
- Performance: eager loading en todos los resources
- Seguridad: rate limiting en login, validacion de `activo`
- Comentarios en Tickets (tabla `comentarios` con `ticket_id`)
- Pagina 403 personalizada
- Fallback LDAP con mensaje friendly cuando el usuario no existe
- Reverb configurado en puerto 6002 (colision con proyecto Transporte en 6001)
- Queue worker para notificaciones en tiempo real
- Superadmin password configurable via `.env`

---

## Paso 1 -- Pull del codigo

```bash
cd /var/www/asamblea-departamentales-sistemas
git pull origin main
```

**Nota:** Ajustar la ruta si el proyecto esta en otra ubicacion.

---

## Paso 2 -- Dependencias PHP

```bash
composer install --no-dev --optimize-autoloader
```

---

## Paso 3 -- Migraciones

```bash
php artisan migrate --force
```

**Nota:** La migracion `2026_07_21_210000_fix_actividades_user_id_null_on_delete` es robusta:
- Detecta y elimina FK e indice huerfano si existen (usando `dropForeign`).
- Limpia registros con `user_id` invalido
- Cambia la columna a nullable
- Crea la FK con `ON DELETE SET NULL`

Si falla, revisar el log de errores y ejecutar manualmente los pasos del SQL que aparece en el error.

**Nota nueva:** La migracion `2026_07_22_150000_add_ticket_id_to_comentarios_table` agrega soporte de comentarios a Tickets. Es idempotente (puede re-ejecutarse sin problemas).

**Nota nueva:** La migracion `2026_07_22_160000_make_password_nullable_in_users_table` hace que la columna `password` sea nullable (necesario para usuarios LDAP que no tienen password local).

---

## Paso 4 -- Limpiar caches

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan cache:clear
```

**ADVERTENCIA:** Si `cache:clear` da error de permisos, ejecutar:
```bash
sudo chown -R asamblea:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## Paso 5 -- Storage link

```bash
php artisan storage:link
```

Si ya existe, ignorar.

---

## Paso 6 -- Configurar Variables de Entorno (.env)

Editar el archivo `.env` del servidor con las siguientes lineas.

**ADVERTENCIA clave para que funcionen las notificaciones:**
PHP (Laravel) y el Navegador (Vite) **NO** pueden usar la misma URL. Laravel debe enviar la notificacion a traves de la red interna (127.0.0.1 HTTP) para evitar que el certificado SSL auto-firmado bloquee la peticion interna. El navegador si debe conectarse por la red externa (HTTPS).

```env
# ══════════════════════════════════════════════════════════════
# BROADCASTING
# ══════════════════════════════════════════════════════════════
BROADCAST_CONNECTION=reverb

# ══════════════════════════════════════════════════════════════
# LARAVEL REVERB - CONFIGURACION DEL BACKEND (RUTA INTERNA HTTP)
# ══════════════════════════════════════════════════════════════
REVERB_APP_ID=999999
REVERB_APP_KEY=asambleaLlaveWebSocket2026
REVERB_APP_SECRET=asambleaLlaveWebSocket2026
# Laravel se conecta directo al Daemon internamente sin pasar por Nginx
REVERB_HOST=127.0.0.1
REVERB_PORT=6002
REVERB_SCHEME=http
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=6002
REVERB_ALLOWED_ORIGINS=*

# ══════════════════════════════════════════════════════════════
# LARAVEL ECHO - CONFIGURACION DEL FRONTEND (RUTA EXTERNA HTTPS)
# ══════════════════════════════════════════════════════════════
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST=172.19.20.41
VITE_REVERB_PORT=6443
VITE_REVERB_SCHEME=https

# ══════════════════════════════════════════════════════════════
# COLA DE TRABAJO (QUEUE)
# ══════════════════════════════════════════════════════════════
# IMPORTANTE: Debe ser 'database' para que las notificaciones funcionen.
# Requiere un worker corriendo (ver Paso 8 - Crontab).
QUEUE_CONNECTION=database

# ══════════════════════════════════════════════════════════════
# SUPERADMIN - Password para el seeder inicial
# ══════════════════════════════════════════════════════════════
# [IMPORTANTE]: Definir un password seguro de al menos 12 caracteres.
# Este password se usa para crear el usuario superadmin en el seeder.
SUPERADMIN_PASSWORD=CHANGE_THIS_TO_A_SECURE_PASSWORD
```

**IMPORTANTE:**
- Las llaves `REVERB_APP_KEY` y `VITE_REVERB_APP_KEY` son strings planos y aleatorios.
- **NO** anteponer `base64:` -- eso rompe las conexiones del frontend.
- Generar llaves con: `php artisan reverb:generate-key`
- Usar el **mismo valor** en `REVERB_APP_KEY`, `REVERB_APP_SECRET` y `VITE_REVERB_APP_KEY`.
- Si `BROADCAST_DRIVER` existe en el `.env`, **eliminarlo**. Solo usar `BROADCAST_CONNECTION`.
- Si el servidor tiene otro proyecto usando el puerto 6001, usar un puerto diferente (ej: 6002). Ajustar `REVERB_SERVER_PORT` y el proxy Nginx en consecuencia.

---

## Paso 7 -- Daemon de Reverb

### 7.1 Verificar que el archivo existe
```bash
ls -la reverb_daemon.sh
```

### 7.2 Editar puerto en el script
```bash
nano reverb_daemon.sh
```

Asegurarse de que el script levante el puerto **6002**:
```bash
php artisan reverb:start --host=0.0.0.0 --port=6002 > storage/logs/reverb.log 2>&1 &
```

Verificar que `APP_DIR=` apunte a la ruta real del proyecto en el servidor.

### 7.3 Dar permisos
```bash
chmod +x reverb_daemon.sh
```

---

## Paso 8 -- Crontab

```bash
crontab -e
```

Agregar al final del archivo:

```bash
# ──────────────────────────────────────────────────────────────
# SISTEMA DE OFICINAS DEPARTAMENTALES
# ──────────────────────────────────────────────────────────────

# Scheduler de Laravel (ejecuta tareas programadas cada minuto)
* * * * * cd /var/www/asamblea-departamentales-sistemas && php artisan schedule:run >> /dev/null 2>&1

# Worker de colas para notificaciones (cada minuto, se detiene cuando no hay jobs)
* * * * * cd /var/www/asamblea-departamentales-sistemas && php artisan queue:work --queue=notifications --timeout=30 --tries=3 --stop-when-empty >> /dev/null 2>&1

# Daemon de Reverb (verifica que este corriendo cada minuto)
* * * * * bash /var/www/asamblea-departamentales-sistemas/reverb_daemon.sh
```

**Nota:** Ajustar la ruta si el proyecto esta en otra ubicacion.

**Detalle de cada linea:**
- `schedule:run` -- ejecuta los comandos programados (actividades:reminders, departamentales:check).
- `queue:work --queue=notifications` -- procesa las notificaciones de la tabla `jobs`. `--stop-when-empty` evita procesos zombie.
- `reverb_daemon.sh` -- levanta Reverb si se cayo.

**Verificar que se guardo:**
```bash
crontab -l
```

---

## Paso 9 -- Nginx: Proxy WebSocket

### 9.1 Editar configuracion del sitio
```bash
sudo nano /etc/nginx/sites-available/asamblea-departamentales
```

**Nota:** Ajustar el nombre del archivo segun la configuracion del servidor.

### 9.2 Agregar dentro del bloque `server { listen 6443 ssl; ... }`:

```nginx
# REDIRECCION FRONTEND DE WEBSOCKETS HACIA LARAVEL REVERB (PUERTO 6002)
location /app/ {
    proxy_pass http://127.0.0.1:6002;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_read_timeout 86400;
}
```

**Nota:** El puerto en `proxy_pass` debe coincidir con `REVERB_SERVER_PORT` en `.env`. Todos los valores deben coincidir: `.env`, `reverb_daemon.sh`, y este proxy.

### 9.3 Validar y reiniciar Nginx
```bash
sudo nginx -t
sudo systemctl reload nginx
```

---

## Paso 10 -- Verificacion

### 10.1 Verificar que Reverb esta corriendo en 6002
```bash
ps aux | grep reverb
```
Debe aparecer un proceso `php artisan reverb:start` con el puerto 6002.

### 10.2 Verificar que el daemon funciona
```bash
# Matar el proceso manualmente para probar el daemon
kill $(cat storage/logs/reverb.pid)

# Esperar 60 segundos (el cron corre cada minuto)
sleep 65

# Verificar que se levanto solo
ps aux | grep reverb
```

### 10.3 Verificar la conexion WebSocket
Abrir el navegador en la URL del sistema y en la consola (F12) buscar errores de WebSocket. No debe haber errores rojos de "Connection failed".

### 10.4 Verificar que las migraciones corrieron
```bash
php artisan migrate:status
```
Debe mostrar `Yes` en la columna `Ran` para todas las migraciones.

### 10.5 Verificar que la cola funciona
```bash
# Verificar que la tabla notifications existe
php artisan tinker --execute="echo \Illuminate\Support\Facades\Schema::hasTable('notifications') ? 'OK' : 'FALTA';"

# Verificar que la tabla jobs existe (si queue=database)
php artisan tinker --execute="echo \Illuminate\Support\Facades\Schema::hasTable('jobs') ? 'OK' : 'FALTA';"

# Probar envio de recordatorios (dry run)
php artisan actividades:reminders --dry-run

# Probar envio real
php artisan actividades:reminders
```

### 10.6 Verificar que el worker de colas esta activo
```bash
ps aux | grep queue:work
```
Debe aparecer un proceso `php artisan queue:work --queue=notifications`.

---

## Troubleshooting

### Error: "Foreign key constraint is incorrectly formed"
La migracion anterior fallo. Ejecutar manualmente:
```sql
-- Verificar el estado actual
SHOW CREATE TABLE actividades;

-- Si hay un KEY huerfano sin CONSTRAINT:
ALTER TABLE actividades DROP INDEX actividades_user_id_foreign;

-- Luego re-ejecutar la migracion
php artisan migrate
```

### Error: "Duplicate foreign key constraint name"
La migracion ya corrio antes. Ejecutar:
```sql
-- Verificar si la FK ya existe
SELECT * FROM information_schema.TABLE_CONSTRAINTS
WHERE TABLE_NAME = 'actividades'
AND CONSTRAINT_NAME = 'actividades_user_id_foreign';

-- Si existe, la migracion ya esta aplicada. No hacer nada.
```

### Error EADDRINUSE: puerto en uso
Otro proyecto ya usa el puerto configurado para Reverb.
Solucion: usar un puerto diferente (ej: 6002) en:
1. `REVERB_SERVER_PORT` en `.env`
2. `reverb_daemon.sh` (`--port=...`)
3. Nginx (`proxy_pass http://127.0.0.1:6002`)
**Todos deben coincidir.**

```bash
killall php
# Y dejar que el Crontab lo vuelva a levantar
```

### Reverb no se conecta desde el navegador
1. Verificar que el proxy Nginx esta configurado (Paso 9)
2. Verificar que el puerto en Nginx coincide con `REVERB_SERVER_PORT`
3. Verificar que el firewall del servidor permite conexiones entrantes en puerto 443 o 6443
4. Verificar logs de Reverb: `tail -f storage/logs/reverb.log`

### El daemon no levanta Reverb
1. Verificar permisos: `ls -la reverb_daemon.sh` (debe tener `x`)
2. Verificar el log: `tail -f storage/logs/reverb.log`
3. Verificar que PHP esta en el PATH del cron: usar ruta completa a PHP si es necesario
   ```
   * * * * * /usr/bin/php /var/www/asamblea-departamentales-sistemas/artisan reverb:start --host=0.0.0.0 --port=6002
   ```
4. Verificar que `APP_DIR` en el daemon apunta a la ruta correcta

### Usuario LDAP no puede acceder al panel
1. Verificar que el usuario fue creado manualmente por TI en el panel
2. Verificar que el usuario tiene un rol asignado (super_admin, ti, gol, coordinador, asistente_tecnico, auditoria)
3. Verificar que `activo` esta en `true`
4. Verificar que el username en la DB coincide con `samaccountname` en Active Directory
5. Verificar logs de LDAP: `tail -f storage/logs/laravel.log | grep ldap`

### Error "Aun no tienes acceso al sistema"
El usuario intento login con credenciales LDAP validas pero no existe en la DB.
Solucion: crear el usuario manualmente desde el panel de administracion (Users > Create) con el mismo username que tiene en Active Directory.

### Las notificaciones no aparecen en la campanita
1. Verificar que `QUEUE_CONNECTION=database` en `.env`
2. Verificar que el worker de colas esta corriendo: `ps aux | grep queue:work`
3. Verificar la tabla jobs: `php artisan tinker --execute="echo \DB::table('jobs')->count();"`
4. Si hay jobs acumulados, procesarlos: `php artisan queue:work --queue=notifications --once`
5. Verificar la tabla notifications: `php artisan tinker --execute="echo \DB::table('notifications')->count();"`
6. Verificar logs: `tail -f storage/logs/laravel.log | grep -i notification`

### Error 419 al limpiar caches
Si en la consola del navegador dice Error 419, significa que la sesion caduco al borrar la caché de Laravel.
**Solucion:** Presionar `Ctrl + F5` y volver a iniciar sesion en el dashboard.

### Los tickets de cambio de contrasena no llegan a TI
1. Verificar que la tabla `notifications` existe: `php artisan tinker --execute="Schema::hasTable('notifications')"`
2. Verificar que los usuarios TI tienen el rol correcto: `php artisan tinker --execute="App\Models\User::whereHas('roles', fn($q) => $q->where('name','ti'))->count()"`
3. Verificar que el worker de colas esta corriendo (arriba)
4. Las notificaciones aparecen en el icono de campana del panel de Filament

---

## Comandos de Emergencia

```bash
# Reiniciar Reverb manualmente (en 6002)
killall php
php artisan reverb:start --host=0.0.0.0 --port=6002 >> storage/logs/reverb.log 2>&1 &

# Reiniciar worker de colas
php artisan queue:restart

# Forzar procesamiento de un solo job
php artisan queue:work --queue=notifications --once

# Ver logs de Reverb en tiempo real
tail -f storage/logs/reverb.log

# Ver errores recientes de Laravel
tail -f storage/logs/laravel.log

# Limpiar todo si las notificaciones fallan
php artisan config:clear && php artisan cache:clear
php artisan config:cache
```
