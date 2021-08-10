<?php


namespace Wallets\Services;


use Bavix\Wallet\Interfaces\Wallet;

class Bank
{

    public static function wallet(Wallet $owner, $wallet_name): Wallet
    {
        $slug = "$wallet_name-slug";
        if (!$owner->hasWallet($slug))
            $owner->createWallet([
                'name' => $wallet_name,
                'slug' => $slug
            ]);

        return $owner->getWallet($slug);
    }

    public static function deposit(Wallet $wallet, $amount, $meta = null)
    {
        if (is_string($meta))
            $meta = ['description' => $meta];
        return $wallet->deposit($amount, $meta);
    }

    public static function withdraw(Wallet $wallet, $amount, $meta = null)
    {
        if (is_string($meta))
            $meta = ['description' => $meta];
        return $wallet->withdraw($amount, $meta);
    }

    public static function forceWithdraw(Wallet $wallet, $amount, $meta = null)
    {
        if (is_string($meta))
            $meta = ['description' => $meta];
        return $wallet->forceWithdraw($amount, $meta);
    }

    public static function transfer(Wallet $from_wallet, Wallet $to_wallet, $amount, $meta = null)
    {

        if (is_string($meta))
            $meta = ['description' => $meta];
        return $from_wallet->transfer($to_wallet, $amount, $meta);
    }

    public static function getBalance(Wallet $wallet)
    {
        $wallet->refreshBalance();
        return $wallet->balance;
    }



}
