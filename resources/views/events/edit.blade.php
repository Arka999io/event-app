<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Редактировать мероприятие
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form
                    method="POST"
                    action="{{ route('events.update', $event) }}"
                    class="p-6 space-y-6"
                >
                    @csrf
                    @method('PATCH')

                    <div>
                        <x-input-label
                            for="title"
                            value="Название"
                        />

                        <x-text-input
                            id="title"
                            name="title"
                            type="text"
                            class="mt-1 block w-full"
                            :value="old('title', $event->title)"
                            required
                            autofocus
                        />

                        <x-input-error
                            class="mt-2"
                            :messages="$errors->get('title')"
                        />
                    </div>

                    <div>
                        <x-input-label
                            for="category_id"
                            value="Категория"
                        />

                        <select
                            id="category_id"
                            name="category_id"
                            class="mt-1 block w-full rounded-md
                                   border-gray-300 shadow-sm"
                            required
                        >
                            <option value="">
                                Выберите категорию
                            </option>

                            @foreach ($categories as $category)
                                <option
                                    value="{{ $category->id }}"
                                    @selected(
                                        (string) old(
                                            'category_id',
                                            $event->category_id
                                        )
                                        === (string) $category->id
                                    )
                                >
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>

                        <x-input-error
                            class="mt-2"
                            :messages="$errors->get('category_id')"
                        />
                    </div>

                    <div>
                        <x-input-label
                            for="description"
                            value="Описание"
                        />

                        <textarea
                            id="description"
                            name="description"
                            rows="6"
                            class="mt-1 block w-full rounded-md
                                   border-gray-300 shadow-sm"
                            required
                        >{{ old('description', $event->description) }}</textarea>

                        <x-input-error
                            class="mt-2"
                            :messages="$errors->get('description')"
                        />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label
                                for="starts_at"
                                value="Начало"
                            />

                            <x-text-input
                                id="starts_at"
                                name="starts_at"
                                type="datetime-local"
                                class="mt-1 block w-full"
                                :value="old(
                                    'starts_at',
                                    $event->starts_at->format('Y-m-d\TH:i')
                                )"
                                required
                            />

                            <x-input-error
                                class="mt-2"
                                :messages="$errors->get('starts_at')"
                            />
                        </div>

                        <div>
                            <x-input-label
                                for="ends_at"
                                value="Окончание"
                            />

                            <x-text-input
                                id="ends_at"
                                name="ends_at"
                                type="datetime-local"
                                class="mt-1 block w-full"
                                :value="old(
                                    'ends_at',
                                    $event->ends_at?->format('Y-m-d\TH:i')
                                )"
                            />

                            <x-input-error
                                class="mt-2"
                                :messages="$errors->get('ends_at')"
                            />
                        </div>
                    </div>

                    <div>
                        <x-input-label
                            for="meeting_address"
                            value="Место встречи"
                        />

                        <x-text-input
                            id="meeting_address"
                            name="meeting_address"
                            type="text"
                            class="mt-1 block w-full"
                            :value="old(
                                'meeting_address',
                                $event->meeting_address
                            )"
                        />

                        <x-input-error
                            class="mt-2"
                            :messages="$errors->get('meeting_address')"
                        />
                    </div>

                    <div>
                        <x-input-label
                            for="venue_address"
                            value="Адрес проведения"
                        />

                        <x-text-input
                            id="venue_address"
                            name="venue_address"
                            type="text"
                            class="mt-1 block w-full"
                            :value="old(
                                'venue_address',
                                $event->venue_address
                            )"
                            required
                        />

                        <x-input-error
                            class="mt-2"
                            :messages="$errors->get('venue_address')"
                        />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label
                                for="min_age"
                                value="Минимальный возраст"
                            />

                            <x-text-input
                                id="min_age"
                                name="min_age"
                                type="number"
                                min="0"
                                max="120"
                                class="mt-1 block w-full"
                                :value="old(
                                    'min_age',
                                    $event->min_age
                                )"
                            />

                            <x-input-error
                                class="mt-2"
                                :messages="$errors->get('min_age')"
                            />
                        </div>

                        <div>
                            <x-input-label
                                for="max_age"
                                value="Максимальный возраст"
                            />

                            <x-text-input
                                id="max_age"
                                name="max_age"
                                type="number"
                                min="0"
                                max="120"
                                class="mt-1 block w-full"
                                :value="old(
                                    'max_age',
                                    $event->max_age
                                )"
                            />

                            <x-input-error
                                class="mt-2"
                                :messages="$errors->get('max_age')"
                            />
                        </div>
                    </div>

                    <div>
                        <x-input-label
                            for="gender_preference"
                            value="Предпочтение по полу"
                        />

                        <select
                            id="gender_preference"
                            name="gender_preference"
                            class="mt-1 block w-full rounded-md
                                   border-gray-300 shadow-sm"
                            required
                        >
                            @foreach ($genderPreferences as $preference)
                                <option
                                    value="{{ $preference->value }}"
                                    @selected(
                                        old(
                                            'gender_preference',
                                            $event->gender_preference->value
                                        ) === $preference->value
                                    )
                                >
                                    @switch($preference->value)
                                        @case('male')
                                            Мужчины
                                            @break

                                        @case('female')
                                            Женщины
                                            @break

                                        @default
                                            Неважно
                                    @endswitch
                                </option>
                            @endforeach
                        </select>

                        <x-input-error
                            class="mt-2"
                            :messages="$errors->get('gender_preference')"
                        />
                    </div>

                    <div>
                        <x-input-label
                            for="spots_total"
                            value="Количество мест"
                        />

                        <x-text-input
                            id="spots_total"
                            name="spots_total"
                            type="number"
                            min="{{ max(1, $event->spots_taken) }}"
                            class="mt-1 block w-full"
                            :value="old(
                                'spots_total',
                                $event->spots_total
                            )"
                            required
                        />

                        @if ($event->spots_taken > 0)
                            <p class="mt-1 text-xs text-gray-500">
                                Уже занято мест:
                                {{ $event->spots_taken }}.
                                Уменьшить количество ниже этого значения нельзя.
                            </p>
                        @endif

                        <x-input-error
                            class="mt-2"
                            :messages="$errors->get('spots_total')"
                        />
                    </div>

                    <div class="flex items-center justify-end gap-4">
                        <a
                            href="{{ route('events.show', $event) }}"
                            class="text-sm text-gray-600
                                   hover:text-gray-900"
                        >
                            Отмена
                        </a>

                        <x-primary-button>
                            Сохранить изменения
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>