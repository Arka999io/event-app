<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Сообщения
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="space-y-4">

                @forelse ($conversations as $conversation)
                    @php
                        $isOrganizer =
                            Auth::id() === $conversation->event->organizer_id;

                        $otherUser = $isOrganizer
                            ? $conversation->user
                            : $conversation->event->organizer;

                        $lastMessage =
                            $conversation->messages->first();
                    @endphp

                    <div
                        class="bg-white shadow-sm sm:rounded-lg
                               border border-gray-100
                               hover:shadow-md transition"
                    >
                        <div class="p-5">
                            <div class="flex items-start gap-4">

                                {{-- Аватар собеседника --}}
                                <a
                                    href="{{ route('users.show', $otherUser) }}"
                                    class="shrink-0"
                                >
                                    @if ($otherUser->profile?->avatar)
                                        <img
                                            src="{{ $otherUser->profile->avatar->url() }}"
                                            alt="{{ $otherUser->name }}"
                                            class="h-14 w-14 rounded-full
                                                   object-cover
                                                   ring-1 ring-gray-200"
                                        >
                                    @else
                                        <div
                                            class="flex h-14 w-14
                                                   items-center justify-center
                                                   rounded-full bg-gray-200
                                                   text-xl font-semibold
                                                   text-gray-700
                                                   hover:bg-gray-300"
                                        >
                                            {{ mb_strtoupper(
                                                mb_substr(
                                                    $otherUser->name,
                                                    0,
                                                    1
                                                )
                                            ) }}
                                        </div>
                                    @endif
                                </a>

                                <div class="min-w-0 flex-1">
                                    <div
                                        class="flex items-start
                                               justify-between gap-4"
                                    >
                                        <div class="min-w-0">

                                            {{-- Имя собеседника --}}
                                            <a
                                                href="{{ route('users.show', $otherUser) }}"
                                                class="font-semibold
                                                       text-gray-900
                                                       hover:text-indigo-600"
                                            >
                                                {{ $otherUser->profile?->first_name ?? $otherUser->name }}

                                                @if ($otherUser->profile?->last_name)
                                                    {{ $otherUser->profile->last_name }}
                                                @endif
                                            </a>

                                            <div
                                                class="mt-0.5 text-xs
                                                       text-gray-400"
                                            >
                                                {{ $isOrganizer
                                                    ? 'Участник'
                                                    : 'Организатор'
                                                }}
                                            </div>
                                        </div>

                                        {{-- Время --}}
                                        <div
                                            class="shrink-0 text-xs
                                                   text-gray-400"
                                        >
                                            @if ($lastMessage)
                                                {{ $lastMessage->created_at->format('d.m.Y H:i') }}
                                            @else
                                                {{ $conversation->created_at->format('d.m.Y H:i') }}
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Мероприятие --}}
                                    <a
                                        href="{{ route(
                                            'events.show',
                                            $conversation->event
                                        ) }}"
                                        class="mt-2 inline-block
                                               text-sm text-indigo-600
                                               hover:text-indigo-900"
                                    >
                                        {{ $conversation->event->title }}
                                    </a>

                                    {{-- Последнее сообщение --}}
                                    @if ($lastMessage)
                                        <div
                                            class="mt-3 text-sm
                                                   text-gray-600"
                                        >
                                            <span class="font-medium">
                                                {{ $lastMessage->sender_id === Auth::id()
                                                    ? 'Вы'
                                                    : $lastMessage->sender->name
                                                }}:
                                            </span>

                                            {{ \Illuminate\Support\Str::limit(
                                                $lastMessage->body,
                                                100
                                            ) }}
                                        </div>
                                    @else
                                        <div
                                            class="mt-3 text-sm
                                                   text-gray-400"
                                        >
                                            Сообщений пока нет
                                        </div>
                                    @endif

                                    <div
                                        class="mt-4 flex items-center
                                               justify-between gap-4"
                                    >
                                        {{-- Открыть чат --}}
                                        <a
                                            href="{{ route(
                                                'conversations.show',
                                                $conversation
                                            ) }}"
                                            class="inline-flex items-center
                                                   text-sm font-medium
                                                   text-indigo-600
                                                   hover:text-indigo-900"
                                        >
                                            Открыть чат →
                                        </a>

                                        {{-- Непрочитанные --}}
                                        @if ($conversation->unread_messages_count > 0)
                                            <a
                                                href="{{ route(
                                                    'conversations.show',
                                                    $conversation
                                                ) }}"
                                                class="inline-flex items-center
                                                       gap-2"
                                            >
                                                <span
                                                    class="text-xs
                                                           text-gray-500"
                                                >
                                                    Новые сообщения
                                                </span>

                                                <span
                                                    class="inline-flex
                                                           min-w-6 h-6
                                                           items-center
                                                           justify-center
                                                           rounded-full
                                                           bg-indigo-600
                                                           px-2
                                                           text-xs
                                                           font-semibold
                                                           text-white"
                                                >
                                                    {{ $conversation->unread_messages_count }}
                                                </span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div
                        class="bg-white shadow-sm sm:rounded-lg
                               p-8 text-center text-gray-500"
                    >
                        У вас пока нет сообщений.
                    </div>
                @endforelse

            </div>

            @if ($conversations->hasPages())
                <div class="mt-6">
                    {{ $conversations->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>