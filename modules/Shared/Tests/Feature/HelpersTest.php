<?php

use Modules\User\Models\User;

test('user() returns the authenticated user', function () {
    $authenticated = User::factory()->create();

    $this->actingAs($authenticated);

    expect(user())->not->toBeNull()
        ->and(user()->getAuthIdentifier())->toBe($authenticated->getAuthIdentifier());
});

test('user() returns null for a guest', function () {
    expect(user())->toBeNull();
});
