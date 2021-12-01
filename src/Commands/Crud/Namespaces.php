<?php

namespace Sebastienheyd\BoilerplatePackager\Commands\Crud;

use Illuminate\Support\Facades\Cache;

class Namespaces
{
    private static $namespaces = [];

    public static function register($name, $namespace)
    {
        self::$namespaces = Cache::get('namespaces', self::$namespaces);

        if(! isset(self::$namespaces[$name])) {
            self::$namespaces[$name] = $namespace;
            Cache::put('namespaces', self::$namespaces, 3600);
        }
    }

    public static function get($name = null)
    {
        $namespaces = Cache::get('namespaces', self::$namespaces);
        return isset($name) ? ($namespaces[$name] ?? false) : $namespaces;
    }
}