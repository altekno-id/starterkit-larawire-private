<?php

use Altekno\StarterKit\Support\Starter\StarterNavigation;

test('landing page is public', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Starterpack');
});

test('expired livewire navigate requests receive same origin redirect page', function () {
    $dashboardUrl = route('app1.dashboard');
    $loginUrl = StarterNavigation::authLoginUrl($dashboardUrl);
    $response = $this
        ->withHeader('X-Livewire-Navigate', '1')
        ->get($dashboardUrl);

    $response->assertOk();
    $response->assertHeaderMissing('location');
    $response->assertSee('window.location.replace', false);
    $response->assertSee($loginUrl, false);
});

test('expired livewire component requests receive redirect payload', function () {
    $dashboardUrl = route('app1.dashboard');
    $response = $this
        ->withHeader('X-Livewire', '1')
        ->get($dashboardUrl);

    $response->assertUnauthorized();
    $response->assertJsonPath('redirect', StarterNavigation::authLoginUrl($dashboardUrl));
});
