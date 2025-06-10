<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BookOrder;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{


    public function index(Request $request)
    {
        $this->authorize('view_orders');

        if ($request->ajax()) {
            return response(getModelData(
                model: new BookOrder(),
                relations: [
                    'student' => ['id', 'first_name', 'last_name', 'phone'],
                    'book' => ['id', 'title_ar','title_en'],

                 ]
            ));
        }

        return view("dashboard.orders.index");
    }

public function show(BookOrder $order)
{
    // Authorize the action
    $this->authorize('show_orders');

    // Load related models (student and book)
    $order->load(['student', 'book']);

    $totalPrice = $order->book?->price * $order->quantity;



    return view('dashboard.orders.show', [
        'order' => $order,
        'totalPrice' => $totalPrice,
    ]);
}




}
