<?php


namespace Packages\Services;


use Illuminate\Database\Capsule\Manager as Capsule;

class GrpcMainService
{
    public function __construct()
    {
        $capsule = new Capsule;

        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => 'mysql',
            'database' => 'wo_r2f_back',
            'username' => 'root',
            'password' => '123456',
        ]);
        $capsule->setAsGlobal();

    // Setup the Eloquent ORM.
        $capsule->bootEloquent();
        $capsule->bootEloquent();
    }
}
