<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cierre Mensual</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
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
        }
        table th, table td {
            border: 1px solid #999;
            padding: 6px;
            text-align: center;
        }
        table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .section-title {
            background-color: #34495e;
            color: #fff;
            padding: 5px;
            margin-top: 20px;
            font-size: 14px;
        }
        .observaciones {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #999;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>

    {{-- Encabezado institucional --}}
    <div class="logo">
        <img src="{{ public_path('images/logo-azul-fondo-transparente (002).png') }}" 
        alt="Logo Asamblea">    
    </div>
    <h2>Sistema de Información Estadístico</h2>
    <h3>Departamento de Oficinas Departamentales</h3>

    <p><strong>Departamental:</strong> {{ $cierre->departamental->nombre }}</p>
<p><strong>Responsable del cierre:</strong> {{ $cierre->user->name }}</p>
    <p>Fecha de cierre: {{ optional($cierre->fecha_cierre)->format('d/m/Y') }}</p>
    <hr>


    {{-- Resumen global --}}
    <div class="section-title">Resumen del Cierre Mensual</div>
    <table>
        <tr>
            <th>Mes</th>
            <th>Año</th>
            <th>Proyectadas</th>
            <th>Ejecutadas</th>
            <th>Pendientes</th>
            <th>Canceladas</th>
            <th>% Cumplimiento</th>
        </tr>
        <tr>
            <td>{{ $cierre->mes }}</td>
            <td>{{ $cierre->año }}</td>
            <td>{{ $cierre->actividades_proyectadas }}</td>
            <td>{{ $cierre->actividades_ejecutadas }}</td>
            <td>{{ $cierre->actividades_pendientes }}</td>
            <td>{{ $cierre->actividades_canceladas }}</td>
            <td>{{ $cierre->porcentaje_cumplimiento }}%</td>
        </tr>
    </table>

   {{-- Actividades por programa --}}
@php
// Normalizamos nombres de programas para evitar problemas de tildes o saltos de línea
$mapaProgramas = [
    'Atencion Ciudadana' => 'Atención Ciudadana',
    'Participacion Ciudadana' => 'Participación Ciudadana',
    'Educacion Civica' => 'Educación Cívica',
];

$actividadesPorPrograma = $cierre->actividades->groupBy(function($actividad) use ($mapaProgramas) {
    // Limpia espacios y saltos de línea
    $nombre = preg_replace('/\s+/', ' ', trim($actividad->programa));
    // Quita tildes para comparar
    $nombreSinTilde = str_replace(
        ['á','é','í','ó','ú','Á','É','Í','Ó','Ú'],
        ['a','e','i','o','u','A','E','I','O','U'],
        $nombre
    );
    // Devuelve el nombre oficial si existe en el mapa
    return $mapaProgramas[$nombreSinTilde] ?? $nombre;
});
@endphp

@foreach(['Atención Ciudadana','Participación Ciudadana','Educación Cívica'] as $programa)
<div class="section-title">Programa de {{ $programa }}</div>
<table>
    <tr>
        <th>#</th>
        <th>Macroactividad</th>
        <th>Estado</th>
        <th>Asistentes</th>
        <th>Hombres</th>
        <th>Mujeres</th>
    </tr>
    @forelse($actividadesPorPrograma[$programa] ?? collect() as $i => $actividad)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $actividad->macroactividad }}</td>
            <td>{{ $actividad->estado }}</td>
            <td>{{ $actividad->asistencia_total }}</td>
            <td>{{ $actividad->asistentes_hombres }}</td>
            <td>{{ $actividad->asistentes_mujeres }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="6">No hay actividades registradas para este programa.</td>
        </tr>
    @endforelse
</table>
@endforeach


    {{-- Programación mensual --}}
    <div class="section-title">Actividades Programadas para el Mes</div>
    <table>
        <tr>
            <th>Fecha</th>
            <th>Programa</th>
            <th>Macroactividad</th>
            <th>Estado</th>
            <th>Hora</th>
            <th>Lugar</th>
        </tr>
        @foreach($cierre->actividades as $actividad)
            <tr>
                <td>{{ optional($actividad->fecha)->format('d/m/Y') }}</td>
                <td>{{ $actividad->programa }}</td>
                <td>{{ $actividad->macroactividad }}</td>
                <td>{{ $actividad->estado }}</td>
                <td>{{ optional($actividad->star_date)->format('H:i') }}</td>
                <td>{{ $actividad->lugar }}</td>
            </tr>
        @endforeach
    </table>

    {{-- Observaciones --}}
    @if($cierre->observaciones)
        <div class="section-title">Observaciones</div>
        <div class="observaciones">
            {{ $cierre->observaciones }}
        </div>
    @endif

</body>
</html>
