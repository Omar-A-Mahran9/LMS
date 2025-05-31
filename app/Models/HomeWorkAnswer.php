<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeWorkAnswer extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['answer'];
    protected $casts   = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d',
    ];

    public function question()
    {
        return $this->belongsTo(HomeWorkQuestion::class, 'home_work_question_id');
    }

    public function getAnswerAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->title_ar : $this->title_en;
    }

}
