<?php

namespace App\Http\Controllers\Web;

use App\Actions\CreateReviewAction;
use App\Enums\ApplicationStatus;
use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Event;
use App\Models\User;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View as ViewFacade;

class ReviewController extends Controller
{
    public function create(
        Request $request,
        Event $event,
        User $user,
    ): View {
        /** @var User $author */
        $author = $request->user();

        abort_unless(
            $event->status === EventStatus::Completed,
            404
        );

        $this->ensureReviewPairIsValid(
            author: $author,
            subject: $user,
            event: $event,
        );

        /** @var view-string $view */
        $view = 'reviews.create';

        return ViewFacade::make($view, [
            'event' => $event,
            'subject' => $user,
        ]);
    }

    public function store(
        Request $request,
        Event $event,
        User $user,
        CreateReviewAction $action,
    ): RedirectResponse {
        /** @var User $author */
        $author = $request->user();

        $validated = $request->validate([
            'rating' => [
                'required',
                'integer',
                'between:1,5',
            ],
            'comment' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        try {
            $action->execute(
                author: $author,
                subject: $user,
                event: $event,
                rating: (int) $validated['rating'],
                comment: $validated['comment'] ?? null,
            );
        } catch (DomainException $exception) {
            return back()
                ->withInput()
                ->withErrors([
                    'review' => $exception->getMessage(),
                ]);
        }

        return redirect()
            ->route('users.show', $user)
            ->with(
                'status',
                'Отзыв опубликован.'
            );
    }

    private function ensureReviewPairIsValid(
        User $author,
        User $subject,
        Event $event,
    ): void {
        abort_if(
            $author->id === $subject->id,
            403
        );

        $authorIsOrganizer =
            $author->id === $event->organizer_id;

        $subjectIsOrganizer =
            $subject->id === $event->organizer_id;

        abort_if(
            $authorIsOrganizer === $subjectIsOrganizer,
            403
        );

        $participant = $authorIsOrganizer
            ? $subject
            : $author;

        $accepted = Application::query()
            ->where('event_id', $event->id)
            ->where(
                'applicant_id',
                $participant->id
            )
            ->where(
                'status',
                ApplicationStatus::Accepted->value
            )
            ->exists();

        abort_unless($accepted, 403);
    }
}