<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $this->get(route('ping'))
        ->assertRedirect(route('login'));
});

test('authenticated users can visit the ping page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('ping'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Ping'));
});
