<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['title','description','note','full_image_path','full_attachment_path'];
    protected $casts   = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d',
    ];

    public function getTitleAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->title_ar : $this->title_en;
    }

    public function getDescriptionAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->description_ar : $this->description_en;
    }
     public function getNoteAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->note_ar : $this->note_en;
    }
        public function getFullImagePathAttribute()
    {
        return asset(getImagePathFromDirectory($this->image, 'Books', 'default.svg'));
    }

        public function getFullAttachmentPathAttribute()
    {
        return getAttachmentPathFromDirectory($this->attachment, 'books');
    }

    public function orders()
{
    return $this->hasMany(BookOrder::class);
}

}
