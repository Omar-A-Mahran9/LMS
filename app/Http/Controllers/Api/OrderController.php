<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Models\Order;

use App\Models\Customer;

use App\Services\OTOService;
use Illuminate\Http\Request;
use App\Services\TapPaymentService;
use App\Http\Controllers\Controller;
use App\Traits\WebNotificationsTrait;


use App\Http\Requests\Api\OrderRequest;
use App\Http\Requests\Api\OrderRequestt;
use App\Mail\OrderConfirmationMail;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{

    public function preCreateOrder(OrderRequestt $request)
    {
        $data = $request->validated();

        if (empty($data['name']) || empty($data['phone'])) {
            return response()->json(['error' => 'Name and Phone are required'], 422);
        }

        $existingCustomer = Customer::where(function ($query) use ($data) {
            $query->where('phone', $data['phone']);
            if (!empty($data['email'])) {
                $query->orWhere('email', $data['email']);
            }
        })->first();

        $customer = $existingCustomer ?? Customer::create([
            'full_name'  => $data['name'],
            'phone'      => $data['phone'],
            'email'      => $data['email'] ?? null,
            'block_flag' => 0,
        ]);

        $otp = rand(1000, 9999);

        $order = Order::create([
            'customer_id'  => $customer->id,
            'city_id'      => $data['city_id'],
            'address'      => $data['address'],
            'date'         => $data['date'],
            'time'         => $data['time'],
            'payment_type' => $data['payment_type'] ?? 'cash',
            'status'       => OrderStatus::pending->value,
            'otp'          => $otp,
            'validated_at' => null,
        ]);

        // Attach multiple addon services with counts
        foreach ($data['services'] as $service) {
            $order->addonServices()->attach($service['id'], ['count' => $service['count']]);
        }

        // Optionally send OTP here

        return $this->success([
             'order' =>[
               'order_id'=>$order->id,
               'phone'=>$order->customer->phone,

               'otp'   => $otp,             ],

        ]);
    }

    public function createOrder(OrderRequest $request)
    {
        $data = $request->validated();

        // Step 5: Verify OTP against order ID
        $order = Order::where('id', $request->order_id) // Verify using order_id
                      ->where('otp', $request->otp) // OTP validation
                      ->whereNull('validated_at') // Ensure it’s not already validated
                      ->first();

        if (!$order) {
            return response()->json(['error' => 'Invalid or expired OTP'], 422);
        }

        // Mark the order as validated
        $order->update([
            'validated_at' => now(),
            'otp' => null,

            'status'       => OrderStatus::approved->value
        ]);

        return $this->success([
            'message' => 'Order successfully confirmed',
            'order'   => $order
        ]);
    }


    public function handleStep(OrderRequest $request, $step)
    {
        $validated = $request->validated();

        // You can store the validated data in session, cache, or a temporary table as needed
        session()->put("order_step_$step", $validated);

        return $this->success(['step' => $step, 'data' => $validated]);
    }

}
