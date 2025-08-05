<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassAccessCode extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = [];
    protected $casts   = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d',
    ];

        public function class()
    {
        return $this->belongsTo(CourseClass::class, 'class_id');
    }

    public function canBeUsed(): bool
    {
        if (!$this->is_active) return false;
        if ($this->single_use && $this->used_count >= 1) return false;
        if (!is_null($this->usage_limit) && $this->used_count >= $this->usage_limit) return false;
        return true;
    }
public function logs()
{
    return $this->hasMany(ClassAccessLog::class, 'access_code_id');
}

}
