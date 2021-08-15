<?php


namespace Wallets\Services;


use User\Services;

class WalletService implements  WalletServiceInterface
{

    public function deposit(Deposit $deposit): Transaction
    {
        // TODO: Implement deposit() method.
    }

    public function withdraw(Withdraw $withdraw): Transaction
    {
        // TODO: Implement withdraw() method.
    }

    public function transfer(Transfer $transfer): Transaction
    {
        // TODO: Implement transfer() method.
    }

    public function getBalance(Wallet $request): Wallet
    {
        // TODO: Implement getBalance() method.
    }
}
