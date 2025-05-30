<?php

namespace App\Models;

use App\Models\Scopes\SortingScope;
use App\Traits\SMSTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class Student extends Authenticatable
{
    use HasFactory, HasApiTokens, SMSTrait;

    protected $appends = [ 'full_image_path'];
    protected $guarded = ["password_confirmation"];
    protected $casts   = ['created_at' => 'date:Y-m-d', 'updated_at' => 'date:Y-m-d', 'otp' => 'string'];


    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new SortingScope);
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function getFullImagePathAttribute()
    {
        return asset(getImagePathFromDirectory($this->image, 'Students', "default.svg"));
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function government()
    {
        return $this->belongsTo(Government::class);
    }

     public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

}
