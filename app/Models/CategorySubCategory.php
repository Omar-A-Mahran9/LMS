<?php

namespace App\Models;

use App\Models\Scopes\SortingScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategorySubCategory extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'categories';  // Define the pivot table name
    protected $guarded = [];
    protected $appends = ['name', 'full_image_path', 'description'];
    protected $casts   = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('onlySubcategories', function (Builder $builder) {
            $builder->whereNotNull('parent_id');
        });

        static::addGlobalScope(new SortingScope);
    }


    public function getNameAttribute()
    {
        return $this->attributes['name_' . app()->getLocale()];
    }

    public function getDescriptionAttribute()
    {
        return $this->attributes['description_' . app()->getLocale()];
    }

    public function getFullImagePathAttribute()
    {
        return asset(getImagePathFromDirectory($this->image, 'Categories', "default.svg"));
    }

    public function categories()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }



}
