<?php

namespace App\Http\Controllers\Web;

use App\Actions\MarkConversationAsReadAction;
use App\Actions\SendMessageAction;
use App\Actions\UsersAreBlockedAction;
use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\User;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View as ViewFacade;

class ConversationController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $conversations = Conversation::query()
            ->where(function (Builder $query) use ($user): void {
                $query
                    ->where('user_id', $user->id)
                    ->orWhereHas(
                        'event',
                        fn (Builder $eventQuery) => $eventQuery
                            ->where(
                                'organizer_id',
                                $user->id
                            )
                    );
            })
            ->withCount([
                'messages as unread_messages_count' =>
                    fn (Builder $query) => $query
                        ->where(
                            'sender_id',
                            '!=',
                            $user->id
                        )
                        ->whereNull('read_at'),
            ])
            ->with([
                'event.organizer.profile.avatar',
                'user.profile.avatar',

                'messages' => fn ($query) => $query
                    ->with('sender')
                    ->latest()
                    ->limit(1),
            ])
            ->latest('updated_at')
            ->paginate(20);

        return ViewFacade::make(
            'conversations.index',
            [
                'conversations' => $conversations,
            ]
        );
    }

    public function show(
        Request $request,
        Conversation $conversation,
        MarkConversationAsReadAction $markAsRead,
        UsersAreBlockedAction $usersAreBlocked,
    ): View {
        /** @var User $user */
        $user = $request->user();

        $conversation->load([
            'event.organizer.profile.avatar',
            'user.profile.avatar',
        ]);

        $this->ensureParticipant(
            $user,
            $conversation
        );

        /*
         * Определяем собеседника текущего пользователя.
         *
         * Если текущий пользователь — организатор,
         * собеседником является conversation->user.
         *
         * Если текущий пользователь — обычный участник,
         * собеседником является организатор мероприятия.
         */
        $otherUser = $user->id
            === $conversation->event->organizer_id
                ? $conversation->user
                : $conversation->event->organizer;

        /*
         * Проверяем блокировку в обе стороны:
         *
         * current user -> other user
         * other user   -> current user
         */
        $isBlocked = $usersAreBlocked->execute(
            first: $user,
            second: $otherUser,
        );

        $markAsRead->execute(
            user: $user,
            conversation: $conversation,
        );

        $conversation->load([
            'messages.sender',
        ]);

        /*
         * Чат становится read-only, если:
         *
         * - мероприятие завершено;
         * - мероприятие отменено;
         * - один из пользователей заблокировал другого.
         */
        $isEventClosed = in_array(
            $conversation->event->status,
            [
                EventStatus::Completed,
                EventStatus::Cancelled,
            ],
            true
        );

        $isReadOnly = $isEventClosed
            || $isBlocked;

        $application = $conversation->event
            ->applications()
            ->where(
                'applicant_id',
                $conversation->user_id
            )
            ->first();

        return ViewFacade::make(
            'conversations.show',
            [
                'conversation' => $conversation,
                'isReadOnly' => $isReadOnly,
                'isBlocked' => $isBlocked,
                'application' => $application,
            ]
        );
    }

    public function sendMessage(
        Request $request,
        Conversation $conversation,
        SendMessageAction $action,
    ): RedirectResponse|JsonResponse {
        /** @var User $user */
        $user = $request->user();

        $conversation->load('event');

        $this->ensureParticipant(
            $user,
            $conversation
        );

        $validated = $request->validate([
            'body' => [
                'required',
                'string',
                'max:5000',
            ],
        ]);

        try {
            $action->execute(
                sender: $user,
                conversation: $conversation,
                body: $validated['body'],
            );
        } catch (DomainException $exception) {
            if ($request->expectsJson()) {
                return response()->json(
                    [
                        'message' =>
                            $exception->getMessage(),
                    ],
                    422
                );
            }

            return back()->withErrors([
                'body' => $exception->getMessage(),
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
            ]);
        }

        return redirect()
            ->route(
                'conversations.show',
                $conversation
            );
    }

    private function ensureParticipant(
        User $user,
        Conversation $conversation,
    ): void {
        $isParticipant =
            $user->id === $conversation->user_id
            || $user->id
                === $conversation->event->organizer_id;

        abort_unless(
            $isParticipant,
            403
        );
    }
}