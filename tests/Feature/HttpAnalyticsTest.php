<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $this->get(route('http.analytics'))
        ->assertRedirect(route('login'));
});

test('authenticated users can visit the http analytics page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('http.analytics'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('http/Analytics')
            ->has('logs', 0)
            ->where('pagination.current_page', 1)
            ->where('pagination.last_page', 1)
            ->where('pagination.per_page', 15)
            ->where('pagination.total', 0)
        );
});
