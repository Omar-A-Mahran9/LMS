<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeWorkQuestion extends Model
{
    use HasFactory;
    protected $table="home_work_questions";
    protected $guarded = [];
    protected $appends = ['question'];
    protected $casts   = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d',
    ];

       public function getQuestionAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->question_ar : $this->question_en;
    }

    public function homework()
    {
        return $this->belongsTo(HomeWork::class ,'home_work_id');
    }

    public function answers()
    {
        return $this->hasMany(HomeWorkAnswer::class, 'home_work_question_id');
    }

}
