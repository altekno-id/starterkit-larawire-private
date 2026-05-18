<?php

test('guests are redirected to login', function () {
    $response = $this->get('/');

    $response->assertRedirect('http://auth.13-starterpack.test/login?redirect=http%3A%2F%2F13-starterpack.test');
});
