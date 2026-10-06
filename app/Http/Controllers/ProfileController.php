<?php

namespace App\Http\Controllers;

use App\Actions\UpdateAvatarAction;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\UpdateAvatarRequest;
use App\Http\Requests\UpdatePersonalProfileRequest;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function updateAvatar(
        UpdateAvatarRequest $request,
        UpdateAvatarAction $action,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        /** @var Profile|null $profile */
        $profile = $user->profile;

        abort_if($profile === null, 404);

        /** @var UploadedFile $avatar */
        $avatar = $request->file('avatar');

        $action->execute(
            profile: $profile,
            avatar: $avatar,
        );

        return back()->with(
            'status',
            'avatar-updated'
        );
    }

    public function updatePersonalProfile(
        UpdatePersonalProfileRequest $request,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        /** @var Profile|null $profile */
        $profile = $user->profile;

        abort_if($profile === null, 404);

        $validated = $request->validated();

        $profile->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'city' => $validated['city'] ?? null,
            'bio' => $validated['bio'] ?? null,
        ]);

        return redirect()
            ->route('profile.edit')
            ->with('status', 'personal-profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
