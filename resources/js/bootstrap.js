import './bootstrap';
import '../css/app.css';

// ✅ Función GLOBAL para mostrar un toast
window.showToastNotification = function({ title, body, iconColor, duration }) {
    console.log('🔔 Mostrando toast:', { title, body, iconColor, duration });

    // Usar directamente los toasts HTML que sabemos que funcionan
    createCustomToast({ title, body, iconColor, duration });
};

// Funciones auxiliares para crear el toast HTML (no necesitan ser globales)
function createCustomToast({ title, body, iconColor, duration }) {
    console.log('🎨 Creando toast HTML:', { title, body, iconColor });
    
    const toastContainer = getOrCreateToastContainer();
    const toast = document.createElement('div');
    const bgColor = getBackgroundColorForType(iconColor);
    const borderColor = getColorForType(iconColor);
    
    toast.style.cssText = `
        background: ${bgColor};
        border: 1px solid ${borderColor};
        border-left: 5px solid ${borderColor};
        border-radius: 8px;
        padding: 16px 20px;
        margin-bottom: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        max-width: 400px;
        min-width: 300px;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        pointer-events: auto;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    `;
    
    toast.innerHTML = `
        <div style="display: flex; align-items: flex-start; gap: 12px;">
            <div style="flex-shrink: 0; margin-top: 2px;">
                ${getIconForType(iconColor)}
            </div>
            <div style="flex: 1; min-width: 0;">
                <div style="font-weight: 600; color: #1F2937; margin-bottom: 4px; font-size: 15px; line-height: 1.3;">
                    ${title}
                </div>
                <div style="color: #4B5563; font-size: 14px; line-height: 1.4; word-wrap: break-word;">
                    ${body}
                </div>
            </div>
            <button onclick="this.parentElement.parentElement.style.opacity='0'; this.parentElement.parentElement.style.transform='translateX(100%)'; setTimeout(() => this.parentElement.parentElement.remove(), 300);" 
                    style="background: none; border: none; color: #9CA3AF; cursor: pointer; padding: 4px; font-size: 20px; line-height: 1; flex-shrink: 0; border-radius: 4px; transition: color 0.2s;"
                    onmouseover="this.style.color='#374151'"
                    onmouseout="this.style.color='#9CA3AF'">
                ×
            </button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    console.log('📌 Toast agregado al DOM');
    
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0)';
            console.log('✨ Toast animado');
        });
    });
    
    if (duration && duration !== 'persistent' && duration > 0) {
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                    console.log('🗑️ Toast removido automáticamente');
                }
            }, 300);
        }, duration);
    }
}

function getOrCreateToastContainer() {
    let container = document.getElementById('custom-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'custom-toast-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 999999;
            pointer-events: none;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        `;
        document.body.appendChild(container);
        console.log('📦 Contenedor de toasts creado');
    }
    return container;
}

function getBackgroundColorForType(type) {
    const backgrounds = {
        success: '#F0FDF4',
        warning: '#FFFBEB',
        danger: '#FEF2F2',
        error: '#FEF2F2',
        info: '#EFF6FF'
    };
    return backgrounds[type] || '#F9FAFB';
}

function getColorForType(type) {
    const colors = {
        success: '#059669',
        warning: '#D97706',
        danger: '#DC2626',
        error: '#DC2626',
        info: '#2563EB'
    };
    return colors[type] || '#6B7280';
}

function getIconForType(type) {
    const icons = {
        success: '<div style="width: 20px; height: 20px; background: #059669; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: bold;">✓</div>',
        warning: '<div style="width: 20px; height: 20px; background: #D97706; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: bold;">!</div>',
        danger: '<div style="width: 20px; height: 20px; background: #DC2626; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: bold;">×</div>',
        error: '<div style="width: 20px; height: 20px; background: #DC2626; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: bold;">×</div>',
        info: '<div style="width: 20px; height: 20px; background: #2563EB; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: bold;">i</div>'
    };
    return icons[type] || icons.info;
}

// ✅ Función GLOBAL para actualizar el badge de la campanita
window.updateFilamentBadge = function() {
    if (window.Livewire) {
        // Cambiar el evento a este:
        window.Livewire.dispatch('databaseNotificationsSent');
        console.log('🔔 Badge actualizado con databaseNotificationsSent');
    }
};

// ✅ OBTENER EL USER ID y CONFIGURAR ECHO para el usuario actual
function getCurrentUserId() {
    return document.querySelector('meta[name="user-id"]')?.getAttribute('content');
}

function setupEchoForCurrentUser() {
    const userId = getCurrentUserId();
    
    if (!userId || !window.Echo) {
        console.error('❌ No se puede configurar Echo');
        return;
    }


    console.log(`🔧 Configurando Echo para usuario: ${userId}`);

    // ✅ ESTE ES EL ÚNICO LISTENER DE ECHO QUE NECESITAS
    window.Echo.private(`notifications.${userId}`)
        .listen('.notification', (notification) => {
            console.log('🔔 Notificación de Echo recibida:', notification);
            
            window.showToastNotification({
                title: notification.title || 'Nueva notificación',
                body: notification.body || 'Tienes una notificación pendiente',
                iconColor: notification.iconColor || 'info',
                duration: notification.duration
            });
            
            window.updateFilamentBadge();
        })
        .error((error) => {
            console.error('❌ Error en el canal privado de Echo:', error);
        });
}

// Inicializa Echo cuando el DOM esté listo o tan pronto como sea posible
document.addEventListener('DOMContentLoaded', () => {
    // Pequeño retraso para asegurar que Echo esté completamente cargado
    setTimeout(setupEchoForCurrentUser, 500);
});

// También, inicializa si ya estamos en un estado listo
if (window.Livewire) {
    setupEchoForCurrentUser();
}