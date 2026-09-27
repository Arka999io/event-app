<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Мероприятия
            </h2>

            @can('create', \App\Models\Event::class)
                <a
                    href="{{ route('events.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-800
                           rounded-md font-semibold text-xs text-white
                           uppercase tracking-widest hover:bg-gray-700"
                >
                    Создать мероприятие
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <div class="mb-8 bg-white shadow-sm sm:rounded-lg p-6">
            <form
                method="GET"
                action="{{ route('events.index') }}"
            >
                <div
                    class="grid grid-cols-1 gap-4
                        md:grid-cols-2 lg:grid-cols-4"
                >
                    <div class="lg:col-span-2">
                        <label
                            for="search"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Поиск
                        </label>

                        <input
                            id="search"
                            name="search"
                            type="text"
                            value="{{ request('search') }}"
                            placeholder="Название, описание или адрес"
                            class="mt-1 block w-full rounded-md
                                border-gray-300 shadow-sm
                                focus:border-indigo-500
                                focus:ring-indigo-500"
                        >
                    </div>

                    <div>
                        <label
                            for="category"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Категория
                        </label>

                        <select
                            id="category"
                            name="category"
                            class="mt-1 block w-full rounded-md
                                border-gray-300 shadow-sm
                                focus:border-indigo-500
                                focus:ring-indigo-500"
                        >
                            <option value="">
                                Все категории
                            </option>

                            @foreach ($categories as $category)
                                <option
                                    value="{{ $category->id }}"
                                    @selected(
                                        (string) request('category')
                                        === (string) $category->id
                                    )
                                >
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label
                            for="city"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Город / место
                        </label>

                        <input
                            id="city"
                            name="city"
                            type="text"
                            value="{{ request('city') }}"
                            placeholder="Например, Москва"
                            class="mt-1 block w-full rounded-md
                                border-gray-300 shadow-sm
                                focus:border-indigo-500
                                focus:ring-indigo-500"
                        >
                    </div>

                    <div>
                        <label
                            for="date_from"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Дата от
                        </label>

                        <input
                            id="date_from"
                            name="date_from"
                            type="date"
                            value="{{ request('date_from') }}"
                            class="mt-1 block w-full rounded-md
                                border-gray-300 shadow-sm
                                focus:border-indigo-500
                                focus:ring-indigo-500"
                        >
                    </div>

                    <div>
                        <label
                            for="date_to"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Дата до
                        </label>

                        <input
                            id="date_to"
                            name="date_to"
                            type="date"
                            value="{{ request('date_to') }}"
                            class="mt-1 block w-full rounded-md
                                border-gray-300 shadow-sm
                                focus:border-indigo-500
                                focus:ring-indigo-500"
                        >
                    </div>

                    <div>
                        <label
                            for="gender_preference"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Предпочтение по полу
                        </label>

                        <select
                            id="gender_preference"
                            name="gender_preference"
                            class="mt-1 block w-full rounded-md
                                border-gray-300 shadow-sm
                                focus:border-indigo-500
                                focus:ring-indigo-500"
                        >
                            <option value="">
                                Любое
                            </option>

                            @foreach ($genderPreferences as $preference)
                                <option
                                    value="{{ $preference->value }}"
                                    @selected(
                                        request('gender_preference')
                                        === $preference->value
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
                    </div>

                    <div class="flex items-end">
                        <label
                            class="flex min-h-10 cursor-pointer
                                items-center gap-2"
                        >
                            <input
                                name="available_only"
                                type="checkbox"
                                value="1"
                                @checked(request()->boolean('available_only'))
                                class="rounded border-gray-300
                                    text-indigo-600 shadow-sm
                                    focus:ring-indigo-500"
                            >

                            <span class="text-sm text-gray-700">
                                Только со свободными местами
                            </span>
                        </label>
                    </div>
                </div>

                <div
                    class="mt-6 flex flex-wrap
                        items-center gap-3"
                >
                    <x-primary-button>
                        Найти мероприятия
                    </x-primary-button>

                    <a
                        href="{{ route('events.index') }}"
                        class="inline-flex items-center px-4 py-2
                            text-sm text-gray-600
                            hover:text-gray-900"
                    >
                        Сбросить фильтры
                    </a>
                </div>
            </form>
        </div>

            @if ($events->isEmpty())
                <div
                    class="bg-white shadow-sm sm:rounded-lg
                           p-6 text-gray-600"
                >
                    Пока нет предстоящих мероприятий.
                </div>
            @else
                <div
                    class="grid grid-cols-1 gap-6
                           md:grid-cols-2 lg:grid-cols-3"
                >
                    @foreach ($events as $event)
                        <article
                            class="flex flex-col overflow-hidden bg-white
                                   shadow-sm sm:rounded-lg
                                   hover:shadow-md transition"
                        >
                            {{-- Обложка --}}
                            <a
                                href="{{ route('events.show', $event) }}"
                                class="block"
                            >
                                @if ($event->coverPhoto)
                                    <img
                                        src="{{ $event->coverPhoto->url() }}"
                                        alt="{{ $event->coverPhoto->alt_text ?? $event->title }}"
                                        class="h-48 w-full object-cover"
                                    >
                                @else
                                    <div
                                        class="flex h-48 w-full
                                               items-center justify-center
                                               bg-gray-100
                                               text-sm text-gray-400"
                                    >
                                        Нет фотографии
                                    </div>
                                @endif
                            </a>

                            <div class="flex flex-1 flex-col p-5">
                                {{-- Категория --}}
                                <div class="text-sm text-gray-500">
                                    {{ $event->category->name }}
                                </div>

                                {{-- Название --}}
                                <a
                                    href="{{ route('events.show', $event) }}"
                                    class="mt-2 block"
                                >
                                    <h3
                                        class="text-lg font-semibold
                                               text-gray-900
                                               hover:text-indigo-600"
                                    >
                                        {{ $event->title }}
                                    </h3>
                                </a>

                                {{-- Дата --}}
                                <div
                                    class="mt-4 flex items-center gap-2
                                           text-sm text-gray-600"
                                >
                                    <svg
                                        class="h-5 w-5 shrink-0 text-gray-400"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        aria-hidden="true"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M6.75 3v2.25M17.25 3v2.25M3.75
                                               9.75h16.5M5.25 5.25h13.5A1.5
                                               1.5 0 0 1 20.25 6.75v12A1.5
                                               1.5 0 0 1 18.75 20.25H5.25a1.5
                                               1.5 0 0 1-1.5-1.5v-12a1.5
                                               1.5 0 0 1 1.5-1.5Z"
                                        />
                                    </svg>

                                    <span>
                                        {{ $event->starts_at->format('d.m.Y H:i') }}
                                    </span>
                                </div>

                                {{-- Адрес --}}
                                <div
                                    class="mt-2 flex items-start gap-2
                                           text-sm text-gray-600"
                                >
                                    <svg
                                        class="mt-0.5 h-5 w-5 shrink-0
                                               text-gray-400"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        aria-hidden="true"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M12 21s6-5.25 6-11.25a6
                                               6 0 1 0-12 0C6 15.75 12
                                               21 12 21Z"
                                        />

                                        <circle
                                            cx="12"
                                            cy="9.75"
                                            r="2.25"
                                            stroke-width="1.5"
                                        />
                                    </svg>

                                    <span>
                                        {{ $event->venue_address }}
                                    </span>
                                </div>

                                {{-- Низ карточки --}}
                                <div
                                    class="mt-auto pt-5"
                                >
                                    <div
                                        class="border-t border-gray-100 pt-4
                                               flex items-center
                                               justify-between gap-4"
                                    >
                                        {{-- Организатор --}}
                                        <a
                                            href="{{ route(
                                                'users.show',
                                                $event->organizer
                                            ) }}"
                                            class="flex min-w-0
                                                   items-center gap-2
                                                   group"
                                        >
                                            @if ($event->organizer->profile?->avatar)
                                                <img
                                                    src="{{ $event->organizer->profile->avatar->url() }}"
                                                    alt="{{ $event->organizer->name }}"
                                                    class="h-9 w-9 shrink-0
                                                           rounded-full
                                                           object-cover"
                                                >
                                            @else
                                                <div
                                                    class="flex h-9 w-9
                                                           shrink-0
                                                           items-center
                                                           justify-center
                                                           rounded-full
                                                           bg-gray-200
                                                           text-sm
                                                           font-semibold
                                                           text-gray-700"
                                                >
                                                    {{ mb_strtoupper(
                                                        mb_substr(
                                                            $event->organizer->name,
                                                            0,
                                                            1
                                                        )
                                                    ) }}
                                                </div>
                                            @endif

                                            <span
                                                class="truncate text-sm
                                                       font-medium
                                                       text-gray-700
                                                       group-hover:text-indigo-600"
                                            >
                                                {{ $event->organizer->name }}
                                            </span>
                                        </a>

                                        {{-- Места --}}
                                        <div
                                            class="shrink-0 text-sm
                                                   text-gray-500"
                                        >
                                            {{ $event->spots_taken }}
                                            /
                                            {{ $event->spots_total }}
                                            мест
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $events->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>