<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agenda Diaria - {{ $user->name }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #1e88e5; padding-bottom: 10px; margin-bottom: 20px; }
        .title { color: #1e88e5; font-size: 24px; margin-bottom: 5px; }
        .subtitle { font-size: 16px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #f2f2f2; text-align: left; padding: 10px; border-bottom: 1px solid #ddd; font-size: 14px; }
        td { padding: 10px; border-bottom: 1px solid #eee; font-size: 13px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Agenda Diaria</div>
        <div class="subtitle">Profesional: {{ $user->name }} | Fecha: {{ $date }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="15%">Hora</th>
                <th width="35%">Paciente</th>
                <th width="20%">Estado</th>
                <th width="30%">Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($appointments as $appointment)
            <tr>
                <td>{{ \Carbon\Carbon::parse($appointment->start_at)->format('H:i') }}</td>
                <td>{{ $appointment->client->name ?? 'N/A' }}</td>
                <td>{{ $appointment->status }}</td>
                <td>{{ $appointment->notes }}</td>
            </tr>
            @endforeach
            @if($appointments->isEmpty())
            <tr>
                <td colspan="4" style="text-align: center; padding: 30px;">No hay citas programadas para este día.</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        Generado el {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
