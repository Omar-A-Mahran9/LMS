<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;
      protected $guarded = [];
    protected $appends = [];
    protected $casts   = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d',
    ];
    protected $table = 'course_student'; // This is the pivot table name


    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function getStatusLabelAttribute()
    {
        return [
            'approved' => __('مقبول'),
            'pending' => __('قيد الانتظار'),
            'rejected' => __('مرفوض'),
        ][$this->status] ?? null;
    }

    public function getPaymentTypeLabelAttribute()
    {
        return [
            'wallet_transfer' => __('تحويل من المحفظة'),
            'pay_in_center' => __('الدفع في المركز'),
            'contact_with_support' => __('التواصل مع الدعم'),
        ][$this->payment_type] ?? null;
    }
}
