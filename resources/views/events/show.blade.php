<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $event->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                @if (session('status'))
                    <div class="mb-6 text-sm text-green-700">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($event->status === \App\Enums\EventStatus::Cancelled)
                    <div
                        class="mb-6 rounded-lg border border-red-200
                            bg-red-50 p-4"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center
                                    justify-center rounded-full
                                    bg-red-100 text-red-600"
                            >
                                !
                            </div>

                            <div>
                                <div class="font-semibold text-red-800">
                                    Мероприятие отменено
                                </div>

                                <p class="mt-1 text-sm text-red-700">
                                    Организатор отменил это мероприятие.
                                    Откликнуться или начать новый диалог
                                    с организатором больше нельзя.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Карусель фотографий --}}
                @if ($event->photos->isNotEmpty())
                    <div
                        class="simple-carousel"
                        id="simple-carousel"
                    >
                        <div class="simple-carousel__viewport">
                            <div class="simple-carousel__track">
                                @foreach ($event->photos as $index => $photo)
                                    <div class="simple-carousel__slide">
                                        <img
                                            src="{{ $photo->url() }}"
                                            alt="{{ $photo->alt_text ?? $event->title }}"
                                        >
                                    </div>
                                @endforeach
                            </div>

                            @if ($event->photos->count() > 1)
                                <button
                                    type="button"
                                    class="simple-carousel__button simple-carousel__button--prev"
                                    id="simple-carousel-prev"
                                    aria-label="Предыдущая фотография"
                                >
                                    ←
                                </button>

                                <button
                                    type="button"
                                    class="simple-carousel__button simple-carousel__button--next"
                                    id="simple-carousel-next"
                                    aria-label="Следующая фотография"
                                >
                                    →
                                </button>

                                <div class="simple-carousel__counter">
                                    <span id="simple-carousel-current">1</span>
                                    /
                                    {{ $event->photos->count() }}
                                </div>
                            @endif
                        </div>

                        @if ($event->photos->count() > 1)
                            <div class="simple-carousel__dots">
                                @foreach ($event->photos as $index => $photo)
                                    <button
                                        type="button"
                                        class="simple-carousel__dot {{ $index === 0 ? 'simple-carousel__dot--active' : '' }}"
                                        data-carousel-index="{{ $index }}"
                                        aria-label="Фотография {{ $index + 1 }}"
                                    ></button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @else
                    <div class="simple-carousel__empty">
                        Фотографии мероприятия пока не добавлены
                    </div>
                @endif

                <style>
                    .simple-carousel {
                        width: 100%;
                        max-width: 640px;
                        margin: 0 auto 32px;
                    }

                    .simple-carousel__viewport {
                        position: relative;
                        width: 100%;
                        overflow: hidden;
                        border-radius: 12px;
                        background: #f3f4f6;
                    }

                    .simple-carousel__track {
                        display: flex;
                        width: 100%;
                        overflow-x: auto;

                        scroll-snap-type: x mandatory;
                        scroll-behavior: smooth;

                        scrollbar-width: none;
                    }

                    .simple-carousel__track::-webkit-scrollbar {
                        display: none;
                    }

                    .simple-carousel__slide {
                        flex: 0 0 100%;
                        width: 100%;

                        scroll-snap-align: start;
                        scroll-snap-stop: always;
                    }

                    .simple-carousel__slide img {
                        display: block;
                        width: 100%;
                        height: 300px;

                        object-fit: contain;

                        background: #f3f4f6;
                    }

                    .simple-carousel__button {
                        position: absolute;
                        top: 50%;
                        z-index: 100;

                        width: 46px;
                        height: 46px;

                        padding: 0;
                        border: 0;
                        border-radius: 50%;

                        transform: translateY(-50%);

                        background: rgba(0, 0, 0, 0.7);
                        color: white;

                        font-size: 24px;
                        font-weight: bold;
                        line-height: 46px;
                        text-align: center;

                        cursor: pointer;
                    }

                    .simple-carousel__button:hover {
                        background: rgba(0, 0, 0, 0.9);
                    }

                    .simple-carousel__button--prev {
                        left: 12px;
                    }

                    .simple-carousel__button--next {
                        right: 12px;
                    }

                    .simple-carousel__counter {
                        position: absolute;
                        top: 12px;
                        right: 12px;
                        z-index: 100;

                        padding: 5px 10px;

                        border-radius: 999px;

                        background: rgba(0, 0, 0, 0.7);
                        color: white;

                        font-size: 13px;
                    }

                    .simple-carousel__dots {
                        display: flex;
                        justify-content: center;
                        gap: 8px;

                        margin-top: 10px;
                    }

                    .simple-carousel__dot {
                        width: 10px;
                        height: 10px;

                        padding: 0;
                        border: 0;
                        border-radius: 50%;

                        background: #d1d5db;

                        cursor: pointer;
                    }

                    .simple-carousel__dot--active {
                        background: #374151;
                    }

                    .simple-carousel__empty {
                        display: flex;
                        align-items: center;
                        justify-content: center;

                        width: 100%;
                        max-width: 640px;
                        height: 200px;

                        margin: 0 auto 32px;

                        border-radius: 12px;

                        background: #f3f4f6;
                        color: #9ca3af;

                        font-size: 14px;
                    }

                    @media (max-width: 640px) {
                        .simple-carousel__slide img {
                            height: 240px;
                        }

                        .simple-carousel__button {
                            width: 40px;
                            height: 40px;

                            font-size: 20px;
                            line-height: 40px;
                        }

                        .simple-carousel__button--prev {
                            left: 8px;
                        }

                        .simple-carousel__button--next {
                            right: 8px;
                        }
                    }
                </style>

                <script>
                    (() => {
                        const carousel = document.getElementById(
                            'simple-carousel'
                        );

                        if (!carousel) {
                            return;
                        }

                        const track = carousel.querySelector(
                            '.simple-carousel__track'
                        );

                        const slides = carousel.querySelectorAll(
                            '.simple-carousel__slide'
                        );

                        const previousButton = document.getElementById(
                            'simple-carousel-prev'
                        );

                        const nextButton = document.getElementById(
                            'simple-carousel-next'
                        );

                        const counter = document.getElementById(
                            'simple-carousel-current'
                        );

                        const dots = carousel.querySelectorAll(
                            '.simple-carousel__dot'
                        );

                        const total = slides.length;

                        if (total <= 1) {
                            return;
                        }

                        let currentIndex = 0;

                        const showSlide = (index) => {
                            if (index < 0) {
                                index = total - 1;
                            }

                            if (index >= total) {
                                index = 0;
                            }

                            currentIndex = index;

                            track.scrollTo({
                                left: track.clientWidth * currentIndex,
                                behavior: 'smooth',
                            });

                            if (counter) {
                                counter.textContent = String(
                                    currentIndex + 1
                                );
                            }

                            dots.forEach((dot, dotIndex) => {
                                dot.classList.toggle(
                                    'simple-carousel__dot--active',
                                    dotIndex === currentIndex
                                );
                            });
                        };

                        previousButton?.addEventListener(
                            'click',
                            () => {
                                showSlide(currentIndex - 1);
                            }
                        );

                        nextButton?.addEventListener(
                            'click',
                            () => {
                                showSlide(currentIndex + 1);
                            }
                        );

                        dots.forEach((dot) => {
                            dot.addEventListener(
                                'click',
                                () => {
                                    showSlide(
                                        Number(
                                            dot.dataset.carouselIndex
                                        )
                                    );
                                }
                            );
                        });
                    })();
                </script>

                <div class="text-sm text-gray-500">
                    {{ $event->category->name }}
                </div>

                <h1 class="mt-2 text-2xl font-bold text-gray-900">
                    {{ $event->title }}
                </h1>

                <p class="mt-6 whitespace-pre-line text-gray-700">
                    {{ $event->description }}
                </p>

                <dl class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="font-medium text-gray-900">
                            Начало
                        </dt>

                        <dd class="mt-1 text-gray-600">
                            {{ $event->starts_at->format('d.m.Y H:i') }}
                        </dd>
                    </div>

                    @if ($event->ends_at)
                        <div>
                            <dt class="font-medium text-gray-900">
                                Окончание
                            </dt>

                            <dd class="mt-1 text-gray-600">
                                {{ $event->ends_at->format('d.m.Y H:i') }}
                            </dd>
                        </div>
                    @endif

                    <div>
                        <dt class="font-medium text-gray-900">
                            Адрес проведения
                        </dt>

                        <dd class="mt-1 text-gray-600">
                            {{ $event->venue_address }}
                        </dd>
                    </div>

                    @if ($event->meeting_address)
                        <div>
                            <dt class="font-medium text-gray-900">
                                Место встречи
                            </dt>

                            <dd class="mt-1 text-gray-600">
                                {{ $event->meeting_address }}
                            </dd>
                        </div>
                    @endif

                    <div>
                        <dt class="font-medium text-gray-900">
                            Организатор
                        </dt>

                        <dd class="mt-2">
                            <a
                                href="{{ route('users.show', $event->organizer) }}"
                                class="inline-flex items-center gap-3 group"
                            >
                                @if ($event->organizer->profile?->avatar)
                                    <img
                                        src="{{ $event->organizer->profile->avatar->url() }}"
                                        alt="{{ $event->organizer->name }}"
                                        class="h-10 w-10 rounded-full object-cover"
                                    >
                                @else
                                    <div
                                        class="flex h-10 w-10 items-center justify-center
                                            rounded-full bg-gray-200 font-semibold
                                            text-gray-700"
                                    >
                                        {{ mb_strtoupper(
                                            mb_substr($event->organizer->name, 0, 1)
                                        ) }}
                                    </div>
                                @endif

                                <span
                                    class="text-sm font-medium text-gray-700
                                        group-hover:text-indigo-600"
                                >
                                    {{ $event->organizer->name }}
                                </span>
                            </a>
                        </dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-900">
                            Участники
                        </dt>

                        <dd class="mt-1 text-gray-600">
                            {{ $event->spots_taken }}
                            /
                            {{ $event->spots_total }}
                        </dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-900">
                            Статус
                        </dt>

                        <dd class="mt-1 text-gray-600">
                            @switch($event->status->value)
                                @case('draft')
                                    Черновик
                                    @break

                                @case('published')
                                    Опубликовано
                                    @break

                                @case('ongoing')
                                    Идёт сейчас
                                    @break

                                @case('completed')
                                    Завершено
                                    @break

                                @case('cancelled')
                                    Отменено
                                    @break
                            @endswitch
                        </dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-900">
                            Предпочтение по полу
                        </dt>

                        <dd class="mt-1 text-gray-600">
                            @switch($event->gender_preference->value)
                                @case('male')
                                    Мужчины
                                    @break

                                @case('female')
                                    Женщины
                                    @break

                                @default
                                    Неважно
                            @endswitch
                        </dd>
                    </div>

                    @if ($event->min_age || $event->max_age)
                        <div>
                            <dt class="font-medium text-gray-900">
                                Возраст
                            </dt>

                            <dd class="mt-1 text-gray-600">
                                @if ($event->min_age && $event->max_age)
                                    {{ $event->min_age }}–{{ $event->max_age }} лет
                                @elseif ($event->min_age)
                                    От {{ $event->min_age }} лет
                                @else
                                    До {{ $event->max_age }} лет
                                @endif
                            </dd>
                        </div>
                    @endif
                </dl>

                {{-- Загрузка фотографий --}}
                @auth
                    @if (
                        Auth::id() === $event->organizer_id
                        && ! in_array(
                            $event->status,
                            [
                                \App\Enums\EventStatus::Completed,
                                \App\Enums\EventStatus::Cancelled,
                            ],
                            true
                        )
                    )
                        <div class="mt-8 border-t border-gray-200 pt-6">
                            <h2 class="text-lg font-semibold text-gray-900">
                                Фотографии мероприятия
                            </h2>

                            <p class="mt-1 text-sm text-gray-600">
                                Можно загрузить до 8 фотографий.
                                Первая фотография используется как обложка.
                            </p>

                            <form
                                method="POST"
                                action="{{ route('events.photos.store', $event) }}"
                                enctype="multipart/form-data"
                                class="mt-4"
                            >
                                @csrf

                                <div>
                                    <input
                                        id="photos"
                                        name="photos[]"
                                        type="file"
                                        accept="image/jpeg,image/png,image/webp"
                                        multiple
                                        required
                                        class="block w-full text-sm text-gray-600
                                               file:mr-4 file:rounded-md file:border-0
                                               file:bg-gray-800 file:px-4 file:py-2
                                               file:text-xs file:font-semibold file:uppercase
                                               file:tracking-widest file:text-white
                                               hover:file:bg-gray-700"
                                    >

                                    <x-input-error
                                        class="mt-2"
                                        :messages="$errors->get('photos')"
                                    />

                                    <x-input-error
                                        class="mt-2"
                                        :messages="$errors->get('photos.*')"
                                    />
                                </div>

                                <div class="mt-4">
                                    <x-primary-button>
                                        Добавить фотографии
                                    </x-primary-button>
                                </div>
                            </form>

                            <div class="mt-2 text-xs text-gray-500">
                                JPG, PNG или WebP.
                                Максимум 5 МБ на фотографию.
                                Сейчас загружено:
                                {{ $event->photos->count() }} из 8.
                            </div>

                            @if ($event->photos->isNotEmpty())
                            <div class="mt-6">
                                <h3 class="text-sm font-medium text-gray-900">
                                    Управление фотографиями
                                </h3>

                                <div class="mt-3 grid grid-cols-2 gap-4 sm:grid-cols-4">
                                    @foreach ($event->photos as $index => $photo)
                                        <div>
                                            <div class="relative overflow-hidden rounded-lg bg-gray-100">
                                                <img
                                                    src="{{ $photo->url() }}"
                                                    alt="{{ $photo->alt_text ?? $event->title }}"
                                                    class="h-24 w-full object-cover"
                                                >

                                                @if ($index === 0)
                                                    <div
                                                        class="absolute left-2 top-2 rounded
                                                            bg-gray-900 px-2 py-1
                                                            text-xs text-white"
                                                    >
                                                        Обложка
                                                    </div>
                                                @endif
                                            </div>

                                            <form
                                                method="POST"
                                                action="{{ route('events.photos.destroy', [$event, $photo]) }}"
                                                class="mt-2"
                                                onsubmit="return confirm('Удалить эту фотографию?');"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="text-xs font-medium text-red-600 hover:text-red-800"
                                                >
                                                    Удалить
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        </div>
                    @endif
                @endauth

                {{-- Действия организатора --}}
                @if (
                    Auth::check()
                    && Auth::id() === $event->organizer_id
                )
                    <div class="mt-8 border-t border-gray-200 pt-6">
                        <div class="flex flex-wrap items-center gap-3">

                            {{-- Редактирование --}}
                            @if (
                                ! in_array(
                                    $event->status,
                                    [
                                        \App\Enums\EventStatus::Completed,
                                        \App\Enums\EventStatus::Cancelled,
                                    ],
                                    true
                                )
                            )
                                <a
                                    href="{{ route('events.edit', $event) }}"
                                    class="inline-flex items-center px-4 py-2
                                        border border-gray-300 rounded-md
                                        font-semibold text-xs text-gray-700 uppercase
                                        tracking-widest bg-white hover:bg-gray-50
                                        focus:outline-none focus:ring-2
                                        focus:ring-indigo-500 focus:ring-offset-2
                                        transition ease-in-out duration-150"
                                >
                                    Редактировать мероприятие
                                </a>
                            @endif

                            {{-- Публикация --}}
                            @if ($event->status === \App\Enums\EventStatus::Draft)
                                <form
                                    method="POST"
                                    action="{{ route('events.publish', $event) }}"
                                >
                                    @csrf

                                    <x-primary-button>
                                        Опубликовать мероприятие
                                    </x-primary-button>
                                </form>
                            @endif

                            @if (
                                ! in_array(
                                    $event->status,
                                    [
                                        \App\Enums\EventStatus::Completed,
                                        \App\Enums\EventStatus::Cancelled,
                                    ],
                                    true
                                )
                            )
                                <form
                                    method="POST"
                                    action="{{ route('events.cancel', $event) }}"
                                    onsubmit="return confirm(
                                        'Вы уверены, что хотите отменить мероприятие?'
                                    );"
                                >
                                    @csrf

                                    <button
                                        type="submit"
                                        class="inline-flex items-center px-4 py-2
                                            rounded-md border border-red-300
                                            bg-white font-semibold text-xs
                                            text-red-600 uppercase tracking-widest
                                            hover:bg-red-50
                                            focus:outline-none focus:ring-2
                                            focus:ring-red-500 focus:ring-offset-2"
                                    >
                                        Отменить мероприятие
                                    </button>
                                </form>
                            @endif

                            @if ($event->status === \App\Enums\EventStatus::Ongoing)
                                <form
                                    method="POST"
                                    action="{{ route('events.complete', $event) }}"
                                    onsubmit="return confirm(
                                        'Завершить мероприятие? После этого изменить его будет нельзя.'
                                    );"
                                >
                                    @csrf

                                    <button
                                        type="submit"
                                        class="inline-flex items-center px-4 py-2
                                            rounded-md border border-green-300
                                            bg-white font-semibold text-xs
                                            text-green-700 uppercase tracking-widest
                                            hover:bg-green-50
                                            focus:outline-none focus:ring-2
                                            focus:ring-green-500 focus:ring-offset-2"
                                    >
                                        Завершить мероприятие
                                    </button>
                                </form>
                            @endif

                        </div>
                    </div>
                @endif

                {{-- Чат с организатором --}}
                @auth
                    @if (
                        Auth::id() !== $event->organizer_id
                        && ! in_array(
                            $event->status,
                            [
                                \App\Enums\EventStatus::Completed,
                                \App\Enums\EventStatus::Cancelled,
                            ],
                            true
                        )
                    )
                        <div class="mt-8 border-t border-gray-200 pt-6">
                            <form
                                method="POST"
                                action="{{ route('events.conversation.start', $event) }}"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    class="inline-flex items-center px-4 py-2
                                        border border-gray-300 rounded-md
                                        font-semibold text-xs text-gray-700 uppercase
                                        tracking-widest bg-white hover:bg-gray-50"
                                >
                                    Написать организатору
                                </button>
                            </form>
                        </div>
                    @endif
                @endauth

                {{-- Отклик на мероприятие --}}
                @auth
                    @if (
                        Auth::id() !== $event->organizer_id
                        && $event->status === \App\Enums\EventStatus::Published
                    )
                        <div class="mt-8 border-t border-gray-200 pt-6">
                            @if ($application)
                                <div class="text-sm text-gray-600">
                                    Ваша заявка:

                                    <span class="font-medium">
                                        @switch($application->status->value)
                                            @case('pending')
                                                На рассмотрении
                                                @break

                                            @case('accepted')
                                                Принята
                                                @break

                                            @case('rejected')
                                                Отклонена
                                                @break

                                            @case('withdrawn')
                                                Отозвана
                                                @break

                                            @case('waitlisted')
                                                В листе ожидания
                                                @break
                                        @endswitch
                                    </span>
                                </div>
                            @else
                                <form
                                    method="POST"
                                    action="{{ route('events.apply', $event) }}"
                                >
                                    @csrf

                                    <x-primary-button>
                                        Откликнуться
                                    </x-primary-button>
                                </form>
                            @endif
                        </div>
                    @endif
                @else
                    @if ($event->status === \App\Enums\EventStatus::Published)
                        <div class="mt-8 border-t border-gray-200 pt-6">
                            <p class="text-sm text-gray-600">
                                Чтобы откликнуться на мероприятие,
                                <a
                                    href="{{ route('login') }}"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    войдите в аккаунт
                                </a>.
                            </p>
                        </div>
                    @endif
                @endauth

                {{-- Отзывы после завершения мероприятия --}}
                @auth
                    @if ($event->status === \App\Enums\EventStatus::Completed)
                        <div class="mt-8 border-t border-gray-200 pt-6">
                            <h2 class="text-lg font-semibold text-gray-900">
                                Отзывы
                            </h2>

                            {{-- Организатор оставляет отзывы участникам --}}
                            @if (Auth::id() === $event->organizer_id)
                                <p class="mt-2 text-sm text-gray-500">
                                    Вы можете оставить отзыв участникам,
                                    которых пригласили на мероприятие.
                                </p>

                                <div class="mt-5 space-y-3">
                                    @forelse ($acceptedParticipants as $participant)
                                        <div
                                            class="flex flex-col gap-3 rounded-lg
                                                border border-gray-200 p-4
                                                sm:flex-row sm:items-center
                                                sm:justify-between"
                                        >
                                            <div class="flex items-center gap-3">
                                                @if ($participant->profile?->avatar)
                                                    <img
                                                        src="{{ $participant->profile->avatar->url() }}"
                                                        alt="{{ $participant->name }}"
                                                        class="h-10 w-10 shrink-0
                                                            rounded-full object-cover"
                                                    >
                                                @else
                                                    <div
                                                        class="flex h-10 w-10 shrink-0
                                                            items-center justify-center
                                                            rounded-full bg-gray-200
                                                            font-semibold text-gray-700"
                                                    >
                                                        {{ mb_strtoupper(
                                                            mb_substr(
                                                                $participant->name,
                                                                0,
                                                                1
                                                            )
                                                        ) }}
                                                    </div>
                                                @endif

                                                <a
                                                    href="{{ route(
                                                        'users.show',
                                                        $participant
                                                    ) }}"
                                                    class="font-medium text-gray-900
                                                        hover:text-indigo-600"
                                                >
                                                    {{
                                                        $participant->profile?->first_name
                                                        ?? $participant->name
                                                    }}

                                                    @if ($participant->profile?->last_name)
                                                        {{
                                                            $participant
                                                                ->profile
                                                                ->last_name
                                                        }}
                                                    @endif
                                                </a>
                                            </div>

                                            @if (
                                                $reviewedUserIds->contains(
                                                    $participant->id
                                                )
                                            )
                                                <span
                                                    class="text-sm font-medium
                                                        text-green-600"
                                                >
                                                    ✓ Отзыв оставлен
                                                </span>
                                            @else
                                                <a
                                                    href="{{ route(
                                                        'reviews.create',
                                                        [
                                                            $event,
                                                            $participant,
                                                        ]
                                                    ) }}"
                                                    class="text-sm font-medium
                                                        text-indigo-600
                                                        hover:text-indigo-900"
                                                >
                                                    Оставить отзыв
                                                </a>
                                            @endif
                                        </div>
                                    @empty
                                        <div
                                            class="rounded-lg bg-gray-50
                                                p-4 text-sm text-gray-500"
                                        >
                                            На мероприятии не было
                                            приглашённых участников.
                                        </div>
                                    @endforelse
                                </div>

                            {{-- Принятый участник оставляет отзыв организатору --}}
                            @elseif (
                                $application
                                && $application->status
                                    === \App\Enums\ApplicationStatus::Accepted
                            )
                                <p class="mt-2 text-sm text-gray-500">
                                    Мероприятие завершено.
                                    Вы можете оставить отзыв организатору.
                                </p>

                                <div class="mt-5">
                                    <div
                                        class="flex flex-col gap-3 rounded-lg
                                            border border-gray-200 p-4
                                            sm:flex-row sm:items-center
                                            sm:justify-between"
                                    >
                                        <div class="flex items-center gap-3">
                                            @if ($event->organizer->profile?->avatar)
                                                <img
                                                    src="{{ $event->organizer->profile->avatar->url() }}"
                                                    alt="{{ $event->organizer->name }}"
                                                    class="h-10 w-10 shrink-0
                                                        rounded-full object-cover"
                                                >
                                            @else
                                                <div
                                                    class="flex h-10 w-10 shrink-0
                                                        items-center justify-center
                                                        rounded-full bg-gray-200
                                                        font-semibold text-gray-700"
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

                                            <a
                                                href="{{ route(
                                                    'users.show',
                                                    $event->organizer
                                                ) }}"
                                                class="font-medium text-gray-900
                                                    hover:text-indigo-600"
                                            >
                                                {{
                                                    $event->organizer
                                                        ->profile?->first_name
                                                    ?? $event->organizer->name
                                                }}

                                                @if (
                                                    $event->organizer
                                                        ->profile?->last_name
                                                )
                                                    {{
                                                        $event->organizer
                                                            ->profile
                                                            ->last_name
                                                    }}
                                                @endif
                                            </a>
                                        </div>

                                        @if (
                                            $reviewedUserIds->contains(
                                                $event->organizer_id
                                            )
                                        )
                                            <span
                                                class="text-sm font-medium
                                                    text-green-600"
                                            >
                                                ✓ Отзыв оставлен
                                            </span>
                                        @else
                                            <a
                                                href="{{ route(
                                                    'reviews.create',
                                                    [
                                                        $event,
                                                        $event->organizer,
                                                    ]
                                                ) }}"
                                                class="inline-flex items-center
                                                    justify-center rounded-md
                                                    bg-indigo-600 px-4 py-2
                                                    text-xs font-semibold
                                                    uppercase tracking-widest
                                                    text-white
                                                    hover:bg-indigo-500"
                                            >
                                                Оставить отзыв
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                @endauth

                @auth
                    @if (Auth::id() !== $event->organizer_id)
                        <div class="mt-8 border-t border-gray-200 pt-6">
                            <a
                                href="{{ route(
                                    'reports.events.create',
                                    $event
                                ) }}"
                                class="text-sm font-medium
                                    text-red-600 hover:text-red-800"
                            >
                                Пожаловаться на мероприятие
                            </a>
                        </div>
                    @endif
                @endauth

                <div class="mt-8">
                    <a
                        href="{{ route('events.index') }}"
                        class="text-sm text-indigo-600 hover:text-indigo-900"
                    >
                        ← Вернуться к мероприятиям
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>