<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Appointment;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportingController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->input('professional_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $appointments = Appointment::with(['client', 'professional'])
            ->when($userId, fn($q) => $q->where('professional_id', $userId))
            ->when($startDate, fn($q) => $q->whereDate('start_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('start_at', '<=', $endDate))
            ->orderBy('start_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Reports/Index', [
            'appointments' => $appointments,
            'professionals' => User::whereIn('role', ['professional', 'coordinator', 'admin'])->orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['professional_id', 'start_date', 'end_date']),
        ]);
    }

    public function dailyPdf(Request $request)
    {
        $userId = $request->input('professional_id', auth()->id());
        $dateStr = $request->input('date', today()->toDateString());
        $date = Carbon::parse($dateStr);

        $user = User::findOrFail($userId);
        $appointments = Appointment::with('client')
            ->where('professional_id', $userId)
            ->whereDate('start_at', $date)
            ->orderBy('start_at')
            ->get();

        $pdf = Pdf::loadView('reports.daily', [
            'user' => $user,
            'date' => $date->format('d/m/Y'),
            'appointments' => $appointments
        ]);

        return $pdf->download("agenda-diaria-{$user->name}-{$dateStr}.pdf");
    }

    public function weeklyPdf(Request $request)
    {
        $userId = $request->input('professional_id', auth()->id());
        $dateStr = $request->input('date', today()->toDateString());
        $date = Carbon::parse($dateStr);
        $startOfWeek = $date->copy()->startOfWeek();
        $endOfWeek = $date->copy()->endOfWeek();

        $user = User::findOrFail($userId);
        $appointments = Appointment::with('client')
            ->where('professional_id', $userId)
            ->whereBetween('start_at', [$startOfWeek->startOfDay(), $endOfWeek->endOfDay()])
            ->orderBy('start_at')
            ->get();

        $pdf = Pdf::loadView('reports.weekly', [
            'user' => $user,
            'startDate' => $startOfWeek->format('d/m/Y'),
            'endDate' => $endOfWeek->format('d/m/Y'),
            'appointments' => $appointments
        ]);

        return $pdf->download("agenda-semanal-{$user->name}-{$startOfWeek->toDateString()}.pdf");
    }
}
