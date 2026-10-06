<?php

namespace App\Http\Controllers\Web;

use App\Actions\BlockUserAction;
use App\Actions\UnblockUserAction;
use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\User;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View as ViewFacade;

class UserController extends Controller
{
    public function show(
        Request $request,
        User $user,
    ): View {
        $user->load([
            'profile.avatar',
        ]);

        $reviews = $user
            ->receivedReviews()
            ->with([
                'author.profile.avatar',
                'event',
            ])
            ->latest()
            ->paginate(10);

        $reviewsCount = $user
            ->receivedReviews()
            ->count();

        $averageRating = $reviewsCount > 0
            ? (float) $user
                ->receivedReviews()
                ->avg('rating')
            : null;

        $blockedByCurrentUser = false;
        $currentUserBlockedByProfileUser = false;

        if (
            $request->user() !== null
            && $request->user()->id !== $user->id
        ) {
            /** @var User $currentUser */
            $currentUser = $request->user();

            $blockedByCurrentUser = Block::query()
                ->where(
                    'blocker_id',
                    $currentUser->id
                )
                ->where(
                    'blocked_id',
                    $user->id
                )
                ->exists();

            $currentUserBlockedByProfileUser = Block::query()
                ->where(
                    'blocker_id',
                    $user->id
                )
                ->where(
                    'blocked_id',
                    $currentUser->id
                )
                ->exists();
        }

        /** @var view-string $view */
        $view = 'users.show';

        return ViewFacade::make($view, [
            'profileUser' => $user,
            'reviews' => $reviews,
            'reviewsCount' => $reviewsCount,
            'averageRating' => $averageRating,
            'blockedByCurrentUser' => $blockedByCurrentUser,
            'currentUserBlockedByProfileUser' => $currentUserBlockedByProfileUser,
        ]);
    }

    public function block(
        Request $request,
        User $user,
        BlockUserAction $action,
    ): RedirectResponse {
        /** @var User $currentUser */
        $currentUser = $request->user();

        try {
            $action->execute(
                blocker: $currentUser,
                blocked: $user,
            );
        } catch (DomainException $exception) {
            return back()->withErrors([
                'block' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('users.show', $user)
            ->with(
                'status',
                'Пользователь заблокирован.'
            );
    }

    public function unblock(
        Request $request,
        User $user,
        UnblockUserAction $action,
    ): RedirectResponse {
        /** @var User $currentUser */
        $currentUser = $request->user();

        try {
            $action->execute(
                blocker: $currentUser,
                blocked: $user,
            );
        } catch (DomainException $exception) {
            return back()->withErrors([
                'block' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('users.show', $user)
            ->with(
                'status',
                'Пользователь разблокирован.'
            );
    }
}
