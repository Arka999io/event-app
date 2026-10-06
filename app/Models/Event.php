<?php

namespace App\Models;

use App\Enums\EventStatus;
use App\Enums\GenderPreference;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;

/**
 * @property EventStatus $status
 * @property GenderPreference $gender_preference
 * @property Carbon $starts_at
 * @property Carbon|null $ends_at
 * @property Carbon|null $published_at
 */
#[Fillable([
    'organizer_id',
    'category_id',
    'title',
    'description',
    'starts_at',
    'ends_at',
    'meeting_address',
    'venue_address',
    'min_age',
    'max_age',
    'gender_preference',
    'spots_total',
    'spots_taken',
    'status',
    'published_at',
])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<Application, $this>
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'published_at' => 'datetime',
            'gender_preference' => GenderPreference::class,
            'status' => EventStatus::class,
        ];
    }

    /**
     * @return HasMany<Conversation, $this>
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * @return MorphMany<Photo, $this>
     */
    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable')
            ->orderBy('sort_order');
    }

    /**
     * @return MorphOne<Photo, $this>
     */
    public function coverPhoto(): MorphOne
    {
        return $this->morphOne(Photo::class, 'photoable')
            ->ofMany('sort_order', 'min');
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * @param  Builder<Event>  $query
     * @return Builder<Event>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query
            ->where('organizer_id', '!=', $user->id)
            ->whereNotExists(function ($subQuery) use ($user) {
                $subQuery
                    ->selectRaw('1')
                    ->from('blocks')
                    ->where(function ($query) use ($user) {
                        $query
                            ->whereColumn('blocks.blocker_id', 'events.organizer_id')
                            ->where('blocks.blocked_id', $user->id);
                    })
                    ->orWhere(function ($query) use ($user) {
                        $query
                            ->where('blocks.blocker_id', $user->id)
                            ->whereColumn('blocks.blocked_id', 'events.organizer_id');
                    });
            });
    }

    /**
     * @return MorphMany<Report, $this>
     */
    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }
}
