<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Уведомления
            </h2>

            @if (Auth::user()->unreadNotifications()->exists())
                <form
                    method="POST"
                    action="{{ route('notifications.read-all') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="text-sm font-medium text-indigo-600
                               hover:text-indigo-900"
                    >
                        Прочитать все
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if (session('status'))
                <div
                    class="mb-6 rounded-md bg-green-50
                           p-4 text-sm text-green-700"
                >
                    {{ session('status') }}
                </div>
            @endif

            <div
                class="overflow-hidden bg-white
                       shadow-sm sm:rounded-lg"
            >
                @forelse ($notifications as $notification)
                    <a
                        href="{{ route(
                            'notifications.open',
                            $notification
                        ) }}"
                        class="block border-b border-gray-200
                               p-5 transition
                               {{ $notification->read_at === null
                                   ? 'bg-indigo-50 hover:bg-indigo-100'
                                   : 'bg-white hover:bg-gray-50'
                               }}"
                    >
                        <div
                            class="flex items-start
                                   justify-between gap-4"
                        >
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    @if ($notification->read_at === null)
                                        <span
                                            class="h-2.5 w-2.5 shrink-0
                                                   rounded-full bg-indigo-600"
                                        ></span>
                                    @endif

                                    <div
                                        class="font-medium text-gray-900"
                                    >
                                        {{ $notification->data['text']
                                            ?? 'Новое уведомление'
                                        }}
                                    </div>
                                </div>

                                @if (
                                    isset(
                                        $notification->data['event_title']
                                    )
                                )
                                    <div
                                        class="mt-2 text-sm
                                               text-gray-500"
                                    >
                                        {{ $notification->data['event_title'] }}
                                    </div>
                                @endif
                            </div>

                            <div
                                class="shrink-0 text-xs text-gray-400"
                            >
                                {{ $notification->created_at
                                    ->format('d.m.Y H:i')
                                }}
                            </div>
                        </div>
                    </a>
                @empty
                    <div
                        class="p-10 text-center text-gray-500"
                    >
                        У вас пока нет уведомлений.
                    </div>
                @endforelse
            </div>

            @if ($notifications->hasPages())
                <div class="mt-6">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>