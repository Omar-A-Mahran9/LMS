<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\DashboardAnalytics;

class DashboardController extends Controller
{
    /**
     * Every admin lands here; only admins with view_dashboard get the statistics
     * (the others see the welcome screen, and no numbers are sent to their browser).
     */
    public function index(DashboardAnalytics $analytics)
    {
        $stats = auth('admin')->user()?->can('view_dashboard')
            ? $analytics->summary()
            : null;

        return view('welcome', compact('stats'));
    }
}
