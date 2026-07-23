# Primeros Auxilios para TI -- FAQ de Problemas Frecuentes

**Para:** Equipo de TI -- Asamblea Legislativa
**Sistema:** Sistema de Oficinas Departamentales
**Fecha:** Julio 2026

Cuando un usuario abra un ticket en HelpDesk por un problema con el sistema, seguir estos pasos antes de escalar al desarrollador.

---

## INDICE

1. [No puedo ingresar al sistema](#1-no-puedo-ingresar-al-sistema)
2. [Me sale pantalla 403 -- Acceso Restringido](#2-me-sale-pantalla-403--acceso-restringido)
3. [No veo los menus que debería ver](#3-no-veo-los-menus-que-deberia-ver)
4. [No puedo editar o eliminar registros](#4-no-puedo-editar-o-eliminar-registros)
5. [Los archivos no se suben o no se abren](#5-los-archivos-no-se-suben-o-no-se-abren)
6. [No me llegan notificaciones](#6-no-me-llegan-notificaciones)
7. [No puedo cerrar el mes](#7-no-puedo-cerrar-el-mes)
8. [El consolidado sale vacío](#8-el-consolidado-sale-vacio)
9. [No puedo cambiar mi contraseña](#9-no-puedo-cambiar-mi-contraseña)
10. [La página carga lenta o muestra error 500](#10-la-página-carga-lenta-o-muestra-error-500)
11. [Comandos rápidos de verificación](#11-comandos-rapidos-de-verificacion)

---

## 1. No puedo ingresar al sistema

**Lo que reporta el usuario:** "Me dice usuario o contraseña incorrectos" o "Aún no tenés acceso al sistema"

### Causa más probable
El usuario fue creado en Active Directory (Windows) pero **no fue creado en la base de datos del sistema**.

### Pasos para resolver

```bash
# 1. Verificar si el usuario existe en la base de datos
php artisan tinker --execute="
\$user = App\Models\User::where('username', 'NOMBRE_USUARIO')->first();
echo \$user ? 'EXISTE (activo: ' . (\$user->activo ? 'SI' : 'NO') . ')' : 'NO EXISTE';
"

# 2. Si NO existe, crearlo desde el panel de administracion
#    Ir a: https://departamentales.asamblea.gob.sv/admin/users
#    Click en "Create"
#    Llenar:
#      - Username: mismo que tiene en Active Directory (ej: jperez)
#      - Name: nombre completo
#      - Email: correo institucional
#      - Departamental: seleccionar su departamental
#      - Rol: asignar el rol correcto
#      - Activo: SI
#      - Password: dejar VACIO (usara LDAP)
```

### Si el usuario SÍ existe pero no puede entrar
```bash
# Verificar si tiene rol asignado
php artisan tinker --execute="
\$user = App\Models\User::where('username', 'NOMBRE_USUARIO')->first();
echo 'Rol: ' . (\$user->roles->pluck('name')->implode(', ') ?: 'SIN ROL');
echo PHP_EOL . 'Activo: ' . (\$user->activo ? 'SI' : 'NO');
echo PHP_EOL . 'Departamental: ' . (\$user->departamental_id ?: 'SIN ASIGNAR');
"
```

**Si no tiene rol:** Asignar el rol desde Users > Edit > Roles
**Si tiene `activo=NO`:** Cambiar a `activo=SI` desde Users > Edit

---

## 2. Me sale pantalla 403 -- Acceso Restringido

**Lo que reporta el usuario:** "Entré pero me sale una pantalla que dice 'Acceso Restringido'"

### Causa más probable
El usuario tiene `activo=false` o tiene un rol sin permisos para el panel.

### Pasos para resolver

```bash
# Verificar estado del usuario
php artisan tinker --execute="
\$user = App\Models\User::where('username', 'NOMBRE_USUARIO')->first();
if (!\$user) { echo 'USUARIO NO ENCONTRADO'; exit; }
echo 'Activo: ' . (\$user->activo ? 'SI' : 'NO');
echo PHP_EOL . 'Roles: ' . \$user->roles->pluck('name')->implode(', ');
echo PHP_EOL . 'canAccessPanel: ' . (\$user->canAccessPanel() ? 'SI' : 'NO');
"
```

**Si `activo=NO`:** Activar desde Users > Edit > Activo = SI
**Si `canAccessPanel=NO`:** El usuario necesita un rol con permisos de panel (super_admin, ti, gol, coordinador, asistente_tecnico, auditoria)

---

## 3. No veo los menus que debería ver

**Lo que reporta el usuario:** "No veo la opción de Actividades" / "No veo Requisiciones" / "No veo Tickets"

### Causa más probable
Los menus visibles dependen del rol asignado. Cada rol ve diferente contenido.

### Tabla de qué ve cada rol

| Menu | super_admin | ti | gol | coordinador | asistente_tecnico | auditoria |
|------|:-----------:|:--:|:---:|:-----------:|:-----------------:|:---------:|
| Actividades | Todo | Todo | Todo | Solo suya | Solo suya | Solo suya |
| Cierres Mensuales | Todo | Si | Si | Solo su departamental | No | No |
| Requisiciones | Todo | Todo | Todo | Solo su departamental | Solo su departamental | No |
| Tickets | Todo | Todo | Todo | Solo su departamental | Solo su departamental | No |
| Contratos | Todo | Todo | Todo | Solo su departamental | No | No |
| Consolidado | Todo | Si (descarga) | Todo | **No visible** | No | No |
| Users | Todo | Si (sin super_admin) | No | No | No | No |
| Reportes (Power BI) | Todo | Si | Si | Si | Si | Si |

**Si el usuario debería ver algo que no ve:**
1. Verificar su rol con el comando del paso 1
2. Asignar el rol correcto desde Users > Edit > Roles
3. Si el rol es correcto pero sigue sin ver, puede ser un problema de `departamental_id`

### Verificar departamental del usuario
```bash
php artisan tinker --execute="
\$user = App\Models\User::where('username', 'NOMBRE_USUARIO')->first();
\$dept = \$user->departamental ? \$user->departamental->nombre : 'SIN DEPARTAMENTAL';
echo 'Departamental: ' . \$dept . ' (ID: ' . (\$user->departamental_id ?: 'NULL') . ')';
"
```

**Si `departamental_id` está vacío:** Asignar la departamental desde Users > Edit > Departamental

---

## 4. No puedo editar o eliminar registros

**Lo que reporta el usuario:** "Creé una actividad pero no puedo modificarla" / "No puedo borrar un ticket"

### Reglas de edición/eliminación

| Registro | Quién puede editar | Quién puede eliminar |
|----------|-------------------|---------------------|
| Actividades | super_admin, ti, gol. El coordinador solo ve las suyas | super_admin, ti (con validación de estado) |
| Requisiciones | super_admin, ti, gol, coordinador de esa departamental | super_admin, ti, coordinador si es estado inicial |
| Tickets | super_admin, ti, gol, coordinador de esa departamental | super_admin, ti (solo ciertos estados) |
| Contratos | super_admin, ti, gol, coordinador de esa departamental | super_admin, ti |
| Cierres Mensuales | super_admin (generar/aprobar) | super_admin |

**Si un coordinador no puede editar:** Es el comportamiento esperado. Solo super_admin, ti y gol pueden editar actividades. El coordinador puede **crear** nuevas actividades y **ver** las suyas.

**Si un coordinador quiere eliminar algo:** Solo puede eliminar requisiciones en estado inicial. Para todo lo demás, debe pedir a ti o super_admin.

---

## 5. Los archivos no se suben o no se abren

**Lo que reporta el usuario:** "No puedo subir archivos" / "Los archivos que subí se ven pero no se descargan" / "Error 500 al subir"

### Causa más probable
El servidor de archivos SMB no está accesible o el montaje CIFS no está activo.

### Verificar montaje CIFS
```bash
# Verificar si el montaje está activo
mount | grep repositorio

# Si no aparece nada, montar manualmente
sudo mount -t cifs //172.19.10.122/Repositorio_dpto /mnt/repositorio_dpto \
  -o username=userdpto,password=Dpto2026,uid=www-data,gid=www-data
```

### Verificar permisos
```bash
# Verificar que www-data puede escribir
ls -la /mnt/repositorio_dpto/

# Si hay problema de permisos
sudo chown -R www-data:www-data /mnt/repositorio_dpto/
sudo chmod -R 775 /mnt/repositorio_dpto/
```

### Verificar configuración
```bash
# Verificar que el disco repositorio está configurado
php artisan tinker --execute="echo print_r(config('filesystems.disks.repositorio'), true);"
```

---

## 6. No me llegan notificaciones

**Lo que reporta el usuario:** "Debería recibir un recordatorio pero no me llega" / "No me sale la campanita con avisos"

### Causa más probable
El worker de colas no está corriendo.

### Verificar worker
```bash
# Verificar si hay un proceso queue:work activo
ps aux | grep queue:work

# Si no aparece, iniciarlo
cd /var/www/asamblea-departamentales-sistemas
nohup php artisan queue:work --queue=notifications --timeout=30 --tries=3 >> storage/logs/queue.log 2>&1 &
```

### Verificar si hay notificaciones pendientes
```bash
# Verificar jobs encolados
php artisan tinker --execute="echo 'Jobs pendientes: ' . DB::table('jobs')->count();"

# Verificar notificaciones en la tabla
php artisan tinker --execute="echo 'Notificaciones: ' . DB::table('notifications')->count();"

# Procesar jobs pendientes uno por uno
php artisan queue:work --queue=notifications --once
```

### Recordatorios de actividades
```bash
# Ejecutar recordatorios manualmente (dry run)
php artisan actividades:reminders --dry-run

# Enviar recordatorios reales
php artisan actividades:reminders
```

### Verificar que el crontab tiene todo configurado
```bash
crontab -l
```

Debe mostrar estas 3 líneas:
```
* * * * * cd /var/www/asamblea-departamentales-sistemas && php artisan schedule:run >> /dev/null 2>&1
* * * * * cd /var/www/asamblea-departamentales-sistemas && php artisan queue:work --queue=notifications --timeout=30 --tries=3 --stop-when-empty >> /dev/null 2>&1
* * * * * bash /var/www/asamblea-departamentales-sistemas/reverb_daemon.sh
```

**Si falta alguna línea:** Agregarla con `crontab -e`

---

## 7. No puedo cerrar el mes

**Lo que reporta el usuario:** "Intento cerrar el mes pero me dice que hay actividades pendientes"

### Causa más probable
Hay actividades del mes que no están en estado `Completada` o `Cancelada`.

### Verificar actividades pendientes
```bash
# Reemplazar MES y ANIO con los valores correspondientes
php artisan tinker --execute="
\$pendientes = App\Models\Actividad::whereMonth('fecha', MES)
    ->whereYear('fecha', ANIO)
    ->whereNotIn('estado', ['Completada', 'Cancelada'])
    ->select('id', 'macroactividad', 'estado', 'fecha', 'departamental_id')
    ->get();
echo 'Actividades pendientes del mes: ' . \$pendientes->count() . PHP_EOL;
foreach (\$pendientes as \$a) {
    echo '  #' . \$a->id . ' - ' . \$a->macroactividad . ' (' . \$a->estado . ')' . PHP_EOL;
}
"
```

### Para resolver
1. Decir a los coordinadores que **completen** o **cancelen** todas las actividades del mes
2. O si es un caso especial, pedir a super_admin que fuerce el cierre

### Reabrir un mes cerrado
Solo super_admin puede reabrir. Ir a: Cierres Mensuales > seleccionar el mes > botón "Reabrir"

---

## 8. El consolidado sale vacío

**Lo que reporta el usuario:** "Genero el consolidado pero no muestra datos" / "El PDF sale en blanco"

### Causa más probable
Las actividades no tienen los datos mínimos requeridos.

### Verificar qué necesita el consolidado
```bash
# Verificar actividades completadas del mes
php artisan tinker --execute="
\$completadas = App\Models\Actividad::whereMonth('fecha', MES)
    ->whereYear('fecha', ANIO)
    ->where('estado', 'Completada')
    ->count();
\$total = App\Models\Actividad::whereMonth('fecha', MES)
    ->whereYear('fecha', ANIO)
    ->count();
echo 'Total actividades: ' . \$total . PHP_EOL;
echo 'Completadas: ' . \$completadas . PHP_EOL;
if (\$completadas == 0) {
    echo 'ERROR: No hay actividades Completadas para este mes' . PHP_EOL;
    echo 'El consolidado necesita actividades con estado Completada' . PHP_EOL;
}
"
```

### Requisitos del consolidado
- Actividades con `estado = Completada`
- Actividades con `fecha` dentro del rango del mes consultado
- Actividades con `departamental_id` asignado
- El usuario que genera debe tener permisos (super_admin, gol, ti)

### Si el consolidado es para una departamental específica
Verificar que las actividades tengan el `departamental_id` correcto:
```bash
php artisan tinker --execute="
\$actividades = App\Models\Actividad::whereMonth('fecha', MES)
    ->whereYear('fecha', ANIO)
    ->select('departamental_id', DB::raw('count(*) as total'))
    ->groupBy('departamental_id')
    ->get();
foreach (\$actividades as \$a) {
    \$dept = App\Models\Departamental::find(\$a->departamental_id);
    echo (\$dept ? \$dept->nombre : 'SIN DEPARTAMENTAL') . ': ' . \$a->total . ' actividades' . PHP_EOL;
}
"
```

---

## 9. No puedo cambiar mi contraseña

**Lo que reporta el usuario:** "Quiero cambiar mi contraseña pero no encuentro dónde"

### Flujo correcto
1. Ir al menú del usuario (esquina superior derecha)
2. Click en "Mi Perfil"
3. Sección "Cambiar Contraseña"
4. Ingresar contraseña actual + nueva contraseña
5. Click en "Guardar"

### Qué pasa cuando cambia la contraseña
El sistema envía un ticket automático a TI (tipo `CAMBIO_CONTRASENA`) para que actualicen la contraseña en Active Directory.

### Si el usuario no puede acceder a "Mi Perfil"
```bash
# Verificar que el usuario puede autenticarse
php artisan tinker --execute="
\$user = App\Models\User::where('username', 'NOMBRE_USUARIO')->first();
echo 'Username: ' . \$user->username . PHP_EOL;
echo 'Email: ' . \$user->email . PHP_EOL;
echo 'LDAP GUID: ' . (\$user->guid ?? 'NO ASIGNADO') . PHP_EOL;
"
```

---

## 10. La página carga lenta o muestra error 500

**Lo que reporta el usuario:** "La página tarda mucho" / "Me sale error 500" / "No carga nada"

### Error 500 inmediato
```bash
# Ver errores recientes
tail -50 storage/logs/laravel.log
```

### Página lenta
```bash
# Limpiar caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Verificar que el disco está tiene espacio
df -h

# Verificar que MySQL está corriendo
sudo systemctl status mysql
# o
sudo systemctl status mariadb
```

### Si el error es de permisos
```bash
sudo chown -R asamblea:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## 11. Comandos rápidos de verificación

Copia y pega estos comandos directamente en la terminal del servidor. Reemplazar `NOMBRE_USUARIO` con el usuario real.

### Diagnosticar un usuario completo
```bash
php artisan tinker --execute="
\$u = App\Models\User::where('username', 'NOMBRE_USUARIO')->first();
if (!\$u) { echo 'USUARIO NO ENCONTRADO'; exit; }
echo '=== USUARIO: ' . \$u->name . ' ===' . PHP_EOL;
echo 'Username: ' . \$u->username . PHP_EOL;
echo 'Email: ' . \$u->email . PHP_EOL;
echo 'Activo: ' . (\$u->activo ? 'SI' : 'NO') . PHP_EOL;
echo 'Roles: ' . (\$u->roles->pluck('name')->implode(', ') ?: 'SIN ROL') . PHP_EOL;
echo 'Departamental: ' . (\$u->departamental->nombre ?? 'SIN ASIGNAR') . PHP_EOL;
echo 'LDAP GUID: ' . (\$u->guid ?? 'NO ASIGNADO') . PHP_EOL;
echo 'Creado: ' . \$u->created_at . PHP_EOL;
echo 'Ultimo login: ' . (\$u->last_login_at ?? 'NUNCA') . PHP_EOL;
"
```

### Verificar salud del sistema
```bash
echo "=== REVERB ===" && ps aux | grep reverb | grep -v grep
echo "=== QUEUE WORKER ===" && ps aux | grep queue:work | grep -v grep
echo "=== CRONTAB ===" && crontab -l 2>/dev/null | grep artisan
echo "=== DISCO ===" && df -h / | tail -1
echo "=== LOG ERRORS ===" && tail -5 storage/logs/laravel.log 2>/dev/null | grep -c "ERROR"
```

### Verificar que todo funciona
```bash
# Test de base de datos
php artisan tinker --execute="echo 'DB: OK (' . DB::select('SELECT 1')[0]->{1} . ')';"

# Test de LDAP
php artisan tinker --execute="
try {
    \$conn = LdapRecord\Laravel\Facades\Ldap::connection();
    \$conn->connect();
    echo 'LDAP: OK';
} catch (Exception \$e) {
    echo 'LDAP: FALLO - ' . \$e->getMessage();
}
"

# Test de tabla notifications
php artisan tinker --execute="echo 'Tabla notifications: ' . (Schema::hasTable('notifications') ? 'OK' : 'FALTA');"

# Test de tabla jobs
php artisan tinker --execute="echo 'Tabla jobs: ' . (Schema::hasTable('jobs') ? 'OK' : 'FALTA');"
```

### Limpiar todo (emergencia)
```bash
cd /var/www/asamblea-departamentales-sistemas
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "Cachés limpiadas. Pedir al usuario que presione Ctrl+F5."
```

---

## Referencia rápida de roles

| Rol | Quién lo tiene | Qué puede hacer |
|-----|---------------|-----------------|
| `super_admin` | Solo el desarrollador | Todo. No tiene restricciones. |
| `ti` | Equipo de TI | Ver todo, editar actividades, generar reportes, administrar usuarios (excepto super_admin) |
| `gol` | Gerencia de Operaciones Legislativas | Ver todo, generar consolidados, aprobar cierres. **No puede editar** actividades. |
| `coordinador` | Jefe de cada departamental | Crear/ver actividades propias, requisiciones y tickets de su departamental |
| `asistente_tecnico` | Personal de apoyo | Crear/ver actividades propias y requisiciones de su departamental |
| `auditoria` | Equipo de auditoría | Ver actividades de su departamental (solo lectura) |

---

## Escalación al desarrollador

Si después de seguir estos pasos el problema persiste, recopilar:

1. **Nombre de usuario** afectado
2. **Mensaje de error** exacto (captura de pantalla)
3. **Qué intentaba hacer** el usuario
4. **Resultado** de los comandos de verificación del paso 11
5. **Últimas líneas** del `storage/logs/laravel.log`

Enviar a soporte con este formato:
```
PROBLEMA: [descripción breve]
USUARIO: [nombre de usuario]
ERROR: [mensaje de error o "sin error visible"]
PASOS: [qué se intentó]
LOG: [últimas 5 líneas de laravel.log]
```
