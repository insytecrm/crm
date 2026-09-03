<?php

test('the platform login page can be rendered', function () {
    $this->get('/platform/login')->assertOk();
});
