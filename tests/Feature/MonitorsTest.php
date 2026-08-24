<?php

use App\Enums\HttpMethod;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $this->get(route('monitors.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users can visit the monitors page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('monitors.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('monitors/Index')
            ->has('httpMethods', count(HttpMethod::cases()))
            ->where('httpMethods.0.label', HttpMethod::Get->label())
            ->where('httpMethods.0.value', HttpMethod::Get->value));
});
