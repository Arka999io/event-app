<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotBanned
{
    public function handle(
        Request $request,
        Closure $next,
    ): Response {
        /** @var User|null $user */
        $user = $request->user();

        if ($user !== null && $user->is_banned) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Ваш аккаунт заблокирован администрацией.',
                ]);
        }

        return $next($request);
    }
}
