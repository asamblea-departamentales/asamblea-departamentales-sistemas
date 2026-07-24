window.showToastNotification=function({title:e,body:t,iconColor:n,duration:i}){console.log("🔔 Mostrando toast:",{title:e,body:t,iconColor:n,duration:i}),l({title:e,body:t,iconColor:n,duration:i})};function l({title:e,body:t,iconColor:n,duration:i}){console.log("🎨 Creando toast HTML:",{title:e,body:t,iconColor:n});const s=d(),o=document.createElement("div"),a=c(n),r=p(n);o.style.cssText=`
        background: ${a};
        border: 1px solid ${r};
        border-left: 5px solid ${r};
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
    `,o.innerHTML=`
        <div style="display: flex; align-items: flex-start; gap: 12px;">
            <div style="flex-shrink: 0; margin-top: 2px;">
                ${g(n)}
            </div>
            <div style="flex: 1; min-width: 0;">
                <div style="font-weight: 600; color: #1F2937; margin-bottom: 4px; font-size: 15px; line-height: 1.3;">
                    ${e}
                </div>
                <div style="color: #4B5563; font-size: 14px; line-height: 1.4; word-wrap: break-word;">
                    ${t}
                </div>
            </div>
            <button onclick="this.parentElement.parentElement.style.opacity='0'; this.parentElement.parentElement.style.transform='translateX(100%)'; setTimeout(() => this.parentElement.parentElement.remove(), 300);" 
                    style="background: none; border: none; color: #9CA3AF; cursor: pointer; padding: 4px; font-size: 20px; line-height: 1; flex-shrink: 0; border-radius: 4px; transition: color 0.2s;"
                    onmouseover="this.style.color='#374151'"
                    onmouseout="this.style.color='#9CA3AF'">
                ×
            </button>
        </div>
    `,s.appendChild(o),console.log("📌 Toast agregado al DOM"),requestAnimationFrame(()=>{requestAnimationFrame(()=>{o.style.opacity="1",o.style.transform="translateX(0)",console.log("✨ Toast animado")})}),i&&i!=="persistent"&&i>0&&setTimeout(()=>{o.style.opacity="0",o.style.transform="translateX(100%)",setTimeout(()=>{o.parentElement&&(o.remove(),console.log("🗑️ Toast removido automáticamente"))},300)},i)}function d(){let e=document.getElementById("custom-toast-container");return e||(e=document.createElement("div"),e.id="custom-toast-container",e.style.cssText=`
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 999999;
            pointer-events: none;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        `,document.body.appendChild(e),console.log("📦 Contenedor de toasts creado")),e}function c(e){return{success:"#F0FDF4",warning:"#FFFBEB",danger:"#FEF2F2",error:"#FEF2F2",info:"#EFF6FF"}[e]||"#F9FAFB"}function p(e){return{success:"#059669",warning:"#D97706",danger:"#DC2626",error:"#DC2626",info:"#2563EB"}[e]||"#6B7280"}function g(e){const t={success:'<div style="width: 20px; height: 20px; background: #059669; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: bold;">✓</div>',warning:'<div style="width: 20px; height: 20px; background: #D97706; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: bold;">!</div>',danger:'<div style="width: 20px; height: 20px; background: #DC2626; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: bold;">×</div>',error:'<div style="width: 20px; height: 20px; background: #DC2626; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: bold;">×</div>',info:'<div style="width: 20px; height: 20px; background: #2563EB; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: bold;">i</div>'};return t[e]||t.info}window.updateFilamentBadge=function(){window.Livewire&&(window.Livewire.dispatch("databaseNotificationsSent"),console.log("🔔 Badge actualizado con databaseNotificationsSent"))};
