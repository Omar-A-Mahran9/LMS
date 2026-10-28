<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Live extends Model
{
    use HasFactory;

    // The dashboard enters the live times in Egypt time, while the app runs in UTC
    public const TIMEZONE = 'Africa/Cairo';

    // Students can join a little before the start (Zoom waiting room / YouTube countdown)
    public const JOIN_EARLY_MINUTES = 15;

    public const PLATFORMS = ['youtube', 'zoom', 'google_meet', 'facebook', 'twitch', 'other'];

    public const STATUS_UPCOMING = 'upcoming';
    public const STATUS_STARTING_SOON = 'starting_soon';
    public const STATUS_LIVE = 'live';
    public const STATUS_ENDED = 'ended';

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d',
        'notified_at' => 'datetime',
        'is_public' => 'boolean',
    ];

    protected $appends = [
        'title',
         'description',
         'full_image_path',
         'status',
    ];

    protected $hidden = ['notified_at'];

    protected static function booted(): void
    {
        // Tell the students about the live once (created active, or activated later).
        // A notification problem must never block saving the live.
        $notify = function (self $model) {
            try {
                \App\Services\StudentNotifier::newLive($model);
            } catch (\Throwable $e) {
                report($e);
            }
        };

        static::created(fn(self $model) => $model->is_active && $notify($model));
        static::updated(fn(self $model) => $model->wasChanged('is_active') && $model->is_active && $notify($model));
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function class()
    {
        return $this->belongsTo(CourseClass::class, 'class_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /** Current time as stored in the lives table (Egypt time, no timezone). */
    public static function localNow(): string
    {
        return now(self::TIMEZONE)->format('Y-m-d H:i:s');
    }

    /** Active lives in an active course (and an active class, when the live belongs to one). */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->whereHas('course', fn($q) => $q->where('is_active', 1))
            ->where(fn($q) => $q->whereNull('class_id')->orWhereHas('class', fn($c) => $c->where('is_active', 1)));
    }

    /** Not finished yet: on air now or coming later. */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('end_time', '>=', self::localNow());
    }

    /** On air right now. */
    public function scopeOnAir(Builder $query): Builder
    {
        $now = self::localNow();

        return $query->where('start_time', '<=', $now)->where('end_time', '>=', $now);
    }

    /** Lives on air first, then the closest upcoming ones. */
    public function scopeSoonestFirst(Builder $query): Builder
    {
        return $query->orderBy('start_time');
    }

    public function scopeForCategory(Builder $query, $categoryId): Builder
    {
        return $query->whereHas('course', function ($q) use ($categoryId) {
            $q->where('category_id', $categoryId)
                // Category's "only parents" scope would hide the sub-categories here
                ->orWhereHas('subCategories', fn($c) => $c->withoutGlobalScopes()->where('categories.id', $categoryId));
        });
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

    public function getFullImagePathAttribute()
    {
        if (!$this->image && $this->class) {
            return $this->class->full_image_path;
        }

        return asset(getImagePathFromDirectory($this->image, 'Lives', 'default.svg'));
    }

    public function startsAt(): ?Carbon
    {
        return $this->start_time ? Carbon::parse($this->start_time, self::TIMEZONE) : null;
    }

    public function endsAt(): ?Carbon
    {
        return $this->end_time ? Carbon::parse($this->end_time, self::TIMEZONE) : null;
    }

    /**
     * When the links open. Counted in UTC: minutes taken on Cairo's wall clock would jump
     * an extra hour on the night the clocks change.
     */
    public function joinOpensAt(): ?Carbon
    {
        return $this->startsAt()?->copy()->utc()->subMinutes(self::JOIN_EARLY_MINUTES)->setTimezone(self::TIMEZONE);
    }

    public function getStatusAttribute(): string
    {
        $now = now();
        $start = $this->startsAt();
        $end = $this->endsAt();

        if ($end && $now->greaterThan($end)) {
            return self::STATUS_ENDED;
        }
        if (!$start || $now->greaterThanOrEqualTo($start)) {
            return self::STATUS_LIVE;
        }
        if ($now->greaterThanOrEqualTo($this->joinOpensAt())) {
            return self::STATUS_STARTING_SOON;
        }

        return self::STATUS_UPCOMING;
    }

    /** The student can open the stream / meeting now. */
    public function canJoin(): bool
    {
        return $this->is_active && in_array($this->status, [self::STATUS_STARTING_SOON, self::STATUS_LIVE]);
    }

    public function youtubeId(): ?string
    {
        preg_match(
            '/(?:youtube\.com\/(?:watch\?v=|embed\/|live\/|v\/|shorts\/|.*[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/',
            (string) $this->embed_url,
            $matches
        );

        return $matches[1] ?? null;
    }

    /**
     * Link that plays inside the class page, or null when the platform can't be embedded
     * (Zoom / Google Meet open in their own app or tab).
     */
    public function playerUrl(): ?string
    {
        return match ($this->platform) {
            'youtube' => ($id = $this->youtubeId())
                ? "https://www.youtube.com/embed/{$id}?autoplay=1&rel=0&modestbranding=1"
                : null,
            'facebook' => 'https://www.facebook.com/plugins/video.php?show_text=false&href=' . urlencode($this->embed_url),
            default => null,
        };
    }
}
