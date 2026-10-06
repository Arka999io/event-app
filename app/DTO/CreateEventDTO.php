<?php

namespace App\DTO;

use App\Enums\GenderPreference;
use Carbon\CarbonImmutable;

readonly class CreateEventDTO
{
    public function __construct(
        public int $categoryId,
        public string $title,
        public string $description,
        public CarbonImmutable $startsAt,
        public ?CarbonImmutable $endsAt,
        public ?string $meetingAddress,
        public string $venueAddress,
        public ?int $minAge,
        public ?int $maxAge,
        public GenderPreference $genderPreference,
        public int $spotsTotal,
    ) {}
}
