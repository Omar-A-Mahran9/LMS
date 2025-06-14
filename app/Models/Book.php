<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['title','description','note','full_image_path','full_attachment_path','is_booked','request_status','payment_type'];
    protected $casts   = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d',
    ];

    public function getTitleAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->title_ar : $this->title_en;
    }


    public function getDescriptionAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->description_ar : $this->description_en;
    }
     public function getNoteAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->note_ar : $this->note_en;
    }
        public function getFullImagePathAttribute()
    {
        return asset(getImagePathFromDirectory($this->image, 'Books', 'default.svg'));
    }

        public function getFullAttachmentPathAttribute()
    {
        return getAttachmentPathFromDirectory($this->attachment, 'books');
    }

    public function orders()
    {
        return $this->hasMany(BookOrder::class);
    }
public function getIsBookedAttribute()
{
    if (!auth('api')->check()) return false;

    return $this->students()
        ->where('student_id', auth('api')->id())
        ->reorder() // remove orderBy to avoid ambiguous column inside exists()
        ->exists();
}

public function students()
{
    return $this->belongsToMany(Student::class, 'book_orders')
                ->withPivot('payment_type', 'status')
                ->withTimestamps();
}

public function getPaymentTypeAttribute()
{
    if (!auth('api')->check()) return null;

    $studentId = auth('api')->id();

    $order = $this->orders()
        ->where('student_id', $studentId)
        ->latest('id')
        ->first();

    return $order?->payment_type;
}

public function getRequestStatusAttribute()
{
    if (!auth('api')->check()) return null;

    $studentId = auth('api')->id();

    $order = $this->orders()
        ->where('student_id', $studentId)
        ->latest('id')
        ->first();

    return $order?->status;
}


}
