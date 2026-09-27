<?php

namespace App\Http\Requests;

use App\Enums\EventStatus;
use App\Enums\GenderPreference;
use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        $event = $this->route('event');

        return $event instanceof Event
            && ($this->user()?->can('update', $event) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'integer',
                'exists:categories,id',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'required',
                'string',
            ],

            'starts_at' => [
                'required',
                'date',
                'after:now',
            ],

            'ends_at' => [
                'nullable',
                'date',
                'after:starts_at',
            ],

            'meeting_address' => [
                'nullable',
                'string',
                'max:255',
            ],

            'venue_address' => [
                'required',
                'string',
                'max:255',
            ],

            'min_age' => [
                'nullable',
                'integer',
                'min:0',
                'max:120',
            ],

            'max_age' => [
                'nullable',
                'integer',
                'min:0',
                'max:120',
                'gte:min_age',
            ],

            'gender_preference' => [
                'required',
                Rule::enum(GenderPreference::class),
            ],

            'spots_total' => [
                'required',
                'integer',
                'min:1',
            ],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $event = $this->route('event');

                if (! $event instanceof Event) {
                    return;
                }

                if (
                    in_array(
                        $event->status,
                        [
                            EventStatus::Completed,
                            EventStatus::Cancelled,
                        ],
                        true
                    )
                ) {
                    $validator->errors()->add(
                        'title',
                        'Завершённое или отменённое мероприятие нельзя редактировать.'
                    );

                    return;
                }

                $spotsTotal = $this->integer('spots_total');

                if ($spotsTotal < $event->spots_taken) {
                    $validator->errors()->add(
                        'spots_total',
                        'Количество мест не может быть меньше числа уже принятых участников.'
                    );
                }
            },
        ];
    }
}