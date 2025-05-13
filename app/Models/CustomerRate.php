<?php

namespace App\Models;

use App\Models\Scopes\SortingScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerRate extends Model
{
    use HasFactory;
    protected $table = 'customer_rates';
    protected $guarded = [];
    protected $appends = ['full_image_path', 'audio_full_path'];

    protected $casts   = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new SortingScope);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class,'customer_id');
    }

    public function getFullImagePathAttribute()
    {
        return getImagePathFromDirectory($this->image, 'Customer', 'default.svg');
    }
    public function getAudioFullPathAttribute()
    {
        return getAudioPathFromDirectory($this->audio, 'Customer', 'default.mp3');
    }

}
