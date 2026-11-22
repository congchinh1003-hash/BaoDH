<?php
use App\Core\Language;

if (!function_exists('__')) {
    function __($key, $default = null) {
        return Language::get($key, $default);
    }
}

