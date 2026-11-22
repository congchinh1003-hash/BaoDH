<?php
namespace App\Core;

class Language
{
    private static $translations = [];
    private static $currentLocale = 'vi';

    public static function load()
    {
        self::$currentLocale = Session::get('locale', 'vi');
        $file = __DIR__ . '/../Lang/' . self::$currentLocale . '.php';
        
        if (file_exists($file)) {
            self::$translations = require $file;
        } else {
            self::$translations = [];
        }
    }

    public static function get($key, $default = null)
    {
        $keys = explode('.', $key);
        $val = self::$translations;
        
        foreach ($keys as $k) {
            if (isset($val[$k])) {
                $val = $val[$k];
            } else {
                return $default ?? $key;
            }
        }
        
        return $val;
    }
    
    public static function current()
    {
        return self::$currentLocale;
    }
}

