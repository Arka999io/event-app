<?php

namespace App\Http\Controllers\Web;

use App\Actions\CancelEventAction;
use App\Actions\CompleteEventAction;
use App\Actions\CreateApplicationAction;
use App\Actions\CreateEventAction;
use App\Actions\DeleteEventPhotoAction;
use App\Actions\PublishEventAction;
use App\Actions\StartConversationAction;
use App\Actions\UpdateEventAction;
use App\Actions\UploadEventPhotosAction;
use App\DTO\CreateEventDTO;
use App\Enums\ApplicationStatus;
use App\Enums\EventStatus;
use App\Enums\GenderPreference;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Requests\UploadEventPhotosRequest;
use App\Models\Category;
use App\Models\Event;
use App\Models\Photo;
use App\Models\Review;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\View as ViewFacade;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $query = Event::query()
            ->where('status', EventStatus::Published->value)
            ->where('starts_at', '>', now())
            ->with([
                'organizer.profile.avatar',
                'category',
                'coverPhoto',
            ])
            ->orderBy('starts_at');

        if ($request->user() !== null) {
            $query->visibleTo($request->user());
        }

        $search = trim((string) $request->query('search', ''));

        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query
                    ->where('title', 'ilike', '%'.$search.'%')
                    ->orWhere('description', 'ilike', '%'.$search.'%')
                    ->orWhere('venue_address', 'ilike', '%'.$search.'%');
            });
        }

        $categoryId = $request->integer('category');

        if ($categoryId > 0) {
            $query->where('category_id', $categoryId);
        }

        $city = trim((string) $request->query('city', ''));

        if ($city !== '') {
            $query->where(
                'venue_address',
                'ilike',
                '%'.$city.'%'
            );
        }

        $dateFrom = $request->query('date_from');

        if (is_string($dateFrom) && $dateFrom !== '') {
            $query->whereDate(
                'starts_at',
                '>=',
                $dateFrom
            );
        }

        $dateTo = $request->query('date_to');

        if (is_string($dateTo) && $dateTo !== '') {
            $query->whereDate(
                'starts_at',
                '<=',
                $dateTo
            );
        }

        $genderPreference = $request->query(
            'gender_preference'
        );

        if (
            is_string($genderPreference)
            && in_array(
                $genderPreference,
                array_column(
                    GenderPreference::cases(),
                    'value'
                ),
                true
            )
        ) {
            $query->where(
                'gender_preference',
                $genderPreference
            );
        }

        if ($request->boolean('available_only')) {
            $query->whereColumn(
                'spots_taken',
                '<',
                'spots_total'
            );
        }

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $events = $query
            ->paginate(12)
            ->withQueryString();

        return ViewFacade::make('events.index', [
            'events' => $events,
            'categories' => $categories,
            'genderPreferences' => GenderPreference::cases(),
        ]);
    }

    public function show(Request $request, Event $event): View
    {
        $this->authorize('view', $event);

        $event->load([
            'organizer.profile.avatar',
            'category',
            'photos',
            'coverPhoto',
        ]);

        $application = null;
        $acceptedParticipants = collect();
        $reviewedUserIds = collect();

        if ($request->user() !== null) {
            /** @var User $currentUser */
            $currentUser = $request->user();

            $application = $event->applications()
                ->where(
                    'applicant_id',
                    $currentUser->id
                )
                ->first();

            if (
                $event->status === EventStatus::Completed
                && $currentUser->id === $event->organizer_id
            ) {
                $acceptedParticipants = $event
                    ->applications()
                    ->where(
                        'status',
                        ApplicationStatus::Accepted->value
                    )
                    ->with([
                        'applicant.profile.avatar',
                    ])
                    ->get()
                    ->pluck('applicant');
            }

            if ($event->status === EventStatus::Completed) {
                $reviewedUserIds = Review::query()
                    ->where('event_id', $event->id)
                    ->where(
                        'author_id',
                        $currentUser->id
                    )
                    ->pluck('subject_id');
            }
        }

        return ViewFacade::make('events.show', [
            'event' => $event,
            'application' => $application,
            'acceptedParticipants' => $acceptedParticipants,
            'reviewedUserIds' => $reviewedUserIds,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Event::class);

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return ViewFacade::make('events.create', [
            'categories' => $categories,
            'genderPreferences' => GenderPreference::cases(),
        ]);
    }

    public function store(
        StoreEventRequest $request,
        CreateEventAction $action,
    ): RedirectResponse {
        $validated = $request->validated();

        /** @var User $user */
        $user = $request->user();

        $data = new CreateEventDTO(
            categoryId: (int) $validated['category_id'],
            title: $validated['title'],
            description: $validated['description'],
            startsAt: CarbonImmutable::parse($validated['starts_at']),
            endsAt: isset($validated['ends_at'])
                ? CarbonImmutable::parse($validated['ends_at'])
                : null,
            meetingAddress: $validated['meeting_address'] ?? null,
            venueAddress: $validated['venue_address'],
            minAge: isset($validated['min_age'])
                ? (int) $validated['min_age']
                : null,
            maxAge: isset($validated['max_age'])
                ? (int) $validated['max_age']
                : null,
            genderPreference: GenderPreference::from(
                $validated['gender_preference']
            ),
            spotsTotal: (int) $validated['spots_total'],
        );

        $event = $action->execute(
            organizer: $user,
            data: $data,
        );

        return redirect()
            ->route('events.show', $event)
            ->with('status', 'Event created successfully.');
    }

    public function update(
        UpdateEventRequest $request,
        Event $event,
        UpdateEventAction $action,
    ): RedirectResponse {
        $validated = $request->validated();

        $data = new CreateEventDTO(
            categoryId: (int) $validated['category_id'],
            title: $validated['title'],
            description: $validated['description'],
            startsAt: CarbonImmutable::parse(
                $validated['starts_at']
            ),
            endsAt: isset($validated['ends_at'])
                ? CarbonImmutable::parse(
                    $validated['ends_at']
                )
                : null,
            meetingAddress: $validated['meeting_address'] ?? null,
            venueAddress: $validated['venue_address'],
            minAge: isset($validated['min_age'])
                ? (int) $validated['min_age']
                : null,
            maxAge: isset($validated['max_age'])
                ? (int) $validated['max_age']
                : null,
            genderPreference: GenderPreference::from(
                $validated['gender_preference']
            ),
            spotsTotal: (int) $validated['spots_total'],
        );

        $action->execute(
            event: $event,
            data: $data,
        );

        return redirect()
            ->route('events.show', $event)
            ->with(
                'status',
                'Мероприятие обновлено.'
            );
    }

    public function publish(
        Event $event,
        PublishEventAction $action,
    ): RedirectResponse {
        $this->authorize('publish', $event);

        $action->execute($event);

        return redirect()
            ->route('events.show', $event)
            ->with('status', 'Мероприятие опубликовано.');
    }

    public function cancel(
        Event $event,
        CancelEventAction $action,
    ): RedirectResponse {
        $this->authorize('cancel', $event);

        abort_if(
            in_array(
                $event->status,
                [
                    EventStatus::Completed,
                    EventStatus::Cancelled,
                ],
                true
            ),
            403
        );

        $action->execute($event);

        return redirect()
            ->route('events.show', $event)
            ->with(
                'status',
                'Мероприятие отменено.'
            );
    }

    public function complete(
        Event $event,
        CompleteEventAction $action,
    ): RedirectResponse {
        $this->authorize('complete', $event);

        $action->execute($event);

        return redirect()
            ->route('events.show', $event)
            ->with(
                'status',
                'Мероприятие завершено.'
            );
    }

    public function apply(
        Request $request,
        Event $event,
        CreateApplicationAction $action,
    ): RedirectResponse {
        $this->authorize('view', $event);

        /** @var User $user */
        $user = $request->user();

        $action->execute(
            applicant: $user,
            event: $event,
        );

        return redirect()
            ->route('events.show', $event)
            ->with('status', 'Заявка на участие отправлена.');
    }

    public function startConversation(
        Request $request,
        Event $event,
        StartConversationAction $action,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        $conversation = $action->execute(
            user: $user,
            event: $event,
        );

        return redirect()
            ->route('conversations.show', $conversation);
    }

    public function uploadPhotos(
        UploadEventPhotosRequest $request,
        Event $event,
        UploadEventPhotosAction $action,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        abort_unless(
            $user->id === $event->organizer_id,
            403
        );

        abort_if(
            in_array(
                $event->status,
                [
                    EventStatus::Completed,
                    EventStatus::Cancelled,
                ],
                true
            ),
            403
        );

        /** @var array<int, UploadedFile> $photos */
        $photos = $request->file('photos');

        $action->execute(
            event: $event,
            photos: $photos,
        );

        return redirect()
            ->route('events.show', $event)
            ->with(
                'status',
                'Фотографии добавлены.'
            );
    }

    public function deletePhoto(
        Request $request,
        Event $event,
        Photo $photo,
        DeleteEventPhotoAction $action,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        abort_unless(
            $user->id === $event->organizer_id,
            403
        );

        abort_if(
            in_array(
                $event->status,
                [
                    EventStatus::Completed,
                    EventStatus::Cancelled,
                ],
                true
            ),
            403
        );

        abort_unless(
            $photo->photoable_type === Event::class
            && $photo->photoable_id === $event->id,
            404
        );

        $action->execute(
            event: $event,
            photo: $photo,
        );

        return redirect()
            ->route('events.show', $event)
            ->with('status', 'Фотография удалена.');
    }

    public function edit(Event $event): View
    {
        $this->authorize('update', $event);

        abort_if(
            in_array(
                $event->status,
                [
                    EventStatus::Completed,
                    EventStatus::Cancelled,
                ],
                true
            ),
            403
        );

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return ViewFacade::make('events.edit', [
            'event' => $event,
            'categories' => $categories,
            'genderPreferences' => GenderPreference::cases(),
        ]);
    }
}
