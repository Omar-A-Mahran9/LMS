<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookOrder extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['title','description','note','full_image_path','full_attachment_path'];
    protected $casts   = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d',

    ];

    // Relationship
    public function book()
    {
        return $this->belongsTo(Book::class);
    }
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    // Accessors
    public function getTitleAttribute()
    {
        $book = $this->book;
        return $book ? ['en' => $book->title_en, 'ar' => $book->title_ar] : null;
    }

    public function getDescriptionAttribute()
    {
        $book = $this->book;
        return $book ? ['en' => $book->description_en, 'ar' => $book->description_ar] : null;
    }

    public function getNoteAttribute()
    {
        $book = $this->book;
        return $book ? ['en' => $book->note_en, 'ar' => $book->note_ar] : null;
    }

    public function getFullImagePathAttribute()
    {
        return $this->book && $this->book->image
            ? asset('uploads/books/' . $this->book->image)
            : asset('assets/no-image.png');
    }

    public function getFullAttachmentPathAttribute()
    {
        return $this->book && $this->book->attachment
            ? asset('uploads/books/attachments/' . $this->book->attachment)
            : null;
    }

}
