<?php


namespace Packages\Services;


use Illuminate\Database\Capsule\Manager as Capsule;

class GrpcMainService
{
    public function __construct()
    {
        $capsule = new Capsule;


        $capsule->addConnection([
            'driver' => env('DB_CONNECTION'),
            'host' => env('DB_HOST'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
        ]);
        $capsule->setAsGlobal();

    // Setup the Eloquent ORM.
        $capsule->bootEloquent();
    }
}
