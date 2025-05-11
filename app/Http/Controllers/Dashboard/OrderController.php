<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\City;
use App\Models\Order;
use App\Models\Vendor;
use App\Models\HistoryOrder;
use App\Services\OTOService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ProductSpecification;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{


    public function index(Request $request)
    {
        $this->authorize('view_orders');

        if ($request->ajax()) {
            return response(getModelData(
                model: new Order(),
                relations: [
                    'customer' => ['id', 'full_name', 'phone'],
                 ]
            ));
        }

        return view("dashboard.orders.index");
    }

    public function show(Order $order)
    {
        // Authorize the action
        $this->authorize('show_orders');

        // Load the necessary relationships for the order
        $order->load([
            'customer',            // Load customer data
            'addonServices',       // Load related addon services (with count)
            'city'                 // Load the city for the order
        ]);

        // Calculate total price (assuming each addon service has a price attribute)
        $totalPrice = $order->addonServices->sum(function ($addonService) {
            return $addonService->price * $addonService->pivot->count;
        });

        // Organize data for the view
        $addonServices = $order->addonServices->map(function ($addonService) {
            return [
                'service_name' => $addonService->name,
                'count' => $addonService->pivot->count,
                'price' => $addonService->price, // Assuming addon_service has a price field
                'total' => $addonService->price * $addonService->pivot->count, // Calculate total for this service
            ];
        });

        // Return the view with the order data and addon services
        $paymentDetails = null;

        if ($order->payment_id) {
            try {
                $response = Http::withBasicAuth(env('MOYASAR_SECRET_KEY'), '')
                    ->get("https://api.moyasar.com/v1/payments/{$order->payment_id}");

                if ($response->ok()) {
                    $paymentDetails = $response->json(); // entire response
                } else {
                    \Log::warning("Failed to fetch Moyasar payment for order {$order->id}: " . $response->body());
                }
            } catch (\Exception $e) {
                \Log::error("Moyasar API error: " . $e->getMessage());
            }
        }


        return view('dashboard.orders.show', compact('order', 'addonServices', 'totalPrice', 'paymentDetails'));
    }



}
