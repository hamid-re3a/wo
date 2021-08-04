<?php

namespace Wallets;

use Illuminate\Support\Facades\Artisan;

class WalletConfigure
{

    public static $runsMigrations = true;
    private static $namespace = 'Wallets';


    public static function ignoreMigrations()
    {
        static::$runsMigrations = false;

        return new static;
    }


    public static function seed()
    {
        $files = scandir(__DIR__ . DIRECTORY_SEPARATOR . "database" . DIRECTORY_SEPARATOR . "seeders");
        unset($files[array_search('.', $files)]);
        unset($files[array_search('..', $files)]);
        foreach ($files as $file) {
            Artisan::call('db:seed', ['--class' => self::$namespace . "\database\seeders\\" . str_replace('.php', '', $file)]);
        }
    }
}
