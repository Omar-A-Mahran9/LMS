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
    use WebNotificationsTrait;
    private $tapPaymentService;
    private $otoService;

    public function __construct(TapPaymentService $tapPaymentService, OTOService $otoService)
    {
        $this->tapPaymentService = $tapPaymentService;
        $this->otoService        = $otoService;
    }

    public function preCreateOrder(OrderRequestt $request)
    {
        $data = $request->validated();

        // Ensure 'name' and 'phone' exist
        if (empty($data['name'])) {
            return response()->json(['error' => 'Name is required'], 422);
        }

        if (empty($data['phone'])) {
            return response()->json(['error' => 'Phone is required'], 422);
        }

        // Find existing customer by phone or email
        $existingCustomer = Customer::where(function ($query) use ($data) {
            $query->where('phone', $data['phone']);
            if (!empty($data['email'])) {
                $query->orWhere('email', $data['email']);
            }
        })->first();

        // Create or reuse customer
        $customer = $existingCustomer ?? Customer::create([
            'full_name'  => $data['name'],
            'phone'      => $data['phone'],
            'email'      => $data['email'] ?? null,
            'block_flag' => 0,
        ]);

        // Generate OTP (4-digit)
        $otp = rand(1000, 9999);

        // Create the order
        $order = Order::create([
            'customer_id'      => $customer->id,
            'city_id'          => $data['city_id'],
            'address'          => $data['address'],
            'date'             => $data['date'],
            'time'             => $data['time'],
            'count'            => $data['count'],
            'payment_type'     => $data['payment_type'] ?? 'cash',
            'addon_service_id' => $data['addon_service_id'],
            'status'           => OrderStatus::pending->value,
            'otp'              => $otp,
            'validated_at'     => null,
        ]);

        // Optionally send OTP via SMS/email service here

        return $this->success([
            'order' => $order,
            'otp'   => $otp, // remove or hide this in production
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
