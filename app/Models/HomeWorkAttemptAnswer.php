<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeWorkAttemptAnswer extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = [];
    protected $casts   = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d',
    ];

    public function attempt()
    {
        return $this->belongsTo(HomeWorkAttempt::class);
    }

    public function question()
    {
        return $this->belongsTo(HomeWorkQuestion::class);
    }

    public function answer()
    {
        return $this->belongsTo(HomeWorkAnswer::class, 'homework_answer_id');
    }
}
