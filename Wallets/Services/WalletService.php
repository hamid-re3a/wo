<?php


namespace Wallets\Services;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Payments\Services\Grpc\Invoice;
use Payments\Services\Processors\PaymentFacade;
use User\Models\User;
use Wallets\Jobs\HandleWithdrawUnsentEmails;
use Wallets\Models\WithdrawProfit;
use Wallets\Services\Grpc\Deposit;
use Wallets\Services\Grpc\Transfer;
use Wallets\Services\Grpc\Wallet;
use Wallets\Services\Grpc\WalletNames;
use Wallets\Services\Grpc\Withdraw;

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

        return User::query()->firstOrCreate(
            ['id' => $user_id]
        );

    }

    public function deposit(Deposit $deposit): Deposit
    {
        try {
            DB::beginTransaction();

            if (
                $deposit->getAmount() > 0 AND
                $deposit->getUserId() AND
                !is_null($deposit->getType()) AND
                in_array($deposit->getWalletName(), [WalletNames::EARNING,WalletNames::DEPOSIT])
            ) {
                $walletUser = $this->walletUser($deposit->getUserId());
                $bankService = new BankService($walletUser);

                $wallet_name = $this->earningWallet;
                if($deposit->getWalletName() == WalletNames::DEPOSIT)
                    $wallet_name = $this->depositWallet;

                $transaction = $bankService->deposit($wallet_name, $deposit->getAmount(), $deposit->getDescription() ?: null, true, $deposit->getType(), !empty($deposit->getSubType()) ? $deposit->getSubType() : null);
                $deposit->setTransactionId((int)$transaction->uuid);

                DB::commit();
                return $deposit;

            } else {
                DB::rollBack();
                Log::error('Deposit error 1 => ' . $deposit->getUserId() . ' | type => ' . $deposit->getType() . ' | subType => ' . $deposit->getSubType() . ' | walletName => ' .$deposit->getWalletName() );
                throw new \Exception();
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Deposit error 2 => ' . $deposit->getUserId() . ' | type => ' . $deposit->getType() . ' | subType => ' . $deposit->getSubType() . ' | walletName => ' .$deposit->getWalletName() );
            Log::error('Message => ' . $exception->getMessage());
            return $deposit;
        }
    }

    public function withdraw(Withdraw $withdraw): Withdraw
    {
        try {
            DB::beginTransaction();

            if (
                $withdraw->getAmount() > 0 AND
                $withdraw->getUserId() AND
                !is_null($withdraw->getType()) AND
                in_array($withdraw->getWalletName(), [WalletNames::EARNING,WalletNames::DEPOSIT])
            ) {
                $walletUser = $this->walletUser($withdraw->getUserId());
                $bankService = new BankService($walletUser);

                $wallet_name = $this->earningWallet;
                if($withdraw->getWalletName() == WalletNames::DEPOSIT)
                    $wallet_name = $this->depositWallet;

                $transaction = $bankService->withdraw($wallet_name, $withdraw->getAmount(), $withdraw->getDescription() ?: null, $withdraw->getType(),empty($withdraw->getSubType()) ? null : $withdraw->getSubType());
                $withdraw->setTransactionId($transaction->uuid);

                DB::commit();
                return $withdraw;

            } else {
                DB::rollBack();
                Log::error('withdraw error 1 => ' . $withdraw->getUserId() . ' | type => ' . $withdraw->getType() . ' | subType => ' . $withdraw->getSubType() . ' | walletName => ' .$withdraw->getWalletName() );
                throw new \Exception();
            }
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('withdraw error 2 => ' . $withdraw->getUserId() . ' | type => ' . $withdraw->getType() . ' | subType => ' . $withdraw->getSubType() . ' | walletName => ' .$withdraw->getWalletName() );
            Log::error($exception->getMessage());
            return $withdraw;
        }
    }

    public function transfer(Transfer $transfer): Transfer
    {
        try {
            DB::beginTransaction();
            if (
                $transfer->getFromUserId() AND
                $transfer->getToUserId() AND
                $transfer->getAmount() > 0 AND
                in_array($transfer->getToWalletName(),[WalletNames::EARNING,WalletNames::DEPOSIT]) AND
                in_array($transfer->getFromWalletName(),[WalletNames::EARNING,WalletNames::DEPOSIT])
            ) {
                $fromUser = $this->walletUser($transfer->getFromUserId());
                $toUser = $this->walletUser($transfer->getToUserId());
                $fromBankService = new BankService($fromUser);
                $toBankService = new BankService($toUser);

                $fromWallet = $toWallet = $this->earningWallet;
                if($transfer->getToWalletName() == WalletNames::DEPOSIT)
                    $toWallet = $this->depositWallet;

                if($transfer->getFromWalletName() == WalletNames::DEPOSIT)
                    $fromWallet = $this->depositWallet;

                $bank_transfer = $fromBankService->transfer($fromBankService->getWallet($fromWallet), $toBankService->getWallet($toWallet), $transfer->getAmount(), $transfer->getDescription() ?: null);
                $transfer->setDepositTransactionId($bank_transfer->withdraw->uuid);
                $transfer->setWithdrawTransactionId($bank_transfer->deposit->uuid);


                DB::commit();
                return $transfer;

            } else {
                DB::rollBack();
                Log::error('transfer error 1 :  from user id =>  ' . $transfer->getFromUserId() . ' | to user id => ' . $transfer->getToUserId() . ' | from wallet name => ' . $transfer->getFromWalletName() . ' | to wallet name => ' . $transfer->getToWalletName() );
                throw new \Exception();
            }
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('transfer error 2 :  from user id =>  ' . $transfer->getFromUserId() . ' | to user id => ' . $transfer->getToUserId() . ' | from wallet name => ' . $transfer->getFromWalletName() . ' | to wallet name => ' . $transfer->getToWalletName() );
            return $transfer;
        }
    }

    public function getBalance(Wallet $wallet): Wallet
    {
        try {
            $walletUser = $this->walletUser($wallet->getUserId());
            $bankService = new BankService($walletUser);

            $walletName = $this->earningWallet;
            if($wallet->getName() == WalletNames::DEPOSIT)
                $walletName = $this->depositWallet;

            $balance = $bankService->getBalance($walletName);

            $response = new Wallet();
            $response->setBalance($balance);
            return $response;

        } catch (\Throwable $exception) {
            return new Wallet();
        }
    }

    public function updateWithdrawRequestsByIds($ids,$updates)
    {
        WithdrawProfit::query()->whereIn('id',$ids)->update($updates);
        HandleWithdrawUnsentEmails::dispatch();
    }

    public function invoiceWallet(Request $request)
    {
        $invoice_request = new Invoice();
        $invoice_request->setPfAmount(usdToPf($request->get('amount')));
        $invoice_request->setPaymentDriver('btc-pay-server');
        $invoice_request->setPaymentType('purchase');
        $invoice_request->setPaymentCurrency('BTC');
        $invoice_request->setPayableType('DepositWallet');
        $invoice_request->setUser(auth()->user()->getUserService());

        return PaymentFacade::pay($invoice_request);
    }

}
