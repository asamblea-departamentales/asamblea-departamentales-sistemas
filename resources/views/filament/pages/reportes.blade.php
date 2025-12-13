<x-filament-panels::page>
    <div class="space-y-4">
        
        {{-- Header --}}
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6 mb-6 border border-blue-200">
            <div class="flex items-start justify-between">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Reportes Departamentales
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Usuario: <span class="font-semibold text-indigo-600">{{ auth()->user()->name }}</span>
                        </p>
                    </div>
                </div>
                @if(config('app.env') !== 'production')
                <div class="flex-shrink-0">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        🧪 Modo Desarrollo
                    </span>
                </div>
                @endif
            </div>
        </div>

        {{-- Controles --}}
        <div class="flex justify-between items-center mb-4 flex-wrap gap-2">
            <div class="flex gap-2 flex-wrap">
                <button 
                    onclick="toggleFullscreen()"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                    Pantalla Completa
                </button>

                <button 
                    onclick="refreshReport()"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Actualizar
                </button>
            </div>

            <a 
                href="{{ config('services.powerbi.embed_url') }}" 
                target="_blank"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                Abrir en Power BI
            </a>
        </div>

        {{-- Loading --}}
        <div id="loadingIndicator" class="flex items-center justify-center bg-white rounded-lg shadow-sm border border-gray-200 p-12">
            <div class="text-center">
                <svg class="animate-spin h-12 w-12 mx-auto text-indigo-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-gray-600 font-medium">Cargando reporte...</p>
                <p class="text-gray-400 text-sm mt-2">Conectando con Power BI</p>
            </div>
        </div>

        {{-- Contenedor del reporte --}}
        <div id="reportContainer" class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200" style="display: none;">
            @if(config('services.powerbi.embed_url'))
                <iframe 
                    id="powerbiFrame"
                    title="Reportes Departamentales" 
                    width="100%" 
                    height="800" 
                    src="{{ config('services.powerbi.embed_url') }}" 
                    frameborder="0" 
                    allowFullScreen="true"
                    style="border: none; display: block;">
                </iframe>
            @else
                <div class="flex items-center justify-center h-96 bg-gray-50">
                    <div class="text-center p-8">
                        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Reporte no configurado</h3>
                        <p class="text-sm text-gray-500 mb-4">Agrega POWERBI_EMBED_URL en tu archivo .env</p>
                        <code class="bg-gray-100 px-3 py-2 rounded text-xs">POWERBI_EMBED_URL="https://app.powerbi.com/..."</code>
                    </div>
                </div>
            @endif
        </div>

        {{-- Info de ambiente --}}
        @if(config('app.env') !== 'production')
        <div class="mt-6 bg-yellow-50 rounded-lg p-4 border border-yellow-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Entorno de Desarrollo/Staging</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>Estás en un ambiente de prueba que simula el servidor gubernamental. En producción:</p>
                        <ul class="list-disc list-inside mt-2 space-y-1">
                            <li>Se usarán credenciales de correo institucional de la Asamblea</li>
                            <li>Los datos estarán en el servidor privado interno</li>
                            <li>Se aplicará autenticación con Azure AD corporativo</li>
                            <li>Row-Level Security filtrará datos por departamento/usuario</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Tips --}}
        <div class="mt-4 bg-blue-50 rounded-lg p-4 border border-blue-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-900">Tips de uso:</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Interactúa con los gráficos para filtrar y explorar datos</li>
                            <li>Usa los filtros del panel para refinar información</li>
                            <li>El reporte se actualiza automáticamente según configuración</li>
                            <li>Puedes exportar datos desde cada visual (menú de 3 puntos)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const iframe = document.getElementById('powerbiFrame');
            const loadingIndicator = document.getElementById('loadingIndicator');
            const reportContainer = document.getElementById('reportContainer');

            if (!iframe) {
                // No hay configuración, mostrar el mensaje de error
                loadingIndicator.style.display = 'none';
                reportContainer.style.display = 'block';
                return;
            }

            // Timeout para mostrar reporte después de 2 segundos
            const loadTimeout = setTimeout(function() {
                loadingIndicator.style.display = 'none';
                reportContainer.style.display = 'block';
            }, 2000);

            // Detectar cuando el iframe cargue
            iframe.addEventListener('load', function() {
                clearTimeout(loadTimeout);
                loadingIndicator.style.display = 'none';
                reportContainer.style.display = 'block';
                console.log('✅ Reporte de Power BI cargado');
            });

            // Manejar errores
            iframe.addEventListener('error', function() {
                clearTimeout(loadTimeout);
                loadingIndicator.innerHTML = `
                    <div class="text-center">
                        <svg class="h-12 w-12 mx-auto text-red-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-gray-600 font-medium">Error al cargar el reporte</p>
                        <p class="text-gray-400 text-sm mt-2">Verifica la configuración o contacta al administrador</p>
                    </div>
                `;
                console.error('❌ Error al cargar Power BI');
            });
        });

        function toggleFullscreen() {
            const container = document.getElementById('reportContainer');
            
            if (!document.fullscreenElement) {
                container.requestFullscreen().catch(err => {
                    console.error('Error pantalla completa:', err);
                });
            } else {
                document.exitFullscreen();
            }
        }

        function refreshReport() {
            const iframe = document.getElementById('powerbiFrame');
            const loadingIndicator = document.getElementById('loadingIndicator');
            const reportContainer = document.getElementById('reportContainer');
            
            if (!iframe) return;
            
            reportContainer.style.display = 'none';
            loadingIndicator.style.display = 'flex';
            
            iframe.src = iframe.src;
            
            setTimeout(function() {
                loadingIndicator.style.display = 'none';
                reportContainer.style.display = 'block';
            }, 2000);
        }

        // Ajuste responsivo
        function adjustIframeHeight() {
            const iframe = document.getElementById('powerbiFrame');
            if (iframe) {
                const windowHeight = window.innerHeight;
                const newHeight = Math.max(600, windowHeight - 300);
                iframe.style.height = newHeight + 'px';
            }
        }

        window.addEventListener('resize', adjustIframeHeight);
        adjustIframeHeight();

        // Log para debugging
        @if(config('app.debug'))
        console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        console.log('📊 Power BI Embed - Reportes');
        console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        console.log('👤 Usuario:', '{{ auth()->user()->name }}');
        console.log('🌍 Ambiente:', '{{ config("app.env") }}');
        console.log('🔗 URL:', '{{ config("services.powerbi.embed_url") ? "Configurada" : "No configurada" }}');
        console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        @endif
    </script>
    @endpush
</x-filament-panels::page>