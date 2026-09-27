<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Отклики
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-6 rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="space-y-4">
                @forelse ($applications as $application)
                    @php
                        $eventClosed = in_array(
                            $application->event->status,
                            [
                                \App\Enums\EventStatus::Completed,
                                \App\Enums\EventStatus::Cancelled,
                            ],
                            true
                        );
                    @endphp

                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                            <div class="min-w-0">
                                <div class="flex items-center gap-4">
                                    <a
                                        href="{{ route('users.show', $application->applicant) }}"
                                        class="block h-12 w-12 shrink-0"
                                    >
                                        @if ($application->applicant->profile?->avatar)
                                            <img
                                                src="{{ $application->applicant->profile->avatar->url() }}"
                                                alt="{{ $application->applicant->name }}"
                                                class="h-12 w-12 rounded-full object-cover"
                                            >
                                        @else
                                            <div
                                                class="flex h-12 w-12 items-center justify-center
                                                    rounded-full bg-gray-200 text-lg font-semibold
                                                    text-gray-700 hover:bg-gray-300"
                                            >
                                                {{ mb_strtoupper(mb_substr($application->applicant->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </a>

                                    <div>
                                        <a
                                            href="{{ route('users.show', $application->applicant) }}"
                                            class="font-semibold text-gray-900 hover:text-indigo-600"
                                        >
                                            {{ $application->applicant->name }}
                                        </a>

                                        <a
                                            href="{{ route('events.show', $application->event) }}"
                                            class="mt-1 block text-sm text-indigo-600 hover:text-indigo-900"
                                        >
                                            {{ $application->event->title }}
                                        </a>
                                    </div>
                                </div>

                                <div class="mt-4 text-sm">
                                    @if ($eventClosed)
                                        <span class="font-medium text-gray-500">
                                            Мероприятие завершено
                                        </span>
                                    @else
                                        @switch($application->status->value)
                                            @case('pending')
                                                <span class="font-medium text-amber-600">
                                                    Ожидает вашего решения
                                                </span>
                                                @break

                                            @case('waitlisted')
                                                <span class="font-medium text-amber-600">
                                                    Пользователь в листе ожидания
                                                </span>
                                                @break

                                            @case('accepted')
                                                <span class="font-medium text-green-600">
                                                    Вы пригласили пользователя
                                                </span>
                                                @break

                                            @case('rejected')
                                                <span class="font-medium text-red-600">
                                                    Вы отказали пользователю
                                                </span>
                                                @break

                                            @case('withdrawn')
                                                <span class="font-medium text-gray-500">
                                                    Пользователь отозвал заявку
                                                </span>
                                                @break
                                        @endswitch
                                    @endif
                                </div>

                                <div class="mt-2 text-xs text-gray-400">
                                    {{ $application->updated_at->format('d.m.Y H:i') }}
                                </div>
                            </div>

                            @if (! $eventClosed)
                                <div class="flex flex-wrap gap-2">
                                    @if ($application->status !== \App\Enums\ApplicationStatus::Accepted)
                                        <form
                                            method="POST"
                                            action="{{ route('applications.accept', $application) }}"
                                        >
                                            @csrf

                                            <x-primary-button>
                                                Пригласить
                                            </x-primary-button>
                                        </form>
                                    @endif

                                    @if ($application->status !== \App\Enums\ApplicationStatus::Rejected)
                                        <form
                                            method="POST"
                                            action="{{ route('applications.reject', $application) }}"
                                        >
                                            @csrf

                                            <button
                                                type="submit"
                                                class="inline-flex items-center px-4 py-2
                                                       bg-red-600 border border-transparent rounded-md
                                                       font-semibold text-xs text-white uppercase
                                                       tracking-widest hover:bg-red-500"
                                            >
                                                Отказать
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endif
                            <div>
                                <form
                                    method="POST"
                                    action="{{ route('applications.conversation', $application) }}"
                                >
                                    @csrf

                                    <button
                                        type="submit"
                                        class="inline-flex items-center px-4 py-2
                                               bg-white border border-gray-300 rounded-md
                                               font-semibold text-xs text-gray-700 uppercase
                                               tracking-widest hover:bg-gray-50"
                                    >
                                        Чат
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white shadow-sm sm:rounded-lg p-8 text-center text-gray-500">
                        На ваши мероприятия пока никто не откликнулся.
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $applications->links() }}
            </div>
        </div>
    </div>
</x-app-layout>