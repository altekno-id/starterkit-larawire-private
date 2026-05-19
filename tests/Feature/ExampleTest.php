<?php

test('guests are redirected to login', function () {
    $response = $this->get('/');

    $response->assertRedirect('http://auth.13-starterpack.test/login?redirect=http%3A%2F%2F13-starterpack.test');
});

test('expired livewire navigate requests receive same origin redirect page', function () {
    $response = $this
        ->withHeader('X-Livewire-Navigate', '1')
        ->get('/dashboard/index');

    $response->assertOk();
    $response->assertHeaderMissing('location');
    $response->assertSee('window.location.replace', false);
    $response->assertSee('http://auth.13-starterpack.test/login?redirect=http%3A%2F%2F13-starterpack.test%2Fdashboard%2Findex', false);
});

test('expired livewire component requests receive redirect payload', function () {
    $response = $this
        ->withHeader('X-Livewire', '1')
        ->get('/dashboard/index');

    $response->assertUnauthorized();
    $response->assertJsonPath('redirect', 'http://auth.13-starterpack.test/login?redirect=http%3A%2F%2F13-starterpack.test%2Fdashboard%2Findex');
});
