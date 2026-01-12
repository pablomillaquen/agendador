<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        
        if (in_array($user->role, ['admin', 'coordinator'])) {
            $data = $this->dashboardService->getAdminSummary();
        } else {
            $data = $this->dashboardService->getProfessionalSummary($user->id);
        }

        return Inertia::render('Dashboard/Index', [
            'stats' => $data,
            'role' => $user->role,
        ]);
    }
}
