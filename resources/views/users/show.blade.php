<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Профиль пользователя
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                {{-- Основная информация --}}
                <div class="flex items-center gap-5">
                    @if ($profileUser->profile?->avatar)
                        <img
                            src="{{ $profileUser->profile->avatar->url() }}"
                            alt="{{ $profileUser->name }}"
                            class="h-20 w-20 shrink-0 rounded-full object-cover"
                        >
                    @else
                        <div
                            class="flex h-20 w-20 shrink-0 items-center
                                   justify-center rounded-full bg-gray-200
                                   text-2xl font-semibold text-gray-700"
                        >
                            {{ mb_strtoupper(
                                mb_substr($profileUser->name, 0, 1)
                            ) }}
                        </div>
                    @endif

                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            {{ $profileUser->profile?->first_name
                                ?? $profileUser->name
                            }}

                            @if ($profileUser->profile?->last_name)
                                {{ $profileUser->profile->last_name }}
                            @endif
                        </h1>

                        @if ($profileUser->profile?->city)
                            <div class="mt-1 text-sm text-gray-500">
                                {{ $profileUser->profile->city }}
                            </div>
                        @endif

                        {{-- Рейтинг --}}
                        <div class="mt-3">
                            @if ($averageRating !== null)
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-lg font-semibold
                                               text-amber-500"
                                    >
                                        ★ {{ number_format(
                                            $averageRating,
                                            1
                                        ) }}
                                    </span>

                                    <span class="text-sm text-gray-500">
                                        {{ $reviewsCount }} отзывов
                                    </span>
                                </div>
                            @else
                                <div class="text-sm text-gray-500">
                                    Отзывов пока нет
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- О себе --}}
                @if ($profileUser->profile?->bio)
                    <div class="mt-8">
                        <h2 class="font-semibold text-gray-900">
                            О себе
                        </h2>

                        <p class="mt-2 whitespace-pre-line text-gray-700">
                            {{ $profileUser->profile->bio }}
                        </p>
                    </div>
                @endif

                {{-- Возраст --}}
                @if ($profileUser->profile?->birth_date)
                    <div class="mt-6 text-sm text-gray-600">
                        <span class="font-medium text-gray-900">
                            Возраст:
                        </span>

                        {{ $profileUser->profile->birth_date->age }}
                    </div>
                @endif

                {{-- Пол --}}
                @if ($profileUser->profile?->gender)
                    <div class="mt-2 text-sm text-gray-600">
                        <span class="font-medium text-gray-900">
                            Пол:
                        </span>

                        @switch($profileUser->profile->gender->value)
                            @case('male')
                                Мужской
                                @break

                            @case('female')
                                Женский
                                @break
                        @endswitch
                    </div>
                @endif

                {{-- Отзывы --}}
                <div class="mt-10 border-t border-gray-200 pt-8">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-lg font-semibold text-gray-900">
                            Отзывы
                        </h2>

                        @if ($averageRating !== null)
                            <div
                                class="text-sm font-medium
                                       text-amber-600"
                            >
                                ★ {{ number_format(
                                    $averageRating,
                                    1
                                ) }} / 5
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 space-y-5">
                        @forelse ($reviews as $review)
                            <div
                                class="rounded-lg border
                                       border-gray-200 p-5"
                            >
                                <div
                                    class="flex flex-wrap
                                           items-start
                                           justify-between gap-4"
                                >
                                    <div class="flex items-center gap-3">
                                        @if (
                                            $review->author
                                                ->profile?->avatar
                                        )
                                            <img
                                                src="{{
                                                    $review->author
                                                        ->profile
                                                        ->avatar
                                                        ->url()
                                                }}"
                                                alt="{{
                                                    $review->author->name
                                                }}"
                                                class="h-10 w-10
                                                       rounded-full
                                                       object-cover"
                                            >
                                        @else
                                            <div
                                                class="flex h-10 w-10
                                                       items-center
                                                       justify-center
                                                       rounded-full
                                                       bg-gray-200
                                                       font-semibold
                                                       text-gray-700"
                                            >
                                                {{
                                                    mb_strtoupper(
                                                        mb_substr(
                                                            $review
                                                                ->author
                                                                ->name,
                                                            0,
                                                            1
                                                        )
                                                    )
                                                }}
                                            </div>
                                        @endif

                                        <div>
                                            <a
                                                href="{{ route(
                                                    'users.show',
                                                    $review->author
                                                ) }}"
                                                class="font-medium
                                                       text-gray-900
                                                       hover:text-indigo-600"
                                            >
                                                {{
                                                    $review->author
                                                        ->profile
                                                        ?->first_name
                                                    ?? $review
                                                        ->author
                                                        ->name
                                                }}

                                                @if (
                                                    $review->author
                                                        ->profile
                                                        ?->last_name
                                                )
                                                    {{
                                                        $review->author
                                                            ->profile
                                                            ->last_name
                                                    }}
                                                @endif
                                            </a>

                                            <div
                                                class="mt-1 text-sm
                                                       text-amber-500"
                                            >
                                                {{
                                                    str_repeat(
                                                        '★',
                                                        $review->rating
                                                    )
                                                }}

                                                <span
                                                    class="text-gray-300"
                                                >
                                                    {{
                                                        str_repeat(
                                                            '★',
                                                            5 - $review->rating
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="text-xs text-gray-400"
                                    >
                                        {{
                                            $review->created_at
                                                ->format('d.m.Y')
                                        }}
                                    </div>
                                </div>

                                <div class="mt-4 text-sm">
                                    <span class="text-gray-500">
                                        Мероприятие:
                                    </span>

                                    <a
                                        href="{{ route(
                                            'events.show',
                                            $review->event
                                        ) }}"
                                        class="font-medium
                                               text-indigo-600
                                               hover:text-indigo-900"
                                    >
                                        {{ $review->event->title }}
                                    </a>
                                </div>

                                @if ($review->comment)
                                    <p
                                        class="mt-4 whitespace-pre-line
                                               text-gray-700"
                                    >
                                        {{ $review->comment }}
                                    </p>
                                @endif
                            </div>
                        @empty
                            <div
                                class="rounded-lg bg-gray-50
                                       p-6 text-center
                                       text-sm text-gray-500"
                            >
                                У пользователя пока нет отзывов.
                            </div>
                        @endforelse
                    </div>

                    @if ($reviews->hasPages())
                        <div class="mt-6">
                            {{ $reviews->links() }}
                        </div>
                    @endif
                </div>

                {{-- Действия с пользователем --}}
                @auth
                    @if (Auth::id() !== $profileUser->id)
                        <div
                            class="mt-8 border-t border-gray-200 pt-6"
                        >
                            @if (session('status'))
                                <div
                                    class="mb-4 rounded-lg border
                                        border-green-200 bg-green-50
                                        p-4 text-sm text-green-700"
                                >
                                    {{ session('status') }}
                                </div>
                            @endif

                            @if ($errors->has('block'))
                                <div
                                    class="mb-4 rounded-lg border
                                        border-red-200 bg-red-50
                                        p-4 text-sm text-red-700"
                                >
                                    {{ $errors->first('block') }}
                                </div>
                            @endif

                            <div
                                class="flex flex-wrap items-center gap-4"
                            >
                                @if ($blockedByCurrentUser)
                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'users.unblock',
                                            $profileUser
                                        ) }}"
                                        onsubmit="return confirm(
                                            'Разблокировать этого пользователя?'
                                        );"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="inline-flex items-center
                                                rounded-md border
                                                border-gray-300 bg-white
                                                px-4 py-2 text-xs
                                                font-semibold uppercase
                                                tracking-widest text-gray-700
                                                hover:bg-gray-50"
                                        >
                                            Разблокировать
                                        </button>
                                    </form>
                                @elseif ($currentUserBlockedByProfileUser)
                                    <div
                                        class="rounded-md bg-gray-100
                                            px-4 py-2 text-sm
                                            text-gray-600"
                                    >
                                        Этот пользователь ограничил
                                        взаимодействие с вами.
                                    </div>
                                @else
                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'users.block',
                                            $profileUser
                                        ) }}"
                                        onsubmit="return confirm(
                                            'Заблокировать этого пользователя? '
                                            + 'Вы больше не сможете нормально '
                                            + 'взаимодействовать друг с другом.'
                                        );"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="inline-flex items-center
                                                rounded-md border
                                                border-red-300 bg-white
                                                px-4 py-2 text-xs
                                                font-semibold uppercase
                                                tracking-widest text-red-600
                                                hover:bg-red-50"
                                        >
                                            Заблокировать
                                        </button>
                                    </form>
                                @endif

                                @if (! $blockedByCurrentUser)
                                    <a
                                        href="{{ route(
                                            'reports.users.create',
                                            $profileUser
                                        ) }}"
                                        class="text-sm font-medium
                                            text-red-600
                                            hover:text-red-800"
                                    >
                                        Пожаловаться на пользователя
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                @endauth

                {{-- Назад --}}
                <div class="mt-8">
                    <a
                        href="{{ url()->previous() }}"
                        class="text-sm text-indigo-600
                               hover:text-indigo-900"
                    >
                        ← Назад
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>