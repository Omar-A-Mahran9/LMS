<?php

namespace App\Models;

use App\Http\Scopes\WithoutDefaultRole;
use App\Models\Scopes\SortingScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Builder;


class Role extends Model
{
    use HasFactory;

    protected $guarded = ['abilities'];
    protected $appends = ['name'];
    protected $casts = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d'
    ];

    public static $modules = [
        'admins',

        'sliders',

        'categories',

        'courses',

        'classes',

        'sections',

        'videos',

        'quizzes',

        'homework',

        'home_content',

        'students_rate',

        'CommonQuestion',

        'students',

        'newsletter',

        'books',

        'enrollments',

        'roles',

        'governments',

        'contact_us',

        'settings',

        'orders',

    ];

    protected static function booted()
    {
        static::addGlobalScope(new WithoutDefaultRole());
    }

    public function getNameAttribute()
    {
        return $this->attributes['name_' . app()->getLocale()];
    }

    public function admins()
    {
        return $this->belongsToMany(Admin::class)->withoutGlobalScope(SortingScope::class)->whereNot('id', 2);
    }

    public function abilities()
    {
        return $this->belongsToMany(Ability::class);
    }
}
