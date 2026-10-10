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

    /**
     * Count one use, atomically: the limits are re-checked in the UPDATE itself so two requests
     * at the same moment can't both use the last remaining use. False when nothing is left.
     */
    public function consumeUse(): bool
    {
        $consumed = static::whereKey($this->getKey())
            ->where('is_active', 1)
            ->where(fn ($q) => $q->where('single_use', 0)->orWhere('used_count', '<', 1))
            ->where(fn ($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'))
            ->increment('used_count') > 0;

        if ($consumed) {
            $this->used_count++;
        }

        return $consumed;
    }
public function logs()
{
    return $this->hasMany(ClassAccessLog::class, 'access_code_id');
}


}
