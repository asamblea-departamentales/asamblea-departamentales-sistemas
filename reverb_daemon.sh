#!/bin/bash
# ==============================================================================
# REVERB_DAEMON.SH - Monitorea e inicia Laravel Reverb automáticamente
# ==============================================================================
# Instrucciones para el equipo de TI Institucional:
# 1. Colocar este archivo en la raíz del proyecto.
# 2. Darle permisos de ejecución: chmod +x reverb_daemon.sh
# 3. Registrar en el crontab del usuario: * * * * * bash /ruta/reverb_daemon.sh
# ==============================================================================

# [MODIFICAR]: Define aqui la ruta absoluta de la carpeta raiz del proyecto en produccion
# Ajustar segun la ruta real del servidor
APP_DIR="/var/www/asamblea-departamentales-sistemas"

# Rutas dinámicas de archivos de registro y control (No requieren modificación)
LOG_FILE="$APP_DIR/storage/logs/reverb.log"
PID_FILE="$APP_DIR/storage/logs/reverb.pid"

# 1. Validar si existe el registro del ID del proceso (PID)
if [ -f "$PID_FILE" ]; then
    PID=$(cat "$PID_FILE")
    # Verificar si el proceso con ese ID sigue vivo en el sistema operativo
    if kill -0 "$PID" 2>/dev/null; then
        # El servicio ya está corriendo de forma correcta. Finalizar script de validación.
        exit 0
    else
        # El proceso murió de forma inesperada. Limpiar archivo de bandera viejo.
        rm -f "$PID_FILE"
    fi
fi

# 2. Moverse a la ruta de la aplicación para iniciar el proceso de Laravel
cd "$APP_DIR" || exit 1

# 3. Arrancar Reverb en segundo plano redirigiendo logs y guardando el nuevo ID de proceso
nohup php artisan reverb:start --host=0.0.0.0 --port=6002 >> "$LOG_FILE" 2>&1 &
echo $! > "$PID_FILE"
