<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\EventController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\ConversationController;
use App\Http\Controllers\Web\ApplicationController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\ReviewController;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Public event routes
|--------------------------------------------------------------------------
*/

Route::get('/events', [EventController::class, 'index'])
    ->name('events.index');

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'not-banned',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })
        ->middleware('verified')
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::post(
        '/profile/avatar',
        [ProfileController::class, 'updateAvatar']
    )->name('profile.avatar.update');

    Route::patch(
        '/profile/personal',
        [ProfileController::class, 'updatePersonalProfile']
    )->name('profile.personal.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::get('/events/create', [EventController::class, 'create'])
        ->name('events.create');

    Route::get(
        '/events/{event}/edit',
        [EventController::class, 'edit']
    )
        ->whereNumber('event')
        ->name('events.edit');

    Route::patch(
        '/events/{event}',
        [EventController::class, 'update']
    )
    ->whereNumber('event')
    ->name('events.update');

    Route::post('/events', [EventController::class, 'store'])
        ->name('events.store');

    Route::post('/events/{event}/publish', [EventController::class, 'publish'])
        ->whereNumber('event')
        ->name('events.publish');

    Route::post(
        '/events/{event}/cancel',
        [EventController::class, 'cancel']
    )
    ->whereNumber('event')
    ->name('events.cancel');

    Route::post(
        '/events/{event}/complete',
        [EventController::class, 'complete']
    )
        ->whereNumber('event')
        ->name('events.complete');

    Route::post('/events/{event}/apply', [EventController::class, 'apply'])
        ->whereNumber('event')
        ->name('events.apply');

    Route::post('/events/{event}/conversation', [EventController::class, 'startConversation'])
        ->whereNumber('event')
        ->name('events.conversation.start');

    Route::post(
        '/events/{event}/photos',
        [EventController::class, 'uploadPhotos']
    )
        ->whereNumber('event')
        ->name('events.photos.store');

    Route::delete(
        '/events/{event}/photos/{photo}',
        [EventController::class, 'deletePhoto']
    )
        ->whereNumber('event')
        ->whereNumber('photo')
        ->name('events.photos.destroy');

    Route::get('/conversations', [ConversationController::class, 'index'])
        ->name('conversations.index');

    Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])
        ->whereNumber('conversation')
        ->name('conversations.show');

    Route::post('/conversations/{conversation}/messages', [ConversationController::class, 'sendMessage'])
        ->whereNumber('conversation')
        ->name('conversations.messages.store');
        Route::get('/applications/received', [ApplicationController::class, 'received'])
    ->name('applications.received');

    Route::get('/applications/sent', [ApplicationController::class, 'sent'])
        ->name('applications.sent');

    Route::post('/applications/{application}/accept', [ApplicationController::class, 'accept'])
        ->whereNumber('application')
        ->name('applications.accept');

    Route::post('/applications/{application}/reject', [ApplicationController::class, 'reject'])
        ->whereNumber('application')
        ->name('applications.reject');

    Route::post('/applications/{application}/waitlist', [ApplicationController::class, 'waitlist'])
        ->whereNumber('application')
        ->name('applications.waitlist');

    Route::post(
        '/applications/{application}/conversation',
        [ApplicationController::class, 'conversation']
    )
        ->whereNumber('application')
        ->name('applications.conversation');

    Route::get(
        '/notifications',
        [NotificationController::class, 'index']
    )->name('notifications.index');

    Route::post(
        '/notifications/read-all',
        [NotificationController::class, 'markAllAsRead']
    )->name('notifications.read-all');

    Route::get(
        '/notifications/{notification}',
        [NotificationController::class, 'open']
    )->name('notifications.open');

    Route::get(
        '/users/{user}/report',
        [ReportController::class, 'createForUser']
    )
        ->whereNumber('user')
        ->name('reports.users.create');

    Route::post(
        '/users/{user}/report',
        [ReportController::class, 'storeForUser']
    )
        ->whereNumber('user')
        ->name('reports.users.store');

    Route::get(
        '/events/{event}/report',
        [ReportController::class, 'createForEvent']
    )
        ->whereNumber('event')
        ->name('reports.events.create');

    Route::post(
        '/events/{event}/report',
        [ReportController::class, 'storeForEvent']
    )
        ->whereNumber('event')
        ->name('reports.events.store');

    Route::get(
        '/messages/{message}/report',
        [ReportController::class, 'createForMessage']
    )
        ->whereNumber('message')
        ->name('reports.messages.create');

    Route::post(
        '/messages/{message}/report',
        [ReportController::class, 'storeForMessage']
    )
        ->whereNumber('message')
        ->name('reports.messages.store');

    Route::get(
        '/events/{event}/reviews/{user}/create',
        [ReviewController::class, 'create']
    )
        ->whereNumber('event')
        ->whereNumber('user')
        ->name('reviews.create');

    Route::post(
        '/events/{event}/reviews/{user}',
        [ReviewController::class, 'store']
    )
        ->whereNumber('event')
        ->whereNumber('user')
        ->name('reviews.store');

    Route::post(
        '/users/{user}/block',
        [UserController::class, 'block']
    )
        ->whereNumber('user')
        ->name('users.block');

    Route::delete(
        '/users/{user}/block',
        [UserController::class, 'unblock']
    )
        ->whereNumber('user')
        ->name('users.unblock');
});

/*
|--------------------------------------------------------------------------
| Public event page
|--------------------------------------------------------------------------
*/

Route::get('/events/{event}', [EventController::class, 'show'])
    ->whereNumber('event')
    ->name('events.show');

Route::get('/users/{user}', [UserController::class, 'show'])
    ->whereNumber('user')
    ->name('users.show');

Route::get('/speed-test', function () {
    return 'OK';
});
require __DIR__.'/auth.php';