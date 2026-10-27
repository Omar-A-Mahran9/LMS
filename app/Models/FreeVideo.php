<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Free video / tip: public to everyone (no login, no subscription).
 */
class FreeVideo extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    protected $appends = ['title', 'description', 'full_image_path', 'youtube_id'];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function category()
    {
        // Category's "only parents" scope would hide the sub-categories here
        return $this->belongsTo(Category::class)->withoutGlobalScopes();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Videos of the grade, plus the ones for all grades. */
    public function scopeForCategory(Builder $query, $categoryId): Builder
    {
        return $query->where(fn($q) => $q->where('category_id', $categoryId)->orWhereNull('category_id'));
    }

    public function scopeSorted(Builder $query, ?string $sort = null): Builder
    {
        return match ($sort) {
            'popular' => $query->orderByDesc('views')->orderByDesc('id'),
            'oldest' => $query->orderBy('id'),
            'newest' => $query->orderByDesc('id'),
            // Dashboard order first, then the newest
            default => $query->orderBy('sort_order')->orderByDesc('id'),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getTitleAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->title_ar : $this->title_en;
    }

    public function getDescriptionAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->description_ar : $this->description_en;
    }

    public function getYoutubeIdAttribute(): ?string
    {
        preg_match(
            '/(?:youtube\.com\/(?:watch\?v=|embed\/|live\/|v\/|shorts\/|.*[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/',
            (string) $this->video_url,
            $matches
        );

        return $matches[1] ?? null;
    }

    public function getFullImagePathAttribute()
    {
        if (!$this->image && $this->youtube_id) {
            return "https://i.ytimg.com/vi/{$this->youtube_id}/hqdefault.jpg";
        }

        return getImagePathFromDirectory($this->image, 'FreeVideos', 'default.svg');
    }
}
