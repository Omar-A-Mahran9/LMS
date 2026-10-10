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

    public function logs()
    {
        return $this->hasMany(
            ClassAccessLog::class,
            'access_code_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

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
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
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
