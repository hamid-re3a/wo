<?php


namespace Wallets\Services;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use User\Models\User;

class WalletService implements WalletServiceInterface
{
    private $depositWallet;
    private $earningWallet;
    private $wallets = [];

    public function __construct()
    {
        $this->depositWallet = config('depositWallet');
        $this->earningWallet = config('earningWallet');
        $this->wallets[] = $this->depositWallet;
        $this->wallets[] = $this->earningWallet;
    }

    private function walletUser($user)
    {

        return User::firstOrCreate(
            [ 'id' => $user->getId() ]
        );

    }

    private function trueResponse()
    {
        $response = new Transaction();
        $response->setConfiremd(true);
        return $response;
    }

    private function falseResponse()
    {
        $response = new Transaction();
        $response->setConfiremd(false);
        return $response;
    }

    public function deposit(Deposit $deposit): Transaction
    {
        try {
            DB::beginTransaction();
            $walletUser = $this->walletUser($deposit->getUser());
            $transaction = $deposit->getTransaction();
            if (
                $transaction->getAmount() > 0 AND
                $transaction->getToWalletName() AND
                $transaction->getToUserId() AND
                in_array(strtolower($transaction->getToWalletName()) ,$this->wallets)
            ) {
                $bankService = new BankService($walletUser);
                $bankService->deposit($this->depositWallet,$transaction->getAmount(), $transaction->getDescription() ?: null);

                DB::commit();

                return $this->trueResponse();

            } else {
                DB::rollBack();
                return $this->falseResponse();
            }
        } catch (\Throwable $exception) {
            return $this->falseResponse();
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
                $transaction->getFromWalletName() AND
                $transaction->getFromUserId() AND
                in_array($transaction->getFromWalletName() ,[$this->depositWallet,$this->earningWallet])
            ) {
                $bankService = new BankService($walletUser);

                $type = null;
                $description = $transactionDescription = unserialize($transaction->getDescription());

                if(is_array($transactionDescription)) {
                    if(array_key_exists('description', $transactionDescription))
                        $description['description'] = $transactionDescription['description'];
                    if(array_key_exists('type',$transactionDescription))
                        $description['type'] = $transactionDescription['type'];
                }

                $bankService->withdraw($transaction->getFromWalletName(),$transaction->getAmount(), $description ?: null);

                DB::commit();

                return $this->trueResponse();

            } else {
                DB::rollBack();
                return $this->falseResponse();
            }
        } catch (\Throwable $exception) {
            DB::rollBack();
            return $this->falseResponse();
        }
    }

    public function transfer(Transfer $transfer): Transaction
    {
        try {
            DB::beginTransaction();
            $transaction = $transfer->getTransaction();
            if (
                $transaction->getAmount() > 0 AND
                $transaction->getToWalletName() AND
                $transaction->getFromWalletName() AND
                $transaction->getFromUserId() AND
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

                return $this->trueResponse();

            } else {
                DB::rollBack();
                return $this->falseResponse();
            }
        } catch (\Throwable $exception) {

            DB::rollBack();
            return $this->falseResponse();
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
