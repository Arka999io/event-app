<?php

namespace App\Http\Requests;

use App\Enums\GenderPreference;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Event::class) ?? false;
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
}