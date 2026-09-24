<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StudentNotification extends Model
{
    public const TYPE_NEW_CLASS  = 'new_class';
    public const TYPE_NEW_COURSE = 'new_course';
    public const TYPE_MESSAGE    = 'message';
    public const TYPE_SUBSCRIPTION = 'subscription'; // the student's subscription was activated

    public const TARGET_ALL        = 'all';
    public const TARGET_CATEGORIES = 'categories';
    public const TARGET_STUDENTS   = 'students';

    protected $guarded = [];
    protected $appends = ['title', 'body'];
    protected $casts   = [
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
    ];

    public function getTitleAttribute()
    {
        return app()->getLocale() === 'en' && $this->title_en ? $this->title_en : $this->title_ar;
    }

    public function getBodyAttribute()
    {
        return app()->getLocale() === 'en' && $this->body_en ? $this->body_en : $this->body_ar;
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'student_notification_category')->withoutGlobalScopes();
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_notification_student');
    }

    public function readers()
    {
        return $this->belongsToMany(Student::class, 'student_notification_reads')->withPivot('read_at');
    }

    public function sender()
    {
        return $this->belongsTo(Admin::class, 'sent_by');
    }

    /**
     * Notifications a student should see: sent to everyone, to the student's category (grade),
     * or to the student directly. Broadcasts sent before the student registered are skipped.
     */
    public function scopeForStudent(Builder $query, Student $student): Builder
    {
        $registeredAt = $student->getRawOriginal('created_at');
        $since = fn($q) => $registeredAt ? $q->where('created_at', '>=', $registeredAt) : $q;

        return $query->where(function ($q) use ($student, $since) {
            $q->where(function ($q) use ($since) {
                $since($q->where('target', self::TARGET_ALL));
            })->orWhere(function ($q) use ($student, $since) {
                $since($q->where('target', self::TARGET_CATEGORIES))
                    ->whereHas('categories', fn($c) => $c->where('categories.id', $student->category_id));
            })->orWhere(function ($q) use ($student) {
                $q->where('target', self::TARGET_STUDENTS)
                    ->whereHas('students', fn($s) => $s->where('students.id', $student->id));
            });
        });
    }

    /** Adds is_read (0/1) for the given student. */
    public function scopeWithReadState(Builder $query, Student $student): Builder
    {
        return $query->withExists([
            'readers as is_read' => fn($q) => $q->where('students.id', $student->id),
        ]);
    }

    public function scopeUnreadFor(Builder $query, Student $student): Builder
    {
        return $query->whereDoesntHave('readers', fn($q) => $q->where('students.id', $student->id));
    }
}
