<?php
use App\Core\Language;
use App\Core\Session;

if (!function_exists('__')) {
    function __($key, $default = null) {
        return Language::get($key, $default);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token() {
        return Session::csrfToken();
    }
}


