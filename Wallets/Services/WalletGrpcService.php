<?php


namespace Wallets\Services;

use Mix\Grpc\Context;
use Wallets\Services\Grpc\Deposit;
use Wallets\Services\Grpc\Transfer;
use Wallets\Services\Grpc\Wallet;
use Wallets\Services\Grpc\Withdraw;

class WalletGrpcService implements \Wallets\Services\Grpc\WalletServiceInterface
{
    /**
     * @var $wallet_service WalletService
     */
    private $wallet_service;

    public function __construct()
    {
        $this->wallet_service = app(WalletService::class);
    }


    public function deposit(Context $context, Deposit $deposit): Deposit
    {
        return $this->wallet_service->deposit($deposit);
    }

    public function withdraw(Context $context, Withdraw $withdraw): Withdraw
    {
        return $this->wallet_service->withdraw($withdraw);
    }

    public function transfer(Context $context, Transfer $transfer): Transfer
    {
        return $this->wallet_service->transfer($transfer);
    }


    public function getBalance(Context $context, Wallet $wallet): Wallet
    {
        return $this->wallet_service->getBalance($wallet);
    }

}
