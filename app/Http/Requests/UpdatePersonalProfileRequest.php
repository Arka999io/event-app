<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePersonalProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => [
                'required',
                'string',
                'max:100',
            ],

            'last_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'birth_date' => [
                'nullable',
                'date',
                'before:today',
            ],

            'gender' => [
                'nullable',
                'string',
                'in:male,female',
            ],

            'city' => [
                'nullable',
                'string',
                'max:100',
            ],

            'bio' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}
