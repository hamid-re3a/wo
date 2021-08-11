<?php


namespace Wallets\Services;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Packages\Services\UserService;
use User\Services;

class WalletService implements WalletServiceInterface
{
    private $depositWallet;
    private $earningWallet;

    public function __construct()
    {
        $this->depositWallet = config('depositWallet');
        $this->earningWallet = config('earningWallet');
    }

    private function walletUser($user)
    {
        return UserService::getUser([
            'user_id' => $user->getId(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername()
        ]);
    }

    public function deposit(Deposit $deposit): Transaction
    {
        try {
            DB::beginTransaction();
            $walletUser = $this->walletUser($deposit->getUser());

            $transaction = $deposit->getTransaction();
            if (
                $transaction->getConfiremd() AND
                $transaction->getAmount() > 0 AND
                $transaction->getToWalletName() AND
                $transaction->getToUserId() AND
                $transaction->getConfiremd() AND
                in_array(strtolower($transaction->getToWalletName()) ,['deposit','deposit wallet'])
            ) {
                $bankService = new BankService($walletUser);

                $bankService->deposit($this->depositWallet,$transaction->getAmount(), $transaction->getDescription() ?: null);

                $transactionObject = new Transaction();
                $transactionObject->setConfiremd(true);

                DB::commit();
                return $transactionObject;
            } else {
                DB::rollBack();
                return new Transaction();
            }
        } catch (\Throwable $exception) {
            return new Transaction();
        }
    }

    public function withdraw(Withdraw $withdraw): Transaction
    {
        try {
            DB::beginTransaction();
            $walletUser = $this->walletUser($withdraw->getUser());

            $transaction = $withdraw->getTransaction();
            if (
                $transaction->getConfiremd() AND
                $transaction->getAmount() > 0 AND
                $transaction->getToWalletName() AND
                $transaction->getToUserId() AND
                $transaction->getConfiremd() AND
                in_array(strtolower($transaction->getToWalletName()) ,['earning','earning wallet'])
            ) {
                $bankService = new BankService($walletUser);

                $bankService->withdraw($this->earningWallet,$transaction->getAmount(), $transaction->getDescription() ?: null);

                $transactionObject = new Transaction();
                $transactionObject->setConfiremd(true);

                DB::commit();
                return $transactionObject;
            } else {
                DB::rollBack();
                return new Transaction();
            }
        } catch (\Throwable $exception) {
            DB::rollBack();
            return new Transaction();
        }
    }

    public function transfer(Transfer $transfer): Transaction
    {
        try {
            DB::beginTransaction();
            $transaction = $transfer->getTransaction();
            if (
                $transaction->getConfiremd() AND
                $transaction->getAmount() > 0 AND
                $transaction->getToWalletName() AND
                $transaction->getToUserId() AND
                $transaction->getConfiremd()
            ) {
                $fromUser = $this->walletUser($transfer->getFromUser());
                $toUser = $this->walletUser($transfer->getToUser());
                $fromBankService = new BankService($fromUser);
                $toBankService = new BankService($toUser);

                $fromWallet = Str::contains(Str::lower($transaction->getFromWalletName()), 'deposit') ? $this->depositWallet : $this->earningWallet;
                $toWallet = Str::contains(Str::lower($transaction->getToWalletName()), 'deposit') ? $this->depositWallet : $this->earningWallet;

                $fromBankService->transfer($fromWallet,$toBankService->getWallet($toWallet), $transaction->getAmount(), $transaction->getDescription() ?: null);
                DB::commit();
            } else {
                DB::rollBack();
                return new Transaction();
            }
        } catch (\Throwable $exception) {

            DB::rollBack();
            return new Transaction();
        }
    }

    public function getBalance(Wallet $wallet): Wallet
    {
        try {
            $walletUser = $this->walletUser($wallet->getUser());
            $bankService = new BankService($walletUser);
            $balance = $bankService->getBalance(Str::contains(Str::lower($wallet->getName()),'deposit') ? $this->depositWallet : $this->earningWallet);

            $response = new Wallet();
            $response->setBalance($balance);
            return $response;

        } catch (\Throwable $exception) {
            return new Wallet();
        }
    }
}
