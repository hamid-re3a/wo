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
    private $wallets = [];

    public function __construct()
    {
        $this->depositWallet = config('depositWallet');
        $this->earningWallet = config('earningWallet');
        $this->wallets[] = $this->depositWallet;
        $this->wallets[] = $this->earningWallet;
    }

    private function walletUser($user_id)
    {

        return User::firstOrCreate(
            ['id' => $user_id]
        );

    }

    public function deposit(Deposit $deposit): Deposit
    {
        try {
            DB::beginTransaction();

            if (
                $deposit->getConfirmed() > 0 AND
                $deposit->getAmount() > 0 AND
                $deposit->getWalletName() AND
                $deposit->getUserId() AND
                in_array($deposit->getWalletName(), [$this->earningWallet, $this->depositWallet])
            ) {
                $walletUser = $this->walletUser($deposit->getUserId());
                $bankService = new BankService($walletUser);

                $bankService->deposit($deposit->getWalletName(), $deposit->getAmount(), $deposit->getDescription() ?: null, $deposit->getConfirmed(), $deposit->getType(), !empty($deposit->getSubType()) ? $deposit->getSubType() : null);

                DB::commit();
                return $deposit;

            } else {
                throw new \Exception();
            }
        } catch (\Throwable $exception) {
            DB::rollBack();
            $deposit->setConfirmed(false);
            return $deposit;
        }
    }

    public function withdraw(Withdraw $withdraw): Withdraw
    {
        try {
            DB::beginTransaction();

            if (
                $withdraw->getConfirmed() AND
                $withdraw->getAmount() > 0 AND
                $withdraw->getWalletName() AND
                $withdraw->getUserId() AND
                $withdraw->getType() AND
                in_array($withdraw->getWalletName(), [$this->depositWallet, $this->earningWallet])
            ) {
                $walletUser = $this->walletUser($withdraw->getUserId());
                $bankService = new BankService($walletUser);

                $bankService->withdraw($withdraw->getWalletName(), $withdraw->getAmount(), $withdraw->getDescription() ?: null, $withdraw->getType(),empty($withdraw->getSubType()) ? null : $withdraw->getSubType());
                DB::commit();
                return $withdraw;

            } else {
                throw new \Exception();
            }
        } catch (\Throwable $exception) {
            DB::rollBack();
            $withdraw->setConfirmed(false);
            return $withdraw;
        }
    }

    public function transfer(Transfer $transfer): Transfer
    {
        try {
            DB::beginTransaction();
            if (
                $transfer->getFromUserId() AND
                $transfer->getFromWalletName() AND
                $transfer->getToUserId() AND
                $transfer->getToWalletName() AND
                $transfer->getAmount() > 0 AND
                $transfer->getConfirmed()
            ) {
                $fromUser = $this->walletUser($transfer->getFromUserId());
                $toUser = $this->walletUser($transfer->getToUserId());
                $fromBankService = new BankService($fromUser);
                $toBankService = new BankService($toUser);

                $fromWallet = Str::contains(Str::lower($transfer->getFromWalletName()), 'deposit') ? $this->depositWallet : $this->earningWallet;
                $toWallet = Str::contains(Str::lower($transfer->getToWalletName()), 'deposit') ? $this->depositWallet : $this->earningWallet;
                $fromBankService->transfer($fromBankService->getWallet($fromWallet), $toBankService->getWallet($toWallet), $transfer->getAmount(), $transfer->getDescription() ?: null);

                DB::commit();
                return $transfer;

            } else {
                throw new \Exception();
            }
        } catch (\Throwable $exception) {
            DB::rollBack();
            $transfer->setConfirmed(false);
            return $transfer;
        }
    }


    public function getBalance(Wallet $wallet): Wallet
    {
        try {
            $walletUser = $this->walletUser($wallet->getUserId());
            $bankService = new BankService($walletUser);
            $balance = $bankService->getBalance(Str::contains(Str::lower($wallet->getName()), 'deposit') ? $this->depositWallet : $this->earningWallet);

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
        $invoice_request->setUser($request->user->getUserService());
        $payment_service = app(PaymentService::class);
        return $payment_service->pay($invoice_request);
    }

}
