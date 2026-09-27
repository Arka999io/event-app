<?php

namespace App\Http\Controllers\Web;

use App\Actions\AcceptApplicationAction;
use App\Actions\RejectApplicationAction;
use App\Actions\WaitlistApplicationAction;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View as ViewFacade;
use App\Actions\StartConversationAction;
use App\Events\ApplicationStatusChanged;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;

class ApplicationController extends Controller
{
    public function received(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $applications = Application::query()
            ->whereHas(
                'event',
                fn (Builder $query) => $query
                    ->where('organizer_id', $user->id)
            )
            ->with([
                'applicant.profile.avatar',
                'event',
            ])
            ->orderByRaw(
                <<<'SQL'
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM events
                        WHERE events.id = applications.event_id
                        AND events.status IN ('completed', 'cancelled')
                    ) THEN 4
                    WHEN applications.status IN ('pending', 'waitlisted') THEN 1
                    WHEN applications.status = 'accepted' THEN 2
                    WHEN applications.status IN ('rejected', 'withdrawn') THEN 3
                    ELSE 5
                END
                SQL
            )
            ->orderByDesc('updated_at')
            ->paginate(20);

        /** @var view-string $view */
            $view = 'applications.received';

            return ViewFacade::make($view, [
                'applications' => $applications,
            ]);
                }

    public function sent(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $applications = Application::query()
            ->where('applicant_id', $user->id)
            ->with([
                'event.organizer',
                'event.photos',
            ])
            ->orderByRaw(
                <<<'SQL'
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM events
                        WHERE events.id = applications.event_id
                        AND events.status IN ('completed', 'cancelled')
                    ) THEN 4
                    WHEN applications.status IN ('pending', 'waitlisted') THEN 1
                    WHEN applications.status = 'accepted' THEN 2
                    WHEN applications.status IN ('rejected', 'withdrawn') THEN 3
                    ELSE 5
                END
                SQL
            )
            ->orderByDesc('updated_at')
            ->paginate(20);

        /** @var view-string $view */
        $view = 'applications.sent';

        return ViewFacade::make($view, [
            'applications' => $applications,
        ]);
    }

    public function accept(
        Application $application,
        AcceptApplicationAction $action,
    ): RedirectResponse|JsonResponse {
        $this->authorize('accept', $application);

        $action->execute($application);
        $this->broadcastStatusChange($application);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'status' => 'accepted',
            ]);
        }

        return back()->with(
            'status',
            'Пользователь приглашён на мероприятие.'
        );
    }

    public function reject(
        Application $application,
        RejectApplicationAction $action,
    ): RedirectResponse|JsonResponse {
        $this->authorize('reject', $application);

        $action->execute($application);
        $this->broadcastStatusChange($application);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'status' => 'rejected',
            ]);
        }

        return back()->with(
            'status',
            'В заявке отказано.'
        );
    }

    public function waitlist(
        Application $application,
        WaitlistApplicationAction $action,
    ): RedirectResponse {
        $this->authorize('waitlist', $application);

        $action->execute($application);

        return back()->with(
            'status',
            'Пользователь добавлен в лист ожидания.'
        );
    }

    public function conversation(
        Request $request,
        Application $application,
        StartConversationAction $action,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        $event = $application->event;

        $isApplicant = $user->id === $application->applicant_id;
        $isOrganizer = $user->id === $event->organizer_id;

        abort_unless($isApplicant || $isOrganizer, 403);

        $conversation = $action->execute(
            user: $application->applicant,
            event: $event,
        );

        return redirect()
            ->route('conversations.show', $conversation);
    }

    private function broadcastStatusChange(Application $application): void
    {
        $conversation = Conversation::query()
            ->where('event_id', $application->event_id)
            ->where('user_id', $application->applicant_id)
            ->first();

        if ($conversation === null) {
            return;
        }

        ApplicationStatusChanged::dispatch(
            application: $application->refresh(),
            conversation: $conversation,
        );
    }
}