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

        return true;
    }
}
