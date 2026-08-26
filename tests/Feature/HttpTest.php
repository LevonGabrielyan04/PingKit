<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $this->get(route('http.errors'))
        ->assertRedirect(route('login'));
});

test('authenticated users can visit the http errors page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('http.errors'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('http/Errors')
            ->has('logs', 0)
        );
});

test('authenticated users are redirected from http to http errors', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get('/http')
        ->assertRedirect('/http/errors');
});
