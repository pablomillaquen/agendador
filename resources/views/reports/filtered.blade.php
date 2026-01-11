<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Citas</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #1e88e5; padding-bottom: 10px; margin-bottom: 20px; }
        .title { color: #1e88e5; font-size: 24px; margin-bottom: 5px; }
        .subtitle { font-size: 16px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #f2f2f2; text-align: left; padding: 10px; border-bottom: 1px solid #ddd; font-size: 14px; }
        td { padding: 10px; border-bottom: 1px solid #eee; font-size: 13px; }
        .status-badge { padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .status-scheduled { background-color: #dbeafe; color: #1e40af; }
        .status-confirmed { background-color: #dcfce7; color: #166534; }
        .status-completed { background-color: #f3f4f6; color: #374151; }
        .status-cancelled { background-color: #fee2e2; color: #991b1b; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Reporte de Citas</div>
        <div class="subtitle">
            @if($professional)
                Profesional: {{ $professional->name }}
            @else
                Profesional: Todos
            @endif
            <br>
            Rango: {{ $startDate ?? 'Inicio' }} - {{ $endDate ?? 'Fin' }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="18%">Fecha/Hora</th>
                <th width="25%">Paciente</th>
                <th width="20%">Profesional</th>
                <th width="15%">Estado</th>
                <th width="22%">Notas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($appointments as $appointment)
            <tr>
                <td>{{ \Carbon\Carbon::parse($appointment->start_at)->format('d/m/Y H:i') }}</td>
                <td>{{ $appointment->client->name ?? 'N/A' }}</td>
                <td>{{ $appointment->professional->name ?? 'N/A' }}</td>
                <td>
                    <span class="status-badge status-{{ $appointment->status }}">
                        {{ $appointment->status }}
                    </span>
                </td>
                <td>{{ $appointment->notes }}</td>
            </tr>
            @endforeach
            @if($appointments->isEmpty())
            <tr>
                <td colspan="5" style="text-align: center; padding: 30px;">No se encontraron citas para los criterios seleccionados.</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        Generado el {{ now()->format('d/m/Y H:i') }} | Página 1
    </div>
</body>
</html>
