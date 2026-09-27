<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\View as ViewFacade;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $notifications = $user
            ->notifications()
            ->latest()
            ->paginate(20);

        /** @var view-string $view */
        $view = 'notifications.index';

        return ViewFacade::make($view, [
            'notifications' => $notifications,
        ]);
    }

    public function open(
        Request $request,
        DatabaseNotification $notification,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        abort_unless(
            $notification->notifiable_type === User::class
            && (int) $notification->notifiable_id === $user->id,
            403
        );

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        $data = $notification->data;

        return match ($data['type'] ?? null) {
            'new_message' => redirect()->route(
                'conversations.show',
                $data['conversation_id']
            ),

            'new_application' => redirect()->route(
                'applications.received'
            ),

            'application_accepted',
            'application_rejected' => redirect()->route(
                'events.show',
                $data['event_id']
            ),

            default => redirect()->route(
                'notifications.index'
            ),
        };
    }

    public function markAllAsRead(
        Request $request,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        $user->unreadNotifications->markAsRead();

        return redirect()
            ->route('notifications.index')
            ->with(
                'status',
                'Все уведомления отмечены как прочитанные.'
            );
    }
}