#!/bin/bash
# reverb_daemon.sh - Monitorea e inicia Laravel Reverb si no está corriendo
# Colocar en: public_html/reverb_daemon.sh
# Ejecutar vía crontab: * * * * * /home/1581457.cloudwaysapps.com/ycrtkthbbz/public_html/reverb_daemon.sh

APP_DIR="/home/1581457.cloudwaysapps.com/ycrtkthbbz/public_html"
LOG_FILE="$APP_DIR/storage/logs/reverb.log"
PID_FILE="$APP_DIR/storage/logs/reverb.pid"

# Verificar si Reverb ya está corriendo
if [ -f "$PID_FILE" ]; then
    PID=$(cat "$PID_FILE")
    if kill -0 "$PID" 2>/dev/null; then
        exit 0
    else
        rm -f "$PID_FILE"
    fi
fi

# Iniciar Reverb en background
cd "$APP_DIR"
nohup php artisan reverb:start --host=0.0.0.0 --port=6001 >> "$LOG_FILE" 2>&1 &
echo $! > "$PID_FILE"
