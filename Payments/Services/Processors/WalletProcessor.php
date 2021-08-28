<?php


namespace Payments\Services\Processors;


use Illuminate\Support\Facades\Log;
use User\Models\User;
use Wallets\Services\Deposit;
use Wallets\Services\Transaction;
use Wallets\Services\WalletService;

class WalletProcessor extends ProcessorAbstract
{
    private $wallet_service;

    public function __construct(\Payments\Models\Invoice $invoice_db)
    {

        parent::__construct($invoice_db);

        $this->wallet_service = app(WalletService::class);

    }

    public function partial()
    {

        //send web socket notification
        $this->socket_service->sendInvoiceMessage($this->invoice_model, 'partial_paid');

        //TODO send email notification for due amount

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
            $user_db = User::find($this->invoice_db->user_id);
            $preparedUser = $user_db->getUserService();

            //Prepare Transaction
            $transactionService = app(Transaction::class);
            $transactionService->setConfiremd(true);
            $transactionService->setAmount($deposit_amount);
            $transactionService->setToWalletName('Deposit Wallet');
            $transactionService->setToUserId($user_db->id);
            $transactionService->setDescription(serialize([
                'description' => 'Payment #' . $this->invoice_model->getTransactionId(),
                'type' => 'Deposit'
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


            //TODO send deposited amount email
        }

    }

    public function expired()
    {

        // send web socket notification
        $this->socket_service->sendInvoiceMessage($this->invoice_model, 'expired');

        //TODO send expired email

    }

    public function invalid()
    {
        // TODO: Implement invalid() method.
    }
}
