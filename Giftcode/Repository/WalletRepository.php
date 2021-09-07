<?php


namespace Giftcode\Repository;


use Giftcode\Models\Giftcode;
use Illuminate\Http\Request;
use User\Models\User;
use Wallets\Services\Deposit;
use Wallets\Services\Transaction;
use Wallets\Services\Wallet;
use Wallets\Services\WalletService;
use Wallets\Services\Withdraw;

class WalletRepository
{
    private $wallet_service;

    public function __construct(WalletService $walletService)
    {
        $this->wallet_service = $walletService;
    }

    public function withdrawUserWallet(Giftcode $giftcode)
    {
        $withdraw_object = app(Withdraw::class);
        $withdraw_object->setConfirmed(true);
        $withdraw_object->setAmount($giftcode->total_cost_in_usd);
        $withdraw_object->setWalletName('Deposit Wallet');
        $withdraw_object->setUserId($giftcode->user_id);
        $withdraw_object->setType('Create Giftcode');
        $withdraw_object->setDescription('Giftcode #' . $giftcode->uuid);
        return $this->wallet_service->withdraw($withdraw_object);
    }

    public function depositUserWallet(Giftcode $giftcode, $description = 'Giftcode refund')
    {
        $deposit_object = app(Deposit::class);
        $deposit_object->setConfirmed(true);
        $deposit_object->setUserId($giftcode->user_id);
        $deposit_object->setAmount($giftcode->getRefundAmount());
        $deposit_object->setType('Giftcode refund');
        $deposit_object->setDescription($description);
        $deposit_object->setWalletName('Deposit Wallet');

        //Deposit fee to admin wallet
        if($giftcode->total_cost_in_usd != $giftcode->getRefundAmount())
            $this->depositToAdminWallet($giftcode,$description);

        return $this->wallet_service->deposit($deposit_object);
    }

    public function checkUserBalance()
    {
        $wallet = app(Wallet::class);
        $wallet->setUserId(request()->user->id);
        $wallet->setName('Deposit Wallet');
        return $this->wallet_service->getBalance($wallet)->getBalance();
    }

    private function depositToAdminWallet(Giftcode $giftcode, $description = 'Giftcode')
    {
        $deposit_object = app(Deposit::class);
        $deposit_object->setConfirmed(true);
        $deposit_object->setUserId(1);
        $deposit_object->setAmount(($giftcode->total_cost_in_usd - $giftcode->getRefundAmount()));
        $deposit_object->setType('Giftcode refund');
        $deposit_object->setDescription($description);
        $deposit_object->setWalletName('Deposit Wallet');
        //Deposit transaction
        $this->wallet_service->deposit($deposit_object);
    }


}
