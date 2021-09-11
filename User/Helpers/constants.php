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



if (!function_exists('updateUserFromGrpcServer')) {
    /**
     * @param Request $request
     * @return array
     */
    function updateUserFromGrpcServer(Request $request): ?\User\Services\User
    {
        $client = new \User\Services\UserServiceClient('staging-api-gateway.janex.org:9595', [
            'credentials' => \Grpc\ChannelCredentials::createInsecure()
        ]);
        $id = new \User\Services\Id();
        $id->setId((int)$request->header('X-user-id'));
        try {
            /** @var $user \User\Services\User */
            list($user, $status) = $client->getUserById($id)->wait();
            if ($status->code == 0) {
                app(UserService::class)->userUpdate($user);
                return $user;
            }
            return null;
        } catch (\Exception $exception) {
            return null;
        }
    }
}
