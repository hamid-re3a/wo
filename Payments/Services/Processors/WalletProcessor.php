<?php


namespace Payments\Services\Processors;


use Illuminate\Support\Facades\Log;
use Payments\Jobs\EmailJob;
use Payments\Mail\Payment\Wallet\EmailWalletInvoiceExpired;
use Payments\Mail\Payment\Wallet\EmailWalletInvoicePaidComplete;
use Payments\Mail\Payment\Wallet\EmailWalletInvoicePaidPartial;
use Payments\Models\Invoice;
use User\Models\User;
use Wallets\Services\Deposit;
use Wallets\Services\WalletService;

class WalletProcessor extends ProcessorAbstract
{
    /**
     * @var $wallet_service WalletService
     * @var $user_db User
     */

    private $wallet_service;
    private $user_db;


    public function __construct(Invoice $invoice_db)
    {

        parent::__construct($invoice_db);

        $this->wallet_service = app(WalletService::class);

        $this->user_db = User::find($invoice_db->user_id);

    }

    public function partial()
    {

        //send web socket notification
        $this->socket_service->sendInvoiceMessage($this->invoice_db, 'partial_paid');

        //send user email
        EmailJob::dispatch(new EmailWalletInvoicePaidPartial($this->user_db, $this->invoice_db), $this->user_db->email);

    }

    public function processing()
    {

        // send web socket notification
        $this->socket_service->sendInvoiceMessage($this->invoice_db, 'paid');

    }

    public function completed()
    {

        $pf_paid = number_format(
            ($this->invoice_db->pf_amount / $this->invoice_db->amount) * $this->invoice_db->paid_amount
            , 2, '.', '');

        if ($pf_paid > (double)$this->invoice_db->deposit_amount) {
            $deposit_amount = $pf_paid - ((double)$this->invoice_db->deposit_amount);


            //Deposit Service
            $deposit_service = app(Deposit::class);
            $deposit_service->setConfirmed(false);
            $deposit_service->setUserId($this->user_db->id);
            $deposit_service->setAmount($this->invoice_db->pf_amount);
            $deposit_service->setType('Deposit');
            $deposit_service->setWalletName('Deposit Wallet');

            //Deposit transaction
            /**
             * @var $deposit Deposit
             */
            $deposit = $this->wallet_service->deposit($deposit_service);

            //Deposit check
            if($deposit->getConfirmed()) {
                $this->invoice_db->update([
                    'is_paid' => true
                ]);

                // send web socket notification
                $this->socket_service->sendInvoiceMessage($this->invoice_db, 'confirmed');
            } else {
                Log::error('Deposit wallet user error , InvoiceID => ' . $this->invoice_db->id);
                //TODO email admin/dev-team email
            }


            // send user email
            EmailJob::dispatch(new EmailWalletInvoicePaidComplete($this->user_db, $this->invoice_db),$this->user_db->email);
        }

    }

    public function expired()
    {

        // send web socket notification
        $this->socket_service->sendInvoiceMessage($this->invoice_db, 'expired');

        // send user email
        EmailJob::dispatch(new EmailWalletInvoiceExpired($this->user_db, $this->invoice_db), $this->user_db->email);

    }

    public function invalid()
    {
        // TODO: Implement invalid() method.
    }
}
