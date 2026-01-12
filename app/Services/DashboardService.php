<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\User;
use App\Models\BusinessHour;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get summary data for Admin/Coordinator dashboard.
     */
    public function getAdminSummary(): array
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // KPIs
        $kpis = [
            'total_today' => Appointment::whereDate('start_at', $today)->where('status', '!=', 'cancelled')->count(),
            'confirmed_today' => Appointment::whereDate('start_at', $today)->where('status', 'confirmed')->count(),
            'cancelled_today' => Appointment::whereDate('start_at', $today)->where('status', 'cancelled')->count(),
            'no_show_today' => Appointment::whereDate('start_at', $today)->where('status', 'no_show')->count(), // Assuming 'no_show' status exists or future-proof
            'occupancy' => $this->calculateGlobalOccupancy($today),
        ];

        // Today's Appointments (Quick List)
        $appointments = Appointment::with(['client', 'professional'])
            ->whereDate('start_at', $today)
            ->orderBy('start_at')
            ->take(10)
            ->get();

        // Professionals Stats
        $professionals = User::whereIn('role', ['professional'])
            ->withCount(['appointments as appointments_today' => function ($query) use ($today) {
                $query->whereDate('start_at', $today)->where('status', '!=', 'cancelled');
            }])
            ->withCount(['appointments as appointments_week' => function ($query) {
                $query->whereBetween('start_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                      ->where('status', '!=', 'cancelled');
            }])
            ->get()
            ->map(function ($user) use ($today) {
                $user->occupancy = $this->calculateUserOccupancy($user->id, $today);
                return $user;
            });

        // Chart Data (Monthly appointments)
        $chartData = Appointment::select(
                DB::raw('COUNT(id) as count'),
                DB::raw('DATE_FORMAT(start_at, "%Y-%m") as month')
            )
            ->whereBetween('start_at', [Carbon::now()->subMonths(6)->startOfMonth(), $endOfMonth])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return [
            'kpis' => $kpis,
            'recent_appointments' => $appointments,
            'professionals' => $professionals,
            'chart_data' => $chartData,
        ];
    }

    /**
     * Get summary data for Professional dashboard.
     */
    public function getProfessionalSummary(int $userId): array
    {
        $today = Carbon::today();
        $now = Carbon::now();

        // KPIs
        $kpis = [
            'today_count' => Appointment::where('professional_id', $userId)->whereDate('start_at', $today)->where('status', '!=', 'cancelled')->count(),
            'week_count' => Appointment::where('professional_id', $userId)->whereBetween('start_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->where('status', '!=', 'cancelled')->count(),
            'month_completed' => Appointment::where('professional_id', $userId)->whereMonth('start_at', Carbon::now()->month)->where('status', 'completed')->count(),
            'occupancy' => $this->calculateUserOccupancy($userId, $today),
        ];

        // Today's Agenda
        $agenda = Appointment::with('client')
            ->where('professional_id', $userId)
            ->whereDate('start_at', $today)
            ->orderBy('start_at')
            ->get();

        // Next Appointment
        $nextAppointment = Appointment::with('client')
            ->where('professional_id', $userId)
            ->where('start_at', '>', $now)
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_at')
            ->first();

        // Recent Clients
        $recentClients = Appointment::with('client')
            ->where('professional_id', $userId)
            ->where('start_at', '<', $now)
            ->orderBy('start_at', 'desc')
            ->take(5)
            ->get()
            ->pluck('client')
            ->unique('id')
            ->values();

        return [
            'kpis' => $kpis,
            'agenda' => $agenda,
            'next_appointment' => $nextAppointment,
            'recent_clients' => $recentClients,
        ];
    }

    private function calculateUserOccupancy(int $userId, Carbon $date): float
    {
        $dayOfWeek = $date->dayOfWeekIso; // 1 (Mon) to 7 (Sun)
        $businessHour = BusinessHour::where('user_id', $userId)->where('day_of_week', $dayOfWeek)->first();

        if (!$businessHour) return 0;

        $start = Carbon::parse($businessHour->start_time);
        $end = Carbon::parse($businessHour->end_time);
        $totalMinutes = $end->diffInMinutes($start);

        if ($totalMinutes <= 0) return 0;

        $bookedMinutes = Appointment::where('professional_id', $userId)
            ->whereDate('start_at', $date)
            ->where('status', '!=', 'cancelled')
            ->get()
            ->sum(function ($a) {
                return Carbon::parse($a->end_at)->diffInMinutes(Carbon::parse($a->start_at));
            });

        return round(($bookedMinutes / $totalMinutes) * 100, 1);
    }

    private function calculateGlobalOccupancy(Carbon $date): float
    {
        $professionals = User::whereIn('role', ['professional', 'coordinator', 'admin'])->pluck('id');
        if ($professionals->isEmpty()) return 0;

        $occupancies = $professionals->map(fn($id) => $this->calculateUserOccupancy($id, $date));
        return round($occupancies->avg(), 1);
    }
}
