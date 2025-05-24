<?php

namespace App\Models;

use App\Models\Scopes\SortingScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new SortingScope);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function customer()
    {
<<<<<<< HEAD
        return $this->belongsTo(Student::class);
=======
        return $this->belongsTo(Studentclass);
>>>>>>> 157b6b9fd352801b60ca819ea76ffd9e68536d9b
    }
}
