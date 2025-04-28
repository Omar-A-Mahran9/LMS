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
use App\Models\AddonService;
use App\Services\TaqnyatSmsService;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{

    public function preCreateOrder(OrderRequestt $request, TaqnyatSmsService $taqnyat)
    {
        $data = $request->validated();
        if (empty($data['name']) || empty($data['phone'])) {
             return $this->failure( __('Name and Phone are required'));
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
        $totalPrice = 0;
        foreach ($data['services'] as $service) {
            $serviceModel = AddonService::findOrFail($service['id']); // Load service from DB
            if (!is_null($serviceModel->price)) {
                $totalPrice += $serviceModel->price * $service['count'];
            } else {
                $totalPrice += $serviceModel->visiting_price * $service['count'];
            }
                    }

        $otp = rand(1000, 9999);

        $order = Order::create([
            'customer_id'  => $customer->id,
            'city_id'      => $data['city_id'],
            'address'      => $data['address'],
            'date'         => $data['date'],
            'time'         => $data['time'],
            'lat'         => $data['lat'],
            'lng'         => $data['lng'],
            'total_price'  => $totalPrice,
            // 'payment_type' => $data['payment_type'],
            'status'       => OrderStatus::pending->value,
            'otp'          => $otp,
            'validated_at' => null,
        ]);

        // Attach multiple addon services with counts
        foreach ($data['services'] as $service) {
            $order->addonServices()->attach($service['id'], ['count' => $service['count']]);
        }
// Optionally send OTP here
$message = "رمز التحقق الخاص بك هو: $otp";
try {
    $phone = ltrim($customer->phone, '0');
    $phone = '966' . $phone;

    $taqnyat->sendMessage($phone, $message);
} catch (\Exception $e) {
    \Log::error("Taqnyat SMS failed for phone {$customer->phone}: " . $e->getMessage());

    // Return the error message as part of the response
    return $this->failure([
        'error' => 'SMS sending failed',
        'message' => $e->getMessage(),
    ]);
}

return $this->success([
    'order' => [
        'order_id' => $order->id,
        'phone' => $order->customer->phone,
        'otp' => $otp,
    ],
]);

    }


    public function confirmOrderOtp(OrderRequest $request)
    {
        $data = $request->validated();

        $order = Order::where('id', $request->order_id)
                      ->where('otp', $request->otp)
                      ->whereNull('validated_at')
                      ->first();

        if (!$order) {
            return $this->failure(__('Invalid or expired OTP'));
        }

        // Mark the order as validated
        $order->update([
            'validated_at' => now(),
            'otp' => null,
            'status' => OrderStatus::approved->value
        ]);

        return $this->success(
             'otp validate successfully',
              [
                'order_id' => $order->id,
                'total_price' => $order->total_price,
                'phone'    => $order->customer->phone,
            ],
        );
    }


    public function handleStep(OrderRequest $request, $step)
    {
        $validated = $request->validated();

        // If it's step 4 — update payment type (otp must be null & validated_at is set)
        if ($step == 5) {
            $order = Order::where('id', $request->order_id)
                          ->whereNull('otp')
                          ->whereNotNull('validated_at')
                          ->first();

            if (!$order) {
                 return $this->failure( __('Invalid or unauthorized order for payment update'));

            }

            // Update the payment type
            $order->update([
                'payment_type' => $validated['payment_type'],
                'is_paid' => true,

            ]);

            return $this->success([
                'message' => 'Order payment successfully.',
            ]);
        }

        // Store validated data in session, cache, or a temporary table if needed
        session()->put("order_step_$step", $validated);

        return $this->success(['step' => $step, 'data' => $validated]);
    }


}
