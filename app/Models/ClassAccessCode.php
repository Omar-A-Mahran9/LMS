<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassAccessCode extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['status'];
    protected $casts   = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d',
        'is_active'  => 'boolean',
        'single_use' => 'boolean',
    ];

    public const STATUSES = ['unused', 'used', 'exhausted', 'inactive'];

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

    /** How many uses are allowed in total (null = unlimited). */
    public function getMaxUsesAttribute(): ?int
    {
        return $this->single_use ? 1 : $this->usage_limit;
    }

    /** unused | used (still has uses left) | exhausted | inactive */
    public function getStatusAttribute(): string
    {
        if (!$this->is_active) return 'inactive';
        if (!is_null($this->max_uses) && $this->used_count >= $this->max_uses) return 'exhausted';

        return $this->used_count > 0 ? 'used' : 'unused';
    }

    /** Same rules as getStatusAttribute, in SQL */
    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        $exhausted = fn ($q) => $q->where(fn ($q) => $q->where('single_use', 1)->where('used_count', '>=', 1))
            ->orWhere(fn ($q) => $q->whereNotNull('usage_limit')->whereColumn('used_count', '>=', 'usage_limit'));

        return match ($status) {
            'inactive'  => $query->where('is_active', 0),
            'exhausted' => $query->where('is_active', 1)->where($exhausted),
            'used'      => $query->where('is_active', 1)->where('used_count', '>', 0)->whereNot($exhausted),
            'unused'    => $query->where('is_active', 1)->where('used_count', 0),
            default     => $query,
        };
    }


}
