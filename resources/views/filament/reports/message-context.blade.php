@php
    /** @var \App\Models\Report $record */

    $reportedMessage = $record->reportable;

    $contextMessages = collect();

    if ($reportedMessage instanceof \App\Models\Message) {
        /*
         * Получаем максимум 10 сообщений
         * перед сообщением, на которое пожаловались.
         */
        $before = \App\Models\Message::query()
            ->where(
                'conversation_id',
                $reportedMessage->conversation_id
            )
            ->where('id', '<', $reportedMessage->id)
            ->with('sender.profile')
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->reverse()
            ->values();

        /*
         * Получаем максимум 10 сообщений после.
         */
        $after = \App\Models\Message::query()
            ->where(
                'conversation_id',
                $reportedMessage->conversation_id
            )
            ->where('id', '>', $reportedMessage->id)
            ->with('sender.profile')
            ->orderBy('id')
            ->limit(10)
            ->get();

        $reportedMessage->loadMissing(
            'sender.profile'
        );

        $contextMessages = $before
            ->push($reportedMessage)
            ->concat($after)
            ->values();
    }

    $reporterId = $record->reporter_id;
@endphp

<div
    style="
        margin-top: 16px;
        border: 1px solid #374151;
        border-radius: 12px;
        overflow: hidden;
        background: #111827;
    "
>
    {{-- Заголовок --}}
    <div
        style="
            padding: 18px 20px;
            border-bottom: 1px solid #374151;
            background: #1f2937;
        "
    >
        <div
            style="
                font-size: 16px;
                font-weight: 700;
                color: #ffffff;
            "
        >
            Контекст переписки
        </div>

        <div
            style="
                margin-top: 6px;
                font-size: 13px;
                line-height: 1.5;
                color: #9ca3af;
            "
        >
            Показано до 10 сообщений перед жалобой
            и до 10 после неё.
        </div>

        <div
            style="
                margin-top: 12px;
                display: flex;
                flex-wrap: wrap;
                gap: 14px;
                font-size: 12px;
            "
        >
            <span style="color: #ffffff;">
                ● Автор жалобы
            </span>

            <span style="color: #facc15;">
                ● Пользователь, на которого пожаловались
            </span>

            <span style="color: #ef4444;">
                ● Сообщение, на которое пожаловались
            </span>
        </div>
    </div>

    @if ($contextMessages->isEmpty())
        <div
            style="
                padding: 24px;
                text-align: center;
                color: #9ca3af;
                font-size: 14px;
            "
        >
            Контекст переписки недоступен.
        </div>
    @else
        {{-- Сообщения --}}
        <div
            style="
                padding: 20px;
                display: flex;
                flex-direction: column;
                gap: 14px;
            "
        >
            @foreach ($contextMessages as $message)
                @php
                    $isReported =
                        $message->id === $reportedMessage->id;

                    $isReporter =
                        $message->sender_id === $reporterId;

                    $senderName = trim(
                        ($message->sender->profile?->first_name ?? '')
                        .' '
                        .($message->sender->profile?->last_name ?? '')
                    );

                    if ($senderName === '') {
                        $senderName = $message->sender->name;
                    }

                    /*
                     * Автор жалобы располагается слева.
                     * Второй участник — справа.
                     */
                    $alignment = $isReporter
                        ? 'flex-start'
                        : 'flex-end';

                    /*
                     * Цвет имени:
                     *
                     * автор жалобы — белый;
                     * второй пользователь — жёлтый.
                     */
                    $nameColor = $isReporter
                        ? '#ffffff'
                        : '#facc15';

                    /*
                     * Жалобное сообщение получает
                     * красный текст.
                     */
                    $messageColor = $isReported
                        ? '#f87171'
                        : '#e5e7eb';

                    $borderColor = $isReported
                        ? '#ef4444'
                        : '#374151';

                    $backgroundColor = $isReported
                        ? '#2b1518'
                        : '#1f2937';
                @endphp

                <div
                    style="
                        display: flex;
                        justify-content: {{ $alignment }};
                    "
                >
                    <div
                        style="
                            width: min(75%, 720px);
                            padding: 14px 16px;
                            border: 1px solid {{ $borderColor }};
                            border-radius: 12px;
                            background: {{ $backgroundColor }};
                            {{ $isReported
                                ? 'box-shadow: 0 0 0 1px rgba(239, 68, 68, 0.20);'
                                : ''
                            }}
                        "
                    >
                        {{-- Метка жалобного сообщения --}}
                        @if ($isReported)
                            <div
                                style="
                                    margin-bottom: 10px;
                                    padding-bottom: 8px;
                                    border-bottom: 1px solid #7f1d1d;
                                    color: #ef4444;
                                    font-size: 11px;
                                    font-weight: 700;
                                    text-transform: uppercase;
                                    letter-spacing: 0.05em;
                                "
                            >
                                ⚠ Сообщение, на которое пожаловались
                            </div>
                        @endif

                        {{-- Автор + время --}}
                        <div
                            style="
                                display: flex;
                                align-items: center;
                                justify-content: space-between;
                                gap: 16px;
                            "
                        >
                            <div
                                style="
                                    color: {{ $nameColor }};
                                    font-size: 14px;
                                    font-weight: 700;
                                "
                            >
                                {{ $senderName }}
                            </div>

                            <div
                                style="
                                    flex-shrink: 0;
                                    color: #9ca3af;
                                    font-size: 11px;
                                "
                            >
                                {{ $message->created_at->format(
                                    'd.m.Y H:i'
                                ) }}
                            </div>
                        </div>

                        {{-- Роль пользователя --}}
                        <div
                            style="
                                margin-top: 3px;
                                color: {{ $isReporter
                                    ? '#d1d5db'
                                    : '#fde047'
                                }};
                                font-size: 11px;
                            "
                        >
                            @if ($isReporter)
                                Автор жалобы
                            @else
                                Пользователь, на которого пожаловались
                            @endif
                        </div>

                        {{-- Текст сообщения --}}
                        <div
                            style="
                                margin-top: 10px;
                                color: {{ $messageColor }};
                                font-size: 14px;
                                line-height: 1.55;
                                white-space: pre-wrap;
                                overflow-wrap: anywhere;
                                {{ $isReported
                                    ? 'font-weight: 600;'
                                    : ''
                                }}
                            "
                        >{{ $message->body }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Подвал --}}
        <div
            style="
                padding: 12px 20px;
                border-top: 1px solid #374151;
                background: #1f2937;
                color: #9ca3af;
                font-size: 12px;
            "
        >
            Показано сообщений:
            <strong style="color: #ffffff;">
                {{ $contextMessages->count() }}
            </strong>
        </div>
    @endif
</div>