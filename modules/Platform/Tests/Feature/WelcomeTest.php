<?php

test('the welcome page can be rendered', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
});
