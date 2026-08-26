<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $this->get(route('http'))
        ->assertRedirect(route('login'));
});

test('authenticated users can visit the http page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('http'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Http')
            ->has('logs', 0)
        );
});
