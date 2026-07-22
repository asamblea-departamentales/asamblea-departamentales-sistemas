# Guia de Despliegue — Sistema de Oficinas Departamentales

**Fecha:** Julio 2026  
**Servidor destino:** Servidor Institucional (Asamblea Legislativa)  
**Requiere acceso:** SSH al servidor + permisos de sudo

---

## Resumen de Cambios

Estados incluye:
- Correccion de migracion `user_id` (nullable + FK segura)
- Notificacion a TI cuando un usuario solicita cambio de contrasena
- Tipo de ticket dedicado `CAMBIO_CONTRASENA`
- Transiciones de estado validadas en Tickets y Requisiciones
- Aislamiento departamental en badges de Contratos
- Performance: eager loading en todos los resources
- Seguridad: rate limiting en login, validacion de `activo`

---

## Paso 1 — Pull del codigo

```bash
cd /var/www/html/sistema-oficinas-departamentales
git pull origin main
```

---

## Paso 2 — Dependencias PHP

```bash
composer install --no-dev --optimize-autoloader
```

---

## Paso 3 — Migraciones

```bash
php artisan migrate --force
```

**Nota:** La migracion `2026_07_21_210000_fix_actividades_user_id_null_on_delete` es robusta:
- Detecta y elimina FK e indice huérfano si existen
- Limpia registros con `user_id` invalido
- Cambia la columna a nullable
- Crea la FK con `ON DELETE SET NULL`

Si falla, revisar el log de errores y ejecutar manualmente los pasos del SQL que aparece en el error.

---

## Paso 4 — Limpiar caches

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan cache:clear
```

---

## Paso 5 — Storage link

```bash
php artisan storage:link (por si no existe)
```

Si ya existe, ignorar.

---

## Paso 6 — Configurar Variables de Entorno (.env)

Editar el archivo `.env` del servidor con las siguientes lineas. **No copiar ciegamente** — ajustar los valores de llaves segun el servidor.

```env
# ══════════════════════════════════════════════════════════════
# BROADCASTING
# ══════════════════════════════════════════════════════════════
BROADCAST_CONNECTION=reverb

# ══════════════════════════════════════════════════════════════
# LARAVEL REVERB - CONFIGURACION DEL BACKEND
# ══════════════════════════════════════════════════════════════
REVERB_APP_ID=999999
REVERB_APP_KEY=Sz0OSmOKPYaCmLYNH3wpjWQ66I4WV9wkyQTu3igBeA
REVERB_APP_SECRET=Sz0OSmOKPYaCmLYNH3wpjWQ66I4WV9wkyQTu3igBeA
REVERB_HOST=departamentales.asamblea.gob.sv
REVERB_PORT=443
REVERB_SCHEME=https
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=6001
REVERB_ALLOWED_ORIGINS=https://departamentales.asamblea.gob.sv

# ══════════════════════════════════════════════════════════════
# LARAVEL ECHO - CONFIGURACION DEL FRONTEND (VITE)
# ══════════════════════════════════════════════════════════════
VITE_REVERB_APP_KEY=Sz0OSmOKPYaCmLYNH3wpjWQ66I4WV9wkyQTu3igBeA
VITE_REVERB_HOST=departamentales.asamblea.gob.sv
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https

```

**IMPORTANTE:**
- Las llaves `REVERB_APP_KEY` y `VITE_REVERB_APP_KEY` son strings planos y aleatorios.
- **NO** anteponer `base64:` — eso rompe las conexiones del frontend.
- Generar llaves con: `php artisan reverb:generate-key`
- Usar el **mismo valor** en `REVERB_APP_KEY`, `REVERB_APP_SECRET` y `VITE_REVERB_APP_KEY`.
- Si `BROADCAST_DRIVER` existe en el `.env`, **eliminarlo**. Solo usar `BROADCAST_CONNECTION`.

---

## Paso 7 — Daemon de Reverb

### 7.1 Verificar que el archivo existe
```bash
ls -la reverb_daemon.sh
```

### 7.2 Editar APP_DIR si es necesario
```bash
nano reverb_daemon.sh
```
Buscar la linea `APP_DIR=` y ajustarla a la ruta real del proyecto.

### 7.3 Dar permisos
```bash
chmod +x reverb_daemon.sh
```

---

## Paso 8 — Crontab

```bash
crontab -e
```

Agregar al final del archivo:
```
* * * * * bash /var/www/html/sistema-oficinas-departamentales/reverb_daemon.sh
```

**Verificar que se guardo:**
```bash
crontab -l
```

---

## Paso 9 — Nginx: Proxy WebSocket

### 9.1 Editar configuracion del sitio
```bash
sudo nano /etc/nginx/sites-available/sistema-oficinas-departamentales.conf
```

### 9.2 Agregar dentro del bloque `server { listen 443 ssl; ... }`:

```nginx
# REDIRECCION FRONTEND DE WEBSOCKETS HACIA LARAVEL REVERB
location /app/ {
    proxy_pass http://127.0.0.1:6001;
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

### 9.3 Validar y reiniciar Nginx
```bash
sudo nginx -t
sudo systemctl restart nginx
```

---

## Paso 10 — Verificacion

### 10.1 Verificar que Reverb esta corriendo
```bash
ps aux | grep reverb
```
Debe aparecer un proceso `php artisan reverb:start`.

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
Abrir el navegador en `https://departamentales.asamblea.gob.sv` y en la consola del navegador (F12) buscar errores de conexion WebSocket. No debe haber errores de conexion.

### 10.4 Verificar que las migraciones corrieron
```bash
php artisan migrate:status
```
Debe mostrar `Yes` en la columna `Ran` para todas las migraciones.

---

## Troubleshooting

### Error: "Foreign key constraint is incorrectly formed"
La migracion anterior fallo. Ejecutar manualmente:
```sql
-- Verificar el estado actual
SHOW CREATE TABLE actividades;

-- Si hay un KEY huérfano sin CONSTRAINT:
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

### Reverb no se conecta desde el navegador
1. Verificar que el proxy Nginx esta configurado (Paso 9)
2. Verificar `REVERB_ALLOWED_ORIGINS` incluye el dominio completo con `https://`
3. Verificar que el firewall del servidor permite conexiones entrantes en puerto 443

### El daemon no levanta Reverb
1. Verificar permisos: `ls -la reverb_daemon.sh` (debe tener `x`)
2. Verificar el log: `tail -f storage/logs/reverb.log`
3. Verificar que PHP esta en el PATH del cron: usar ruta completa a PHP si es necesario
   ```
   * * * * * /usr/bin/php /var/www/html/.../artisan reverb:start ...
   ```

### Los tickets de cambio de contrasena no llegan a TI
1. Verificar que la tabla `notifications` existe: `php artisan tinker --execute="Schema::hasTable('notifications')"`
2. Verificar que los usuarios TI tienen el rol correcto: `php artisan tinker --execute="App\Models\User::whereHas('roles', fn(\$q) => \$q->where('name','ti'))->count()"`
3. Las notificaciones aparecen en el icono de campana del panel de Filament

---

## Comandos de Emergencia

```bash
# Reiniciar Reverb manualmente
kill $(cat storage/logs/reverb.pid) 2>/dev/null
rm -f storage/logs/reverb.pid
php artisan reverb:start --host=0.0.0.0 --port=6001 >> storage/logs/reverb.log 2>&1 &

# Ver logs de Reverb en tiempo real
tail -f storage/logs/reverb.log

# Ver errores recientes de Laravel
tail -f storage/logs/laravel.log

# Limpiar todo y empezar de cero
php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan cache:clear
```
