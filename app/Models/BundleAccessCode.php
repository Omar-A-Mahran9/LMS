<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BundleAccessCode extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [

        'is_active' => 'boolean',

        'single_use' => 'boolean',

        'starts_at' => 'datetime',

        'expires_at' => 'datetime',

        'created_at' => 'date:Y-m-d',

        'updated_at' => 'date:Y-m-d',
    ];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function bundle()
    {
        return $this->belongsTo(
            Bundle::class,
            'bundle_id'
        );
    }

    /** Students who activated the bundle with this code (used to read class code logs by mistake). */
    public function logs()
    {
        return $this->hasMany(
            BundleStudent::class,
            'access_code_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /** unused / used / exhausted / inactive: the codes PDF shows it */
    public function getStatusAttribute(): string
    {
        $max = $this->single_use ? 1 : $this->usage_limit;

        return match (true) {
            !$this->is_active => 'inactive',
            $max !== null && $this->used_count >= $max => 'exhausted',
            $this->used_count > 0 => 'used',
            default => 'unused',
        };
    }

    public function canBeUsed(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if (
            $this->single_use &&
            $this->used_count >= 1
        ) {
            return false;
        }

        if (
            !is_null($this->usage_limit) &&
            $this->used_count >= $this->usage_limit
        ) {
            return false;
        }

        // Validity window set when the codes were generated
        return $this->isWithinValidity();
    }

    public function isWithinValidity(): bool
    {
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        return !($this->expires_at && $this->expires_at->isPast());
    }

    /**
     * Count one use, atomically (see ClassAccessCode::consumeUse). False when nothing is left.
     */
    public function consumeUse(): bool
    {
        if (!$this->canBeUsed()) {
            return false;
        }

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
}
