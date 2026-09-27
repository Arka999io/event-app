<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Database\Factories\ApplicationFactory;

/**
 * @property ApplicationStatus $status
 */
#[Fillable([
    'event_id',
    'applicant_id',
    'cover_letter',
    'status',
])]
class Application extends Model
{
    /** @use HasFactory<ApplicationFactory> */
use HasFactory;

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
        ];
    }


}