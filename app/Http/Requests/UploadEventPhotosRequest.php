<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadEventPhotosRequest extends FormRequest
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
            'photos' => [
                'required',
                'array',
                'min:1',
                'max:8',
            ],

            'photos.*' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'photos.required' => 'Выберите хотя бы одну фотографию.',
            'photos.max' => 'Можно загрузить не более 8 фотографий.',
            'photos.*.image' => 'Каждый файл должен быть изображением.',
            'photos.*.mimes' => 'Разрешены JPG, PNG и WebP.',
            'photos.*.max' => 'Размер одной фотографии не должен превышать 5 МБ.',
        ];
    }
}