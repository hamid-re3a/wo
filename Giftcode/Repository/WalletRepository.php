<?php


namespace Giftcode\Repository;


use Giftcode\Models\Giftcode;
use Wallets\Services\Grpc\Deposit;
use Wallets\Services\Grpc\WalletNames;
use Wallets\Services\Grpc\Wallet;
use Wallets\Services\WalletService;
use Wallets\Services\Grpc\Withdraw;

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
        $withdraw_object->setAmount($giftcode->total_cost_in_pf);
        $withdraw_object->setWalletName(WalletNames::DEPOSIT);
        $withdraw_object->setUserId($giftcode->user_id);
        $withdraw_object->setType('Gift code created');
        $withdraw_object->setDescription('Giftcode #' . $giftcode->uuid);
        return $this->wallet_service->withdraw($withdraw_object);
    }

    public function depositUserWallet(Giftcode $giftcode, $description = 'Gift code cancelled', $type = 'Gift code cancelled')
    {
        $deposit_object = app(Deposit::class);
        $deposit_object->setUserId($giftcode->user_id);
        $deposit_object->setAmount($giftcode->getRefundAmount());
        $deposit_object->setType($type);
        $deposit_object->setDescription($description);
        $deposit_object->setWalletName(WalletNames::DEPOSIT);

        return $this->wallet_service->deposit($deposit_object);
    }

    public function checkUserBalance()
    {
        $wallet = app(Wallet::class);
        $wallet->setUserId(auth()->check() ? auth()->user()->id : 2);
        $wallet->setName(WalletNames::DEPOSIT);
        return $this->wallet_service->getBalance($wallet)->getBalance();
    }

    public function withdrawFromAdminWallet(Giftcode $giftcode, $description = 'Giftcode', $type = 'Gift code Refund fee')
    {
        $deposit_object = app(Withdraw::class);
        $deposit_object->setUserId(1);
        $deposit_object->setAmount($giftcode->getRefundAmount());
        $deposit_object->setType($type);
        $deposit_object->setDescription($description);
        $deposit_object->setWalletName(WalletNames::DEPOSIT);
        //Deposit transaction
        $this->wallet_service->withdraw($deposit_object);
    }

    private function depositToAdminWallet(Giftcode $giftcode, $description = 'Giftcode', $type = 'Gift code Refund fee')
    {
        $deposit_object = app(Deposit::class);
        $deposit_object->setUserId(1);
        $deposit_object->setAmount(($giftcode->total_cost_in_pf - $giftcode->getRefundAmount()));
        $deposit_object->setType($type);
        $deposit_object->setDescription($description);
        $deposit_object->setWalletName(WalletNames::DEPOSIT);
        //Deposit transaction
        $this->wallet_service->deposit($deposit_object);
    }




}
