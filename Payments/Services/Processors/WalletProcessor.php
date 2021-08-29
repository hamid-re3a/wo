<?php


namespace Payments\Services\Processors;


use Illuminate\Support\Facades\Log;
use Payments\Jobs\EmailJob;
use Payments\Mail\Payment\Wallet\EmailWalletInvoiceExpired;
use Payments\Mail\Payment\Wallet\EmailWalletInvoicePaidComplete;
use Payments\Mail\Payment\Wallet\EmailWalletInvoicePaidPartial;
use User\Models\User;
use Wallets\Services\Deposit;
use Wallets\Services\Transaction;
use Wallets\Services\WalletService;

class WalletProcessor extends ProcessorAbstract
{
    private $wallet_service;
    private $user_db;

    public function __construct(\Payments\Models\Invoice $invoice_db)
    {

        parent::__construct($invoice_db);

        $this->wallet_service = app(WalletService::class);

        $this->user_db = User::find($invoice_db->user_id);

    }

    public function partial()
    {

        //send web socket notification
        $this->socket_service->sendInvoiceMessage($this->invoice_model, 'partial_paid');

        //send user email
        EmailJob::dispatch(new EmailWalletInvoicePaidPartial($this->user_db, $this->invoice_model), $this->user_db->email);

    }

    public function processing()
    {

        // send web socket notification
        $this->socket_service->sendInvoiceMessage($this->invoice_model, 'paid');

    }

    public function completed()
    {

        $pf_paid = number_format(
            ($this->invoice_model->getPfAmount() / $this->invoice_model->getAmount()) * $this->invoice_model->getPaidAmount()
            , 2, '.', '');

        if ($pf_paid > (double)$this->invoice_db->deposit_amount) {
            $deposit_amount = $pf_paid - ((double)$this->invoice_db->deposit_amount);

            //Prepare user service
            $preparedUser = $this->user_db->getUserService();

            //Prepare Transaction
            $transactionService = app(Transaction::class);
            $transactionService->setConfiremd(true);
            $transactionService->setAmount($this->invoice_model->getPfAmount());
            $transactionService->setToWalletName('Deposit Wallet');
            $transactionService->setToUserId($this->user_db->id);
            $transactionService->setType('Deposit');
            $transactionService->setDescription(serialize([
                'description' => 'Payment #' . $this->invoice_model->getTransactionId()
            ]));

            //Deposit Service
            $depositService = app(Deposit::class);
            $depositService->setTransaction($transactionService);
            $depositService->setUser($preparedUser);

            //Deposit transaction
            $transaction = $this->wallet_service->deposit($depositService);
            if($transaction->getConfiremd()) {
                $this->invoice_db->update([
                    'is_paid' => true
                ]);

                // send web socket notification
                $this->socket_service->sendInvoiceMessage($this->invoice_model, 'confirmed');
            } else {
                Log::error('Deposit wallet user error , InvoiceID => ' . $this->invoice_db->id);
                //TODO email admin/dev-team email
            }


            // send user email
            EmailJob::dispatch(new EmailWalletInvoicePaidComplete($this->user_db, $this->invoice_model),$this->user_db->email);
        }

    }

    public function expired()
    {

        // send web socket notification
        $this->socket_service->sendInvoiceMessage($this->invoice_model, 'expired');

        // send user email
        EmailJob::dispatch(new EmailWalletInvoiceExpired($this->user_db, $this->invoice_model), $this->user_db->email);

    }

    public function invalid()
    {
        // TODO: Implement invalid() method.
    }
}
