<?php

use Illuminate\Support\Str;
use Illuminate\Container\Container;

if (!function_exists('helpEx')) {
    function helpEx()
    {
        echo 'helper';
    }
}


if (! function_exists('hashPassword')) {
    function hashPassword($password)
    {
        return base64_encode(sha1('wealthbetter' . sha1($password)));
    }
}
