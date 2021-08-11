<?php


namespace Packages\Services;

use Wallets\Models\WalletUser;

class UserService
{
    public function getUser($data)
    {
        if(is_object($data))
            $data = (array) $data;

        return WalletUser::updateOrCreate(
            [ 'user_id' => $data['user_id'] ],
            [
                'first_name' => array_key_exists('first_name', $data) ? $data['first_name'] : null,
                'last_name' => array_key_exists('last_name', $data) ? $data['last_name'] : null,
                'email' => array_key_exists('email', $data) ? $data['email'] : null,
                'username' => array_key_exists('username', $data) ? $data['username'] : null
            ]
        );
    }
}
