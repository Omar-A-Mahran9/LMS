<?php

namespace App\Http\Controllers\Dashboard;


use App\Models\Order;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $orderPlaced = Order::count();
        $earnings = Order::sum('total_price');
        $passengersCount = Student::count();

        $orderStats = [
            'pending' => Order::where('status', OrderStatus::pending)->count(),
            'completed' => Order::where('status', OrderStatus::approved)->count(),
            'canceled' => Order::where('status', OrderStatus::rejected)->count(),
        ];

        $topServices = DB::table('order_addon_service')
            ->select('addon_service_id', DB::raw('SUM(count) as total_usage'))
            ->groupBy('addon_service_id')
            ->orderByDesc('total_usage')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $service = \App\Models\CourseController::find($item->addon_service_id);
                return [
                    'name' => $service?->name ?? 'N/A',
                    'total_usage' => $item->total_usage,
                ];
            });

        $monthlyEarnings = Order::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_price) as total")
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get();

        return view('welcome', compact(
            'orderPlaced',
            'earnings',
            'passengersCount',
            'orderStats',
            'topServices',
            'monthlyEarnings'
        ));
    }


}
