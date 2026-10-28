<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A bundle activated by a student (also the activation log of the bundle code).
 */
class BundleStudent extends Model
{
    protected $table = 'bundle_student';

    protected $guarded = [];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    public function bundle()
    {
        return $this->belongsTo(Bundle::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function code()
    {
        return $this->belongsTo(BundleAccessCode::class, 'access_code_id');
    }
}
