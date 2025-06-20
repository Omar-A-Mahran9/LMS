<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookOrder;
use Illuminate\Http\Request;

class BookOrderController extends Controller
{

public function store(Request $request)
{
    $isLoggedIn = auth('api')->check() && auth('api')->user();

    $rules = [
        'book_id'      => 'required|exists:books,id',
        'quantity'     => 'required|integer|min:1',
        'payment_type' => 'required|in:wallet_transfer,pay_in_center,contact_with_support',
        'address'      => 'required|string',
    ];

    if (!$isLoggedIn) {
        $rules['name']  = 'required|string|max:255';
        $rules['email'] = 'required|email|max:255';
        $rules['phone'] = 'required|string|max:20';
    }

    $validated = $request->validate($rules);

    // Fetch book price
    $book = Book::findOrFail($validated['book_id']);
    $quantity = $validated['quantity'];
    $price = $book->price;
    $totalCost = $price * $quantity;

    // If user is logged in, override personal info and set student_id
    if ($isLoggedIn) {
        $user = auth('api')->user();

        // ✅ Check if the user already has a pending order for the same book
        $existingOrder = BookOrder::where('student_id', $user->id)
            ->where('book_id', $validated['book_id'])
            ->where('status', 'pending') // adjust if your status column is different
            ->first();

        if ($existingOrder) {
            return $this->failure(__('You already have a pending order for this book. Please wait for it to be processed before ordering again.')); // Conflict
        }

        $validated['student_id'] = $user->id;
        $validated['name']       = $user->first_name.' '.$user->last_name;
        $validated['email']      = $user->email;
        $validated['phone']      = $user->phone;
    }

    // Set total cost
    $validated['total_cost'] = $totalCost;

    // Set default status if needed
    $validated['status'] = 'pending';

    $order = BookOrder::create($validated);

    return response()->json([
        'success' => true,
        'message' => __('Book order placed successfully.'),
        'data' => $order
    ]);
}


public function changeStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:pending,approved,rejected'
    ]);

    $enrollment = BookOrder::findOrFail($id);
    $enrollment->status = $request->status;
    $enrollment->save();

    return response()->json([
        'success' => true,
        'message' => __('Status updated successfully.'),
    ]);
}

}
