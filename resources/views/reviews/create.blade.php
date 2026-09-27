<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Оставить отзыв
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                <div>
                    <div class="text-sm text-gray-500">
                        Мероприятие
                    </div>

                    <div class="mt-1 font-semibold text-gray-900">
                        {{ $event->title }}
                    </div>
                </div>

                <div class="mt-5">
                    <div class="text-sm text-gray-500">
                        Отзыв о пользователе
                    </div>

                    <div class="mt-1 font-semibold text-gray-900">
                        {{ $subject->profile?->first_name
                            ?? $subject->name
                        }}

                        @if ($subject->profile?->last_name)
                            {{ $subject->profile->last_name }}
                        @endif
                    </div>
                </div>

                @if ($errors->has('review'))
                    <div
                        class="mt-6 rounded-md border border-red-200
                               bg-red-50 p-4 text-sm text-red-700"
                    >
                        {{ $errors->first('review') }}
                    </div>
                @endif

                <form
                    method="POST"
                    action="{{ route(
                        'reviews.store',
                        [$event, $subject]
                    ) }}"
                    class="mt-8 space-y-6"
                >
                    @csrf

                    <div>
                        <x-input-label
                            for="rating"
                            value="Оценка"
                        />

                        <select
                            id="rating"
                            name="rating"
                            required
                            class="mt-1 block w-full rounded-md
                                   border-gray-300 shadow-sm
                                   focus:border-indigo-500
                                   focus:ring-indigo-500"
                        >
                            <option value="">
                                Выберите оценку
                            </option>

                            @for ($rating = 5; $rating >= 1; $rating--)
                                <option
                                    value="{{ $rating }}"
                                    @selected(
                                        (int) old('rating') === $rating
                                    )
                                >
                                    {{ str_repeat('★', $rating) }}
                                    {{ $rating }}/5
                                </option>
                            @endfor
                        </select>

                        <x-input-error
                            class="mt-2"
                            :messages="$errors->get('rating')"
                        />
                    </div>

                    <div>
                        <x-input-label
                            for="comment"
                            value="Комментарий"
                        />

                        <textarea
                            id="comment"
                            name="comment"
                            rows="6"
                            maxlength="2000"
                            class="mt-1 block w-full rounded-md
                                   border-gray-300 shadow-sm
                                   focus:border-indigo-500
                                   focus:ring-indigo-500"
                            placeholder="Расскажите о вашем опыте..."
                        >{{ old('comment') }}</textarea>

                        <div class="mt-1 text-xs text-gray-500">
                            Необязательно. Максимум 2000 символов.
                        </div>

                        <x-input-error
                            class="mt-2"
                            :messages="$errors->get('comment')"
                        />
                    </div>

                    <div class="flex items-center justify-end gap-4">
                        <a
                            href="{{ route(
                                'events.show',
                                $event
                            ) }}"
                            class="text-sm text-gray-600
                                   hover:text-gray-900"
                        >
                            Отмена
                        </a>

                        <x-primary-button>
                            Опубликовать отзыв
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>