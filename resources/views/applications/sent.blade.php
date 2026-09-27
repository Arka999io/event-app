<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Приглашения
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
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
                        <div class="flex items-center gap-4">
                            <a
                                href="{{ route('events.show', $application->event) }}"
                                class="flex h-16 w-16 shrink-0 items-center justify-center
                                       rounded-lg bg-gray-200 text-xl font-semibold text-gray-700"
                            >
                                {{ mb_strtoupper(mb_substr($application->event->title, 0, 1)) }}
                            </a>

                            <div class="min-w-0">
                                <a
                                    href="{{ route('events.show', $application->event) }}"
                                    class="font-semibold text-gray-900 hover:text-indigo-600"
                                >
                                    {{ $application->event->title }}
                                </a>

                                <div class="mt-1 text-sm text-gray-500">
                                    Организатор:
                                    {{ $application->event->organizer->name }}
                                </div>

                                <div class="mt-3 text-sm">
                                    @if ($eventClosed)
                                        <span class="font-medium text-gray-500">
                                            Мероприятие завершено
                                        </span>
                                    @else
                                        @switch($application->status->value)
                                            @case('pending')
                                                <span class="font-medium text-amber-600">
                                                    Вы отправили заявку. Ожидается решение организатора.
                                                </span>
                                                @break

                                            @case('waitlisted')
                                                <span class="font-medium text-amber-600">
                                                    Вы находитесь в листе ожидания.
                                                </span>
                                                @break

                                            @case('accepted')
                                                <span class="font-medium text-green-600">
                                                    Вас пригласили на мероприятие.
                                                </span>
                                                @break

                                            @case('rejected')
                                                <span class="font-medium text-red-600">
                                                    Организатор отказал в заявке.
                                                </span>
                                                @break

                                            @case('withdrawn')
                                                <span class="font-medium text-gray-500">
                                                    Вы отозвали заявку.
                                                </span>
                                                @break
                                        @endswitch
                                    @endif
                                </div>

                                <div class="mt-2 text-xs text-gray-400">
                                    {{ $application->updated_at->format('d.m.Y H:i') }}
                                </div>
                                <div class="mt-4">
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
                                            Чат с организатором
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white shadow-sm sm:rounded-lg p-8 text-center text-gray-500">
                        Вы пока не отправляли заявок на участие.
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $applications->links() }}
            </div>
        </div>
    </div>
</x-app-layout>