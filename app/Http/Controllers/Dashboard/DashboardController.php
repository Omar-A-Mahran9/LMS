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
            'monthlyEarnings'
        ));
    }


}
