<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe Consolidado Mensual</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 20px;
        }
        h2, h3 {
            text-align: center;
            margin: 5px 0;
            color: #2c3e50;
        }
        p {
            margin: 5px 0;
            text-align: center;
        }
        .logo {
            text-align: center;
            margin-bottom: 10px;
        }
        .logo img {
            height: 80px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10px;
        }
        table th, table td {
            border: 1px solid #999;
            padding: 5px;
            text-align: center;
        }
        table th {
            background-color: #34495e;
            color: white;
            font-weight: bold;
        }
        .section-title {
            background-color: #34495e;
            color: #fff;
            padding: 8px;
            margin-top: 20px;
            font-size: 13px;
            font-weight: bold;
        }
        .resumen-general {
            background-color: #f8f9fa;
            padding: 10px;
            margin: 15px 0;
            border: 2px solid #34495e;
        }
        .resumen-general table th {
            background-color: #2c3e50;
        }
        .page-break {
            page-break-after: always;
        }
        .departamental-section {
            margin-top: 20px;
            border-top: 3px solid #34495e;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    {{-- Encabezado --}}
    <div class="logo">
        <img src="{{ public_path('images/logo-azul-fondo-transparente (002).png') }}" alt="Logo Asamblea">    
    </div>
    <h2>INFORME CONSOLIDADO MENSUAL</h2>
    <h3>Sistema de Información Estadístico - Departamento de Oficinas Departamentales</h3>
    <p><strong>Mes:</strong> {{ $meses[$mes] }} {{ $año }}</p>
    <p><strong>Fecha de generación:</strong> {{ now()->format('d/m/Y H:i') }}</p>
    <p><strong>Generado por:</strong> {{ auth()->user()->name }}</p>
    <hr>

    {{-- Resumen General de TODAS las departamentales --}}
    <div class="section-title">📊 RESUMEN GENERAL CONSOLIDADO</div>
    <div class="resumen-general">
        <table>
            <thead>
                <tr>
                    <th>Departamental</th>
                    <th>Proyectadas</th>
                    <th>Ejecutadas</th>
                    <th>Pendientes</th>
                    <th>Canceladas</th>
                    <th>% Cumplimiento</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalProyectadas = 0;
                    $totalEjecutadas = 0;
                    $totalPendientes = 0;
                    $totalCanceladas = 0;
                @endphp

                @foreach($cierres as $cierre)
                    @php
                        $totalProyectadas += $cierre->actividades_proyectadas;
                        $totalEjecutadas += $cierre->actividades_ejecutadas;
                        $totalPendientes += $cierre->actividades_pendientes;
                        $totalCanceladas += $cierre->actividades_canceladas;
                    @endphp
                    <tr>
                        <td><strong>{{ $cierre->departamental->nombre }}</strong></td>
                        <td>{{ $cierre->actividades_proyectadas }}</td>
                        <td>{{ $cierre->actividades_ejecutadas }}</td>
                        <td>{{ $cierre->actividades_pendientes }}</td>
                        <td>{{ $cierre->actividades_canceladas }}</td>
                        <td>{{ $cierre->porcentaje_cumplimiento }}%</td>
                    </tr>
                @endforeach

                {{-- Totales --}}
                <tr style="background-color: #e8f4f8; font-weight: bold;">
                    <td>TOTAL NACIONAL</td>
                    <td>{{ $totalProyectadas }}</td>
                    <td>{{ $totalEjecutadas }}</td>
                    <td>{{ $totalPendientes }}</td>
                    <td>{{ $totalCanceladas }}</td>
                    <td>{{ $totalProyectadas > 0 ? round(($totalEjecutadas / $totalProyectadas) * 100, 2) : 0 }}%</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Detalle por cada Departamental --}}
    @foreach($cierres as $index => $cierre)
        <div class="departamental-section {{ $index > 0 ? 'page-break' : '' }}">
            <div class="section-title">{{ $cierre->departamental->nombre }}</div>
            
            {{-- Resumen de la departamental --}}
            <table>
                <tr>
                    <th>Proyectadas</th>
                    <th>Ejecutadas</th>
                    <th>Pendientes</th>
                    <th>Canceladas</th>
                    <th>% Cumplimiento</th>
                </tr>
                <tr>
                    <td>{{ $cierre->actividades_proyectadas }}</td>
                    <td>{{ $cierre->actividades_ejecutadas }}</td>
                    <td>{{ $cierre->actividades_pendientes }}</td>
                    <td>{{ $cierre->actividades_canceladas }}</td>
                    <td>{{ $cierre->porcentaje_cumplimiento }}%</td>
                </tr>
            </table>

            {{-- Actividades por programa --}}
            @php
                $actividadesPorPrograma = $cierre->actividades->groupBy('programa');
            @endphp

            @foreach(['Atención Ciudadana','Participación Ciudadana','Educación Cívica'] as $programa)
                @if($actividadesPorPrograma->has($programa))
                    <h4 style="margin-top: 10px; color: #2c3e50;">{{ $programa }}</h4>
                    <table>
                        <tr>
                            <th>#</th>
                            <th>Macroactividad</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Lugar</th>
                        </tr>
                        @foreach($actividadesPorPrograma[$programa] as $i => $actividad)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td style="text-align: left;">{{ Str::limit($actividad->macroactividad, 50) }}</td>
                                <td>{{ $actividad->estado }}</td>
                                <td>{{ optional($actividad->fecha)->format('d/m/Y') }}</td>
                                <td style="text-align: left;">{{ Str::limit($actividad->lugar, 30) }}</td>
                            </tr>
                        @endforeach
                    </table>
                @endif
            @endforeach
        </div>
    @endforeach

</body>
</html>