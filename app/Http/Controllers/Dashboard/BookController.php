<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreBookRequest;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
public function index(Request $request)
{
    $this->authorize('view_books');

    if ($request->ajax()) {
        // Return JSON data for AJAX requests
        return response()->json(getModelData(model: new Book()));
    } else {
        // Return the main view with data
        return view('dashboard.books.index');
    }
}

public function store(StoreBookRequest $request)
{
    $this->authorize('create_books');

     $data = $request->validated();
    // Handle image uploads
    if ($request->hasFile('image')) {
        $data['image'] = uploadImageToDirectory($request->file('image'), 'books');
    }
    if ($request->hasFile('attachment')) {
        $validated['attachment'] = uploadAttachmentToDirectory($request->file('attachment'), 'books');
        dd( $validated['attachment']);
    }
    // If course is free, set price to 0
    if ($request->filled('is_free') && $request->boolean('is_free')) {
        $data['price'] = 0;
    }

    // If course doesn't have a discount, remove discount field
    if (!$request->boolean('have_discount')) {
        $data['discount_percentage'] = null;
    }

    // Handle boolean flags
    $data['is_free'] = $request->boolean('is_free');
    $data['have_discount'] = $request->boolean('have_discount');


    // Create course
     Book::create($data);


}

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book)
    {
        //
    }

public function destroy( $id)
{
    $this->authorize('delete_books');
    $books=Book::find($id);
    // Optionally delete the associated image file
    if ($books->image) {
        deleteImageFromDirectory($books->image, 'books'); // This should be your helper function to delete a file
    }

    $books->delete();

    return response()->json([
        'status' => true,
        'message' => __('Course class deleted successfully.'),
    ]);
}

}
