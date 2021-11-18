<?php

/**
 * user_roles
 */

use Illuminate\Http\Request;
use User\Services\UserService;
const USER_ROLE_SUPER_ADMIN = 'super-admin';
const USER_ROLE_ADMIN_GATEWAY = 'user-gateway-admin';
const USER_ROLE_ADMIN_KYC = 'kyc-admin';
const USER_ROLE_ADMIN_SUBSCRIPTIONS_ORDER = 'subscriptions-order-admin';
const USER_ROLE_ADMIN_SUBSCRIPTIONS_PACKAGE = 'subscriptions-package-admin';
const USER_ROLE_ADMIN_SUBSCRIPTIONS_PAYMENT = 'subscriptions-payment-admin';
const USER_ROLE_ADMIN_SUBSCRIPTIONS_WALLET = 'subscriptions-wallet-admin';
const USER_ROLE_ADMIN_SUBSCRIPTIONS_GIFTCODE = 'subscriptions-giftcode-admin';
const USER_ROLE_ADMIN_MLM = 'mlm-admin';
const USER_ROLE_CLIENT = 'client';
const USER_ROLE_HELP_DESK = 'help-desk';
const USER_ROLES = [
    USER_ROLE_SUPER_ADMIN,
    USER_ROLE_ADMIN_GATEWAY,
    USER_ROLE_ADMIN_KYC,
    USER_ROLE_ADMIN_SUBSCRIPTIONS_ORDER,
    USER_ROLE_ADMIN_SUBSCRIPTIONS_PACKAGE,
    USER_ROLE_ADMIN_SUBSCRIPTIONS_PAYMENT,
    USER_ROLE_ADMIN_SUBSCRIPTIONS_WALLET,
    USER_ROLE_ADMIN_SUBSCRIPTIONS_GIFTCODE,
    USER_ROLE_ADMIN_MLM,
    USER_ROLE_CLIENT,
    USER_ROLE_HELP_DESK,
];

if (!function_exists('getGatewayGrpcClient')) {
    function getGatewayGrpcClient()
    {
        return new \User\Services\Grpc\UserServiceClient(env('API_GATEWAY_GRPC_URL', 'development.dreamcometrue.ai:9595'), [
            'credentials' => \Grpc\ChannelCredentials::createInsecure()
        ]);
    }
}

if (!function_exists('getMLMGrpcClient')) {
    function getMLMGrpcClient()
    {
        return new \MLM\Services\Grpc\MLMServiceClient(env('MLM_GRPC_URL', 'staging-api-gateway.janex.org:9598'), [
            'credentials' => \Grpc\ChannelCredentials::createInsecure()
        ]);
    }
}

if (!function_exists('getKycGrpcClient')) {
    function getKycGrpcClient()
    {
        return new \Kyc\Services\Grpc\KycServiceClient(env('KYC_GRPC_URL', 'staging.janex.org:9597'), [
            'credentials' => \Grpc\ChannelCredentials::createInsecure()
        ]);
    }
}

if (!function_exists('updateUserFromGrpcServer')) {

    function updateUserFromGrpcServer($input_id): ?\User\Services\Grpc\User
    {
        if(!is_numeric($input_id))
            return null;
        $id = new \User\Services\Grpc\Id();
        $id->setId((int)$input_id);
        try {
            $grpc_user = \User\Services\GatewayClientFacade::getUserById($id);
            if(!$grpc_user->getId())
                return null;
            app(UserService::class)->userUpdate($grpc_user);
            return $grpc_user;
        } catch (\Exception $exception) {
            return null;
        }
    }
}


if (!function_exists('updateUserFromGrpcServerByMemberId')) {

    function updateUserFromGrpcServerByMemberId($input_id): ?\User\Services\Grpc\User
    {
        if(!is_numeric($input_id))
            return null;
        $id = new \User\Services\Grpc\Id();
        $id->setId((int)$input_id);
        try {
            $grpc_user = \User\Services\GatewayClientFacade::getUserById($id);
            if(!$grpc_user->getId())
                return null;
            app(UserService::class)->userUpdate($grpc_user);
            return $grpc_user;
        } catch (\Exception $exception) {
            return null;
        }
    }
}
