<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Actividades</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; margin: 20px; }
        h2 { text-align: center; margin: 5px 0; color: #2c3e50; }
        p { margin: 5px 0; text-align: center; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #34495e; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        table th, table td { border: 1px solid #999; padding: 5px; text-align: left; font-size: 10px; }
        table th { background-color: #34495e; color: #fff; font-weight: bold; }
        table tr:nth-child(even) { background-color: #f9f9f9; }
        .footer { text-align: center; margin-top: 20px; font-size: 10px; color: #666; border-top: 1px solid #ccc; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Asamblea Legislativa de El Salvador</h2>
        <p><strong>Sistema de Oficinas Departamentales</strong></p>
        <p>Reporte de Actividades — {{ $titulo ?? 'Todas las departamentales' }}</p>
        <p>Fecha de generación: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Programa</th>
                <th>Macroactividad</th>
                <th>Estado</th>
                <th>Fecha Inicio</th>
                <th>Lugar</th>
                <th>Departamental</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($actividades as $actividad)
                <tr>
                    <td>{{ $actividad->id }}</td>
                    <td>{{ \App\Models\Catalogo::label('programa', $actividad->programa) ?? $actividad->programa }}</td>
                    <td>{{ Str::limit($actividad->macroactividad, 40) }}</td>
                    <td>{{ $actividad->estado }}</td>
                    <td>{{ $actividad->star_date?->format('d/m/Y') }}</td>
                    <td>{{ Str::limit($actividad->lugar, 25) }}</td>
                    <td>{{ $actividad->departamental?->nombre }}</td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;">No hay actividades para mostrar.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Documento generado automáticamente por el Sistema de Oficinas Departamentales.</p>
    </div>
</body>
</html>
