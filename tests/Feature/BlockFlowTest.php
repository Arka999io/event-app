<?php

use App\Actions\BlockUserAction;
use App\Actions\UnblockUserAction;
use App\Actions\UsersAreBlockedAction;
use App\Models\Block;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows a user to block another user', function () {
    $blocker = User::factory()->create();
    $blocked = User::factory()->create();

    $block = app(BlockUserAction::class)->execute(
        blocker: $blocker,
        blocked: $blocked,
    );

    expect($block)
        ->blocker_id->toBe($blocker->id)
        ->blocked_id->toBe($blocked->id);

    $this->assertDatabaseHas('blocks', [
        'blocker_id' => $blocker->id,
        'blocked_id' => $blocked->id,
    ]);
});

it('does not create a duplicate block', function () {
    $blocker = User::factory()->create();
    $blocked = User::factory()->create();

    $action = app(BlockUserAction::class);

    $first = $action->execute($blocker, $blocked);
    $second = $action->execute($blocker, $blocked);

    expect($second->id)->toBe($first->id);

    $this->assertDatabaseCount('blocks', 1);
});

it('does not allow a user to block themselves', function () {
    $user = User::factory()->create();

    expect(fn () => app(BlockUserAction::class)->execute(
        blocker: $user,
        blocked: $user,
    ))->toThrow(
        DomainException::class,
        'You cannot block yourself.',
    );

    $this->assertDatabaseCount('blocks', 0);
});

it('treats a block as symmetric for interaction', function () {
    $first = User::factory()->create();
    $second = User::factory()->create();

    app(BlockUserAction::class)->execute(
        blocker: $first,
        blocked: $second,
    );

    $action = app(UsersAreBlockedAction::class);

    expect($action->execute($first, $second))->toBeTrue()
        ->and($action->execute($second, $first))->toBeTrue();
});

it('allows a user to remove their own block', function () {
    $blocker = User::factory()->create();
    $blocked = User::factory()->create();

    app(BlockUserAction::class)
        ->execute(
            blocker: $blocker,
            blocked: $blocked,
        );

    expect(
        Block::query()
            ->where('blocker_id', $blocker->id)
            ->where('blocked_id', $blocked->id)
            ->exists()
    )->toBeTrue();

    app(UnblockUserAction::class)
        ->execute(
            blocker: $blocker,
            blocked: $blocked,
        );

    expect(
        Block::query()
            ->where('blocker_id', $blocker->id)
            ->where('blocked_id', $blocked->id)
            ->exists()
    )->toBeFalse();
});
