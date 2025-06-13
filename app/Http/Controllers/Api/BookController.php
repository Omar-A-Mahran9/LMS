<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreBookRequest;
use App\Http\Requests\Dashboard\UpdateBookRequest;
use App\Http\Resources\Api\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
public function index(Request $request)
{
    $perPage = $request->get('per_page', 10); // Default to 10 per page

    $books = Book::where('is_active', 1)->paginate($perPage);

    return $this->successWithPagination('',BookResource::collection($books)->response()->getData(true) );
}


public function show($id)
{
    $book = Book::where('is_active', 1)->findOrFail($id);

    return $this->success('', new BookResource($book)
);
}




}
