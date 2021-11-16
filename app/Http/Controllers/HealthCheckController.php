<?php

namespace App\Http\Controllers;

class HealthCheckController extends Controller
{
    public function _healthz()
    {
        try {

            //Check DB connection
            \Illuminate\Support\Facades\DB::connection()->getPdo();
            \Illuminate\Support\Facades\DB::connection()->getDatabaseName();
//
//            //Prepare User
//            $user = new \User\Services\Grpc\User();
//            $user->setId(1);
//
//            //Check Gateway GRPC
//            updateUserFromGrpcServer(1);
//
//            //check mlm grpc
//            \MLM\Services\MlmClientFacade::getUserRank($user);
//
//            //Check Kyc grpc
//            \Kyc\Services\KycClientFacade::checkKYCStatus($user);

            return api()->success(null,[
                'subject' => 'What do you want to see here ?'
            ]);
        } catch (\Throwable $exception) {
            return api()->error(null,[
                'subject' => $exception->getMessage()
            ]);
        }
    }

}
