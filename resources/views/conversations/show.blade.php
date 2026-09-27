<x-app-layout>

    @php
        $isOrganizer =
            Auth::id() === $conversation->event->organizer_id;

        $otherUser = $isOrganizer
            ? $conversation->user
            : $conversation->event->organizer;
    @endphp

    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $conversation->event->title }}
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Чат по мероприятию
            </p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">

                <div class="p-6 border-b border-gray-200">
                    <a
                        href="{{ route('events.show', $conversation->event) }}"
                        class="text-sm text-indigo-600 hover:text-indigo-900"
                    >
                        ← Вернуться к мероприятию
                    </a>

                    <div class="mt-5">
                        <a
                            href="{{ route('users.show', $otherUser) }}"
                            class="inline-flex items-center gap-3 group"
                        >
                            @if ($otherUser->profile?->avatar)
                                <img
                                    src="{{ $otherUser->profile->avatar->url() }}"
                                    alt="{{ $otherUser->name }}"
                                    class="h-12 w-12 shrink-0 rounded-full object-cover
                                        ring-1 ring-gray-200"
                                >
                            @else
                                <div
                                    class="flex h-12 w-12 shrink-0 items-center
                                        justify-center rounded-full bg-gray-200
                                        text-lg font-semibold text-gray-700
                                        group-hover:bg-gray-300"
                                >
                                    {{ mb_strtoupper(
                                        mb_substr($otherUser->name, 0, 1)
                                    ) }}
                                </div>
                            @endif

                            <div>
                                <div
                                    class="font-semibold text-gray-900
                                        group-hover:text-indigo-600"
                                >
                                    {{ $otherUser->profile?->first_name ?? $otherUser->name }}

                                    @if ($otherUser->profile?->last_name)
                                        {{ $otherUser->profile->last_name }}
                                    @endif
                                </div>

                                <div class="mt-0.5 text-xs text-gray-500">
                                    @if ($isOrganizer)
                                        Участник
                                    @else
                                        Организатор мероприятия
                                    @endif
                                </div>
                            </div>
                        </a>
                    </div>

                    @if ($application)
                        <div
                            id="application-status"
                            class="mt-4 rounded-lg border border-gray-200 bg-gray-50 p-4"
                            data-application-id="{{ $application->id }}"
                            data-is-organizer="{{ Auth::id() === $conversation->event->organizer_id ? '1' : '0' }}"
                            data-is-read-only="{{ $isReadOnly ? '1' : '0' }}"
                            data-accept-url="{{ route('applications.accept', $application) }}"
                            data-reject-url="{{ route('applications.reject', $application) }}"
                        >
                            @if (Auth::id() === $conversation->event->organizer_id)
                                @switch($application->status->value)
                                    @case('pending')
                                        <div class="text-sm font-medium text-amber-600">
                                            Пользователь отправил заявку на участие.
                                        </div>

                                        @if (! $isReadOnly)
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                <form
                                                    method="POST"
                                                    action="{{ route('applications.accept', $application) }}"
                                                    class="application-action-form"
                                                >
                                                    @csrf

                                                    <x-primary-button>
                                                        Пригласить
                                                    </x-primary-button>
                                                </form>

                                                <form
                                                    method="POST"
                                                    action="{{ route('applications.reject', $application) }}"
                                                    class="application-action-form"
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
                                            </div>
                                        @endif
                                        @break

                                    @case('waitlisted')
                                        <div class="text-sm font-medium text-amber-600">
                                            Пользователь находится в листе ожидания.
                                        </div>

                                        @if (! $isReadOnly)
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                <form
                                                    method="POST"
                                                    action="{{ route('applications.accept', $application) }}"
                                                    class="application-action-form"
                                                >
                                                    @csrf

                                                    <x-primary-button>
                                                        Пригласить
                                                    </x-primary-button>
                                                </form>

                                                <form
                                                    method="POST"
                                                    action="{{ route('applications.reject', $application) }}"
                                                    class="application-action-form"
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
                                            </div>
                                        @endif
                                        @break

                                    @case('accepted')
                                        <div class="text-sm font-medium text-green-600">
                                            Вы пригласили пользователя.
                                        </div>

                                        @if (! $isReadOnly)
                                            <div class="mt-3">
                                                <form
                                                    method="POST"
                                                    action="{{ route('applications.reject', $application) }}"
                                                    class="application-action-form"
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
                                            </div>
                                        @endif
                                        @break

                                    @case('rejected')
                                        <div class="text-sm font-medium text-red-600">
                                            Вы отказали пользователю.
                                        </div>

                                        @if (! $isReadOnly)
                                            <div class="mt-3">
                                                <form
                                                    method="POST"
                                                    action="{{ route('applications.accept', $application) }}"
                                                    class="application-action-form"
                                                >
                                                    @csrf

                                                    <x-primary-button>
                                                        Пригласить
                                                    </x-primary-button>
                                                </form>
                                            </div>
                                        @endif
                                        @break

                                    @case('withdrawn')
                                        <div class="text-sm font-medium text-gray-500">
                                            Пользователь отозвал заявку.
                                        </div>
                                        @break
                                @endswitch
                            @else
                                @switch($application->status->value)
                                    @case('pending')
                                        <div class="text-sm font-medium text-amber-600">
                                            Вы отправили заявку на участие.
                                            Ожидается решение организатора.
                                        </div>
                                        @break

                                    @case('waitlisted')
                                        <div class="text-sm font-medium text-amber-600">
                                            Вы находитесь в листе ожидания.
                                        </div>
                                        @break

                                    @case('accepted')
                                        <div class="text-sm font-medium text-green-600">
                                            Вас пригласили на мероприятие.
                                        </div>
                                        @break

                                    @case('rejected')
                                        <div class="text-sm font-medium text-red-600">
                                            Организатор отказал в вашей заявке.
                                        </div>
                                        @break

                                    @case('withdrawn')
                                        <div class="text-sm font-medium text-gray-500">
                                            Вы отозвали заявку.
                                        </div>
                                        @break
                                @endswitch
                            @endif
                        </div>
                    @endif
                </div>

                <div class="p-6">
                    <div
                        id="messages"
                        class="space-y-4"
                        data-current-user-id="{{ Auth::id() }}"
                        data-conversation-id="{{ $conversation->id }}"
                        data-report-message-url-template="{{ route(
                            'reports.messages.create',
                            ['message' => '__MESSAGE_ID__']
                        ) }}"
                    >
                        @forelse ($conversation->messages as $message)
                            <div
                                data-message-id="{{ $message->id }}"
                                class="max-w-2xl rounded-lg p-4
                                    {{ $message->sender_id === Auth::id()
                                        ? 'ml-auto bg-gray-100'
                                        : 'mr-auto border border-gray-200'
                                    }}"
                            >
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $message->sender->name }}
                                </div>

                                <div class="mt-1 whitespace-pre-line text-gray-700">
                                    {{ $message->body }}
                                </div>

                                <div
                                    class="mt-2 flex items-center gap-3
                                        text-xs text-gray-400"
                                >
                                    <span>
                                        {{ $message->created_at->format('d.m.Y H:i') }}
                                    </span>

                                    @if ($message->sender_id !== Auth::id())
                                        <a
                                            href="{{ route(
                                                'reports.messages.create',
                                                $message
                                            ) }}"
                                            class="font-medium text-red-500
                                                hover:text-red-700"
                                        >
                                            Пожаловаться
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div
                                id="empty-messages"
                                class="py-8 text-center text-gray-500"
                            >
                                Сообщений пока нет. Начните диалог.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="border-t border-gray-200 p-6">
                    @if ($isReadOnly)
                        <div class="rounded-md bg-gray-100 p-4 text-sm text-gray-600">
                            Мероприятие завершено или отменено.
                            Чат доступен только для чтения.
                        </div>
                    @else
                        <form
                            id="message-form"
                            method="POST"
                            action="{{ route('conversations.messages.store', $conversation) }}"
                        >
                            @csrf

                            <div>
                                <x-input-label
                                    for="body"
                                    value="Сообщение"
                                />

                                <textarea
                                    id="body"
                                    name="body"
                                    rows="3"
                                    maxlength="5000"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm
                                           focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Напишите сообщение..."
                                    required
                                >{{ old('body') }}</textarea>

                                <x-input-error
                                    class="mt-2"
                                    :messages="$errors->get('body')"
                                />
                            </div>

                            <div class="mt-4 flex justify-end">
                                <x-primary-button>
                                    Отправить
                                </x-primary-button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const messages = document.getElementById('messages');
                const messageForm = document.getElementById('message-form');
                const textarea = document.getElementById('body');
                const applicationStatus =
                    document.getElementById('application-status');

                if (!messages || !window.Echo) {
                    return;
                }

                const conversationId = Number(
                    messages.dataset.conversationId
                );

                const currentUserId = Number(
                    messages.dataset.currentUserId
                );

                const csrfToken = document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute('content');

                const channel = window.Echo.private(
                    `conversation.${conversationId}`
                );

                const createActionButton = (
                    text,
                    url,
                    type
                ) => {
                    const form = document.createElement('form');

                    form.method = 'POST';
                    form.action = url;
                    form.className = 'application-action-form';

                    const button = document.createElement('button');

                    button.type = 'submit';
                    button.textContent = text;

                    if (type === 'accept') {
                        button.className =
                            'inline-flex items-center px-4 py-2 bg-gray-800 ' +
                            'border border-transparent rounded-md font-semibold ' +
                            'text-xs text-white uppercase tracking-widest ' +
                            'hover:bg-gray-700';
                    } else {
                        button.className =
                            'inline-flex items-center px-4 py-2 bg-red-600 ' +
                            'border border-transparent rounded-md font-semibold ' +
                            'text-xs text-white uppercase tracking-widest ' +
                            'hover:bg-red-500';
                    }

                    form.appendChild(button);

                    return form;
                };

                const renderApplicationStatus = (status) => {
                    if (!applicationStatus) {
                        return;
                    }

                    const isOrganizer =
                        applicationStatus.dataset.isOrganizer === '1';

                    const isReadOnly =
                        applicationStatus.dataset.isReadOnly === '1';

                    const acceptUrl =
                        applicationStatus.dataset.acceptUrl;

                    const rejectUrl =
                        applicationStatus.dataset.rejectUrl;

                    applicationStatus.innerHTML = '';

                    const statusText = document.createElement('div');

                    statusText.className = 'text-sm font-medium';

                    if (isOrganizer) {
                        switch (status) {
                            case 'pending':
                                statusText.textContent =
                                    'Пользователь отправил заявку на участие.';
                                statusText.classList.add('text-amber-600');
                                break;

                            case 'waitlisted':
                                statusText.textContent =
                                    'Пользователь находится в листе ожидания.';
                                statusText.classList.add('text-amber-600');
                                break;

                            case 'accepted':
                                statusText.textContent =
                                    'Вы пригласили пользователя.';
                                statusText.classList.add('text-green-600');
                                break;

                            case 'rejected':
                                statusText.textContent =
                                    'Вы отказали пользователю.';
                                statusText.classList.add('text-red-600');
                                break;

                            case 'withdrawn':
                                statusText.textContent =
                                    'Пользователь отозвал заявку.';
                                statusText.classList.add('text-gray-500');
                                break;

                            default:
                                return;
                        }

                        applicationStatus.appendChild(statusText);

                        if (isReadOnly || status === 'withdrawn') {
                            return;
                        }

                        const actions = document.createElement('div');

                        actions.className =
                            'mt-3 flex flex-wrap gap-2';

                        if (
                            status === 'pending'
                            || status === 'waitlisted'
                        ) {
                            actions.appendChild(
                                createActionButton(
                                    'Пригласить',
                                    acceptUrl,
                                    'accept'
                                )
                            );

                            actions.appendChild(
                                createActionButton(
                                    'Отказать',
                                    rejectUrl,
                                    'reject'
                                )
                            );
                        }

                        if (status === 'accepted') {
                            actions.appendChild(
                                createActionButton(
                                    'Отказать',
                                    rejectUrl,
                                    'reject'
                                )
                            );
                        }

                        if (status === 'rejected') {
                            actions.appendChild(
                                createActionButton(
                                    'Пригласить',
                                    acceptUrl,
                                    'accept'
                                )
                            );
                        }

                        applicationStatus.appendChild(actions);

                        return;
                    }

                    switch (status) {
                        case 'pending':
                            statusText.textContent =
                                'Вы отправили заявку на участие. ' +
                                'Ожидается решение организатора.';
                            statusText.classList.add('text-amber-600');
                            break;

                        case 'waitlisted':
                            statusText.textContent =
                                'Вы находитесь в листе ожидания.';
                            statusText.classList.add('text-amber-600');
                            break;

                        case 'accepted':
                            statusText.textContent =
                                'Вас пригласили на мероприятие.';
                            statusText.classList.add('text-green-600');
                            break;

                        case 'rejected':
                            statusText.textContent =
                                'Организатор отказал в вашей заявке.';
                            statusText.classList.add('text-red-600');
                            break;

                        case 'withdrawn':
                            statusText.textContent =
                                'Вы отозвали заявку.';
                            statusText.classList.add('text-gray-500');
                            break;

                        default:
                            return;
                    }

                    applicationStatus.appendChild(statusText);
                };

                channel.listen('.message.sent', (event) => {
                    if (
                        document.querySelector(
                            `[data-message-id="${event.id}"]`
                        )
                    ) {
                        return;
                    }

                    document
                        .getElementById('empty-messages')
                        ?.remove();

                    const message = document.createElement('div');

                    message.dataset.messageId = event.id;

                    const isOwnMessage =
                        Number(event.sender_id) === currentUserId;

                    message.className = isOwnMessage
                        ? 'max-w-2xl rounded-lg p-4 ml-auto bg-gray-100'
                        : 'max-w-2xl rounded-lg p-4 mr-auto border border-gray-200';

                    const sender = document.createElement('div');

                    sender.className =
                        'text-sm font-medium text-gray-900';

                    sender.textContent = event.sender_name;

                    const body = document.createElement('div');

                    body.className =
                        'mt-1 whitespace-pre-line text-gray-700';

                    body.textContent = event.body;

                    const footer = document.createElement('div');

                    footer.className =
                        'mt-2 flex items-center gap-3 text-xs text-gray-400';

                    const createdAt = document.createElement('span');

                    createdAt.textContent = event.created_at;

                    footer.appendChild(createdAt);

                    if (!isOwnMessage) {
                        const reportLink = document.createElement('a');

                        const reportUrlTemplate =
                            messages.dataset.reportMessageUrlTemplate;

                        if (reportUrlTemplate) {
                            reportLink.href = reportUrlTemplate.replace(
                                '__MESSAGE_ID__',
                                String(event.id)
                            );

                            reportLink.textContent = 'Пожаловаться';

                            reportLink.className =
                                'font-medium text-red-500 hover:text-red-700';

                            footer.appendChild(reportLink);
                        }
                    }

                    message.append(
                        sender,
                        body,
                        footer
                    );

                    messages.appendChild(message);

                    message.scrollIntoView({
                        behavior: 'smooth',
                        block: 'end',
                    });
                });

                channel.listen(
                    '.application.status.changed',
                    (event) => {
                        if (!applicationStatus) {
                            return;
                        }

                        if (
                            Number(
                                applicationStatus.dataset.applicationId
                            ) !== Number(event.application_id)
                        ) {
                            return;
                        }

                        renderApplicationStatus(event.status);
                    }
                );

                if (messageForm && textarea) {
                    messageForm.addEventListener(
                        'submit',
                        async (event) => {
                            event.preventDefault();

                            const body = textarea.value.trim();

                            if (!body) {
                                return;
                            }

                            textarea.value = '';
                            textarea.focus();

                            const submitButton =
                                messageForm.querySelector(
                                    'button[type="submit"]'
                                );

                            if (submitButton) {
                                submitButton.disabled = true;
                            }

                            try {
                                const response = await fetch(
                                    messageForm.action,
                                    {
                                        method: 'POST',

                                        headers: {
                                            'Accept': 'application/json',
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': csrfToken,
                                        },

                                        body: JSON.stringify({
                                            body: body,
                                        }),
                                    }
                                );

                                if (!response.ok) {
                                    const data = await response.json();

                                    throw new Error(
                                        data.message
                                        ?? 'Не удалось отправить сообщение.'
                                    );
                                }
                            } catch (error) {
                                console.error(error);

                                alert(
                                    error.message
                                    ?? 'Не удалось отправить сообщение. Попробуйте ещё раз.'
                                );
                            } finally {
                                if (submitButton) {
                                    submitButton.disabled = false;
                                }
                            }
                        }
                    );
                }

                document.addEventListener(
                                        'submit',
                    async (event) => {
                        const applicationForm = event.target.closest(
                            '.application-action-form'
                        );

                        if (!applicationForm) {
                            return;
                        }

                        event.preventDefault();

                        const submitButton =
                            applicationForm.querySelector(
                                'button[type="submit"]'
                            );

                        if (submitButton) {
                            submitButton.disabled = true;
                        }

                        try {
                            const response = await fetch(
                                applicationForm.action,
                                {
                                    method: 'POST',

                                    headers: {
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken,
                                    },
                                }
                            );

                            if (!response.ok) {
                                const data = await response.json();

                                throw new Error(
                                    data.message
                                    ?? 'Не удалось изменить решение.'
                                );
                            }
                        } catch (error) {
                            console.error(error);

                            alert(
                                error.message
                                ?? 'Не удалось изменить решение. Попробуйте ещё раз.'
                            );

                            if (submitButton) {
                                submitButton.disabled = false;
                            }
                        }
                    }
                );
            });
        </script>
    @endpush
</x-app-layout>