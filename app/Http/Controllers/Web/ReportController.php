<?php

namespace App\Http\Controllers\Web;

use App\Actions\CreateReportAction;
use App\Enums\ReportReason;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Message;
use App\Models\User;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function createForUser(
        Request $request,
        User $user,
    ): View {
        return $this->makeForm(
            reportable: $user,
            type: 'user',
            title: 'Пожаловаться на пользователя',
            backUrl: route('users.show', $user),
        );
    }

    public function createForEvent(
        Request $request,
        Event $event,
    ): View {
        $this->authorize('view', $event);

        return $this->makeForm(
            reportable: $event,
            type: 'event',
            title: 'Пожаловаться на мероприятие',
            backUrl: route('events.show', $event),
        );
    }

    public function createForMessage(
        Request $request,
        Message $message,
    ): View {
        return $this->makeForm(
            reportable: $message,
            type: 'message',
            title: 'Пожаловаться на сообщение',
            backUrl: url()->previous(),
        );
    }

    public function storeForUser(
        Request $request,
        User $user,
        CreateReportAction $action,
    ): RedirectResponse {
        return $this->store(
            request: $request,
            reportable: $user,
            action: $action,
            redirectUrl: route('users.show', $user),
        );
    }

    public function storeForEvent(
        Request $request,
        Event $event,
        CreateReportAction $action,
    ): RedirectResponse {
        $this->authorize('view', $event);

        return $this->store(
            request: $request,
            reportable: $event,
            action: $action,
            redirectUrl: route('events.show', $event),
        );
    }

    public function storeForMessage(
        Request $request,
        Message $message,
        CreateReportAction $action,
    ): RedirectResponse {
        return $this->store(
            request: $request,
            reportable: $message,
            action: $action,
            redirectUrl: route(
                'conversations.show',
                $message->conversation_id
            ),
        );
    }

    private function makeForm(
        Model $reportable,
        string $type,
        string $title,
        string $backUrl,
    ): View {
        /** @var view-string $view */
        $view = 'reports.create';

        return ViewFacade::make($view, [
            'reportable' => $reportable,
            'reportableType' => $type,
            'title' => $title,
            'backUrl' => $backUrl,
            'reasons' => ReportReason::cases(),
        ]);
    }

    private function store(
        Request $request,
        Model $reportable,
        CreateReportAction $action,
        string $redirectUrl,
    ): RedirectResponse {
        /** @var User $reporter */
        $reporter = $request->user();

        $validated = $request->validate([
            'reason' => [
                'required',
                Rule::enum(ReportReason::class),
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        try {
            $action->execute(
                reporter: $reporter,
                reportable: $reportable,
                reason: ReportReason::from(
                    $validated['reason']
                ),
                description: $validated['description'] ?? null,
            );
        } catch (DomainException $exception) {
            return back()
                ->withInput()
                ->withErrors([
                    'report' => $exception->getMessage(),
                ]);
        }

        return redirect($redirectUrl)
            ->with(
                'status',
                'Жалоба отправлена.'
            );
    }
}
