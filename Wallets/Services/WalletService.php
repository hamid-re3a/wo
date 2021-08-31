<?php


namespace Wallets\Services;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Payments\Services\Invoice;
use Payments\Services\PaymentService;
use User\Models\User;

class WalletService implements WalletServiceInterface
{
    private $depositWallet;
    private $earningWallet;
    private $payment_service;
    private $wallets = [];

    public function __construct(PaymentService $payment_service)
    {
        $this->depositWallet = config('depositWallet');
        $this->earningWallet = config('earningWallet');
        $this->wallets[] = $this->depositWallet;
        $this->wallets[] = $this->earningWallet;
        $this->payment_service = $payment_service;
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
                $transaction->getConfiremd() > 0 AND
                $transaction->getAmount() > 0 AND
                $transaction->getToWalletName() AND
                $transaction->getToUserId() AND
                in_array($transaction->getToWalletName() ,[$this->earningWallet,$this->depositWallet])
            ) {
                $bankService = new BankService($walletUser);

                $bankService->deposit($transaction->getToWalletName(),$transaction->getAmount(), $transaction->getDescription() ?: null, $transaction->getType());

                DB::commit();
                return $this->trueResponse();

            } else {
                throw new \Exception();
            }
        } catch (\Throwable $exception) {
            DB::rollBack();
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
                $transaction->getType() AND
                in_array($transaction->getFromWalletName() ,[$this->depositWallet,$this->earningWallet])
            ) {
                $bankService = new BankService($walletUser);

                $bankService->withdraw($transaction->getFromWalletName(),$transaction->getAmount(), $transaction->getDescription() ?: null,$transaction->getType());

                DB::commit();

                return $this->trueResponse();

            } else {
                throw new \Exception();
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
                $fromBankService->transfer($fromBankService->getWallet($fromWallet),$toBankService->getWallet($toWallet), $transaction->getAmount(), $transaction->getDescription() ?: null);

                DB::commit();

                return $this->trueResponse();

            } else {
                throw new \Exception();
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

    /**
     * @param $request
     * @return Invoice
     */
    public function invoiceWallet(Request $request)
    {
        $invoice_request = new Invoice();
        $invoice_request->setPfAmount($request->get('amount'));
        $invoice_request->setPaymentDriver('btc-pay-server');
        $invoice_request->setPaymentType('purchase');
        $invoice_request->setPaymentCurrency('BTC');
        $invoice_request->setPayableType('DepositWallet');
        $invoice_request->setUser($request->wallet_user->getUserService());
        return $this->payment_service->pay( $invoice_request);
    }

}
