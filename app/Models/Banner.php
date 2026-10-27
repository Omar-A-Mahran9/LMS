<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    // Schedules are entered and compared in Egypt time, same as lives
    public const TIMEZONE = 'Africa/Cairo';

    public const PLACEMENTS = [
        'top_bar'      => 'Top announcement bar',
        'popup'        => 'Popup',
        'home_top'     => 'Home - under the hero',
        'home_middle'  => 'Home - middle',
        'home_bottom'  => 'Home - bottom',
        'courses_page' => 'Courses page',
    ];

    public const AUDIENCES = [
        'all'      => 'Everyone',
        'guests'   => 'Visitors only (not logged in)',
        'students' => 'Logged in students only',
    ];

    protected $guarded = [];

    protected $appends = ['title', 'full_image_path', 'full_mobile_image_path', 'schedule_status'];

    protected $casts = [
        'open_in_new_tab' => 'boolean',
        'show_countdown'  => 'boolean',
        'is_active'       => 'boolean',
        'created_at'      => 'date:Y-m-d',
        'updated_at'      => 'date:Y-m-d',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function localized(string $field): ?string
    {
        $value = $this->attributes[$field . '_' . app()->getLocale()] ?? null;

        // English fields are optional: fall back to Arabic
        return $value ?: ($this->attributes[$field . '_ar'] ?? null);
    }

    public function getTitleAttribute()
    {
        return $this->localized('title');
    }

    public function getFullImagePathAttribute()
    {
        return $this->image ? getImagePathFromDirectory($this->image, 'Banners', 'default.svg') : null;
    }

    public function getFullMobileImagePathAttribute()
    {
        return $this->mobile_image ? getImagePathFromDirectory($this->mobile_image, 'Banners', 'default.svg') : null;
    }

    public static function nowLocal(): string
    {
        return now(self::TIMEZONE)->format('Y-m-d H:i:s');
    }

    /** scheduled | running | expired | disabled (for the dashboard list) */
    public function getScheduleStatusAttribute(): string
    {
        $now = self::nowLocal();

        if (!$this->is_active) return 'disabled';
        if ($this->starts_at && $this->starts_at > $now) return 'scheduled';
        if ($this->ends_at && $this->ends_at < $now) return 'expired';

        return 'running';
    }

    /** ISO date with the Cairo offset, so the website countdown is right in any timezone */
    public static function toIso(?string $value): ?string
    {
        return $value ? Carbon::parse($value, self::TIMEZONE)->toIso8601String() : null;
    }

    /** Active and inside its start / end window right now */
    public function scopeLive(Builder $query): Builder
    {
        $now = self::nowLocal();

        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now));
    }

    /** Only what this visitor should see (null student = guest) */
    public function scopeVisibleTo(Builder $query, ?Student $student): Builder
    {
        $query->whereIn('audience', $student ? ['all', 'students'] : ['all', 'guests']);

        // Grade-targeted banners only reach students of that grade
        return $query->where(function ($q) use ($student) {
            $q->whereNull('category_id');

            if ($student?->category_id) {
                $q->orWhere('category_id', $student->category_id);
            }
        });
    }
}
