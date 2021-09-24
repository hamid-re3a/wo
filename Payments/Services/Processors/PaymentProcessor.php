<?php

namespace Payments\Services\Processors;

use Carbon\Carbon;
use Giftcode\Services\Grpc\Giftcode;
use Giftcode\Services\GiftcodeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Orders\Services\OrderService;
use Payments\Jobs\EmailJob;
use Payments\Mail\Payment\EmailInvoiceCreated;
use Payments\Mail\Payment\Wallet\EmailWalletInvoiceCreated;
use Payments\Services\Grpc\Invoice;
use Wallets\Services\Grpc\Wallet;
use Wallets\Services\Grpc\WalletNames;
use Wallets\Services\WalletService;
use Wallets\Services\Grpc\Withdraw;

class PaymentProcessor
{

    private function createInvoiceModel(Invoice $invoice_request)
    {
        return \Payments\Models\Invoice::query()->create([
            'user_id' => $invoice_request->getUser()->getId(),
            'payable_id' => $invoice_request->getPayableId(),
            'payable_type' => $invoice_request->getPayableType(),
            'pf_amount' => $invoice_request->getPfAmount(),
            'amount' => $invoice_request->getAmount(),
            'due_amount' => $invoice_request->getDueAmount(),
            'paid_amount' => $invoice_request->getPaidAmount(),
            'transaction_id' => $invoice_request->getTransactionId(),
            'checkout_link' => $invoice_request->getCheckoutLink(),
            'expiration_time' => Carbon::createFromTimestamp($invoice_request->getExpirationTime()),
            'status' => $invoice_request->getStatus(),
            'additional_status' => $invoice_request->getAdditionalStatus(),
            'payment_type' => $invoice_request->getPaymentType(),
            'payment_driver' => $invoice_request->getPaymentDriver(),
            'payment_currency' => $invoice_request->getPaymentCurrency(),
        ]);
    }

    /**
     * pay by every payment Driver
     * @inheritDoc
     */
    public function pay(Invoice $invoice_request): array
    {
        switch ($invoice_request->getPaymentType()) {
            case 'purchase':
                return $this->payFromGateway($invoice_request);
            case 'giftcode':
                return $this->payFromGiftcode($invoice_request);
                break;
            case 'deposit' :
                return $this->payFromDepositWallet($invoice_request);
            default:
                break;
        }

        Log::error('PaymentService@pay switch case not found PaymentType, TransactionID: ' . $invoice_request->getTransactionId());
        return [false,trans('payment.responses.something-went-wrong')];

    }

    private function payFromGateway(Invoice $invoice_request): array
    {

        switch ($invoice_request->getPaymentDriver()) {
            case 'btc-pay-server':
                return $this->payBtcServer($invoice_request);
                break;
        }
        Log::error('PaymentService@payFromGateway switch case not found PaymentDriver, TransactionID: ' . $invoice_request->getTransactionId());
        return [false,trans('payment.responses.payment-service.gateway-error')];
    }

    private function payFromGiftcode(Invoice $invoice_request): array
    {
        try {
            DB::beginTransaction();

            //Load order
            $order_service = app(OrderService::class);
            $id_object = app(\Orders\Services\Grpc\Id::class);
            $id_object->setId($invoice_request->getPayableId());
            $order_object = $order_service->OrderById($id_object);
            if(empty($order_object->getId()))
                throw new \Exception(trans('payment.responses.something-went-wrong'));
            //check giftcode
            $giftcode_service = app(GiftcodeService::class);
            $giftcode_object = app(Giftcode::class);
            $giftcode_object->setCode(request()->get('giftcode'));
            $giftcode_response = $giftcode_service->getGiftcodeByCode($giftcode_object);

            if (!$giftcode_response->getId()) // code is not valid
                throw new \Exception(trans('payment.responses.giftcode.wrong-code'));

            if ($giftcode_response->getPackageId() != $order_object->getPackageId())
                throw new \Exception(trans('payment.responses.giftcode.wrong-package'));

            if (!empty($giftcode_response->getRedeemUserId()))
                throw new \Exception(trans('payment.responses.giftcode.used'));

            if (!empty($giftcode_response->getExpirationDate()) AND Carbon::parse($giftcode_response->getExpirationDate())->isPast())
                throw new \Exception(trans('payment.responses.giftcode.expired'));

            if (!empty($giftcode_response->getIsCanceled()))
                throw new \Exception(trans('payment.responses.giftcode.canceled'));

            if(is_numeric($order_object->getRegistrationFeeInUsd()) AND $order_object->getRegistrationFeeInUsd() > 0 AND empty($giftcode_response->getRegistrationFeeInUsd()))
                throw new \Exception(trans('payment.responses.giftcode.giftcode-not-included-registration-fee'));

            //Redeem giftcode
            $giftcode_response->setOrderId($order_object->getId());
            $redeem_giftcode = $giftcode_service->redeemGiftcode($giftcode_response, $invoice_request->getUser());

            if (!$redeem_giftcode->getRedeemUserId())
                throw new \Exception(trans('payment.responses.something-went-wrong'));

            //Update Order
            if(!$order_object->getId() OR $order_object->getId() != $invoice_request->getPayableId())
                throw new \Exception(trans('payment.responses.something-went-wrong'));

            //used same time as Giftcode redeem date for future usage
            $order_object->setIsPaidAt($redeem_giftcode->getRedeemDate());
            $order_object->setPaymentDriver('giftcode');
            $order_service->updateOrder($order_object);

            DB::commit();
            return [
                true,
                trans('payment.responses.giftcode.done')
            ];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('PaymentService@payFromGiftcode error ' . $exception->getMessage());
            return [false,$exception->getMessage()];
        }
    }

    private function payFromDepositWallet(Invoice $invoice_request): array
    {
        try {
            DB::beginTransaction();

            //Load order
            $order_service = app(OrderService::class);
            $id_object = app(\Orders\Services\Grpc\Id::class);
            $id_object->setId($invoice_request->getPayableId());
            $order_object = $order_service->OrderById($id_object);

            if(empty($order_object->getId()))
                throw new \Exception(trans('payment.responses.something-went-wrong'));

            //check deposit wallet
            $wallet_service = app(WalletService::class);

            //Check wallet balance
            $wallet = app(Wallet::class);
            $wallet->setUserId($invoice_request->getUserId());
            $wallet->setName('Deposit Wallet');
            $balance = $wallet_service->getBalance($wallet)->getBalance();

            if ($balance < $invoice_request->getPfAmount())
                throw new \Exception(trans('payment.responses.wallet.not-enough-balance'));

            //Prepare withdraw message
            $withdraw_object = app(Withdraw::class);
            $withdraw_object->setUserId(auth()->user()->id);
            $withdraw_object->setWalletName(WalletNames::DEPOSIT);
            $withdraw_object->setType('Package purchased');
            $withdraw_object->setDescription('Purchase order #' . $invoice_request->getPayableId());
            $withdraw_object->setAmount($invoice_request->getPfAmount());
            $withdraw_response = $wallet_service->withdraw($withdraw_object);
            //Do withdraw
            if (!is_string($withdraw_response->getTransactionId()))
                throw new \Exception(trans('payment.responses.something-went-wrong'));

            //used same time as Giftcode redeem date for future usage
            $order_object->setIsPaidAt(now()->toDateTimeString());
            $order_object->setPaymentDriver('deposit');
            $order_service->updateOrder($order_object);

            DB::commit();
            return [
                true,
                trans('payment.responses.wallet.done')
            ];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('PaymentService@payFromDepositWallet error ' . $exception->getMessage());
            return [false,$exception->getMessage()];
        }
    }

    private function payBtcServer(Invoice $invoice_request)
    {
        try {
            $response = Http::withHeaders(['Authorization' => config('payment.btc-pay-server-api-token')])
                ->post(
                    config('payment.btc-pay-server-domain') .
                    'api/v1/stores/' . config('payment.btc-pay-server-store-id') . '/invoices',
                    [
                        'amount' => $invoice_request->getPfAmount(),
                        'currency' => 'usd'
                    ]
                );
            $payment_response = Http::withHeaders(['Authorization' => config('payment.btc-pay-server-api-token')])
                ->get(
                    config('payment.btc-pay-server-domain') . 'api/v1/stores/' .
                    config('payment.btc-pay-server-store-id') . '/invoices/' . $response->json()['id'] . '/payment-methods'
                );

            if ($response->ok() && preg_match('/:(?P<address>.*?)\?amount/', $payment_response[0]['paymentLink'], $btc_link)) {
                DB::beginTransaction();

                $invoice_request->setPfAmount((double)$response->json()['amount']);
                $invoice_request->setAmount((double)$payment_response[0]['amount']);
                $invoice_request->setDueAmount((double)$payment_response[0]['due']);
                $invoice_request->setPaidAmount((double)$payment_response[0]['totalPaid']);
                $invoice_request->setTransactionId($response->json()['id']);
                $invoice_request->setCheckoutLink($btc_link['address']);
                $invoice_request->setStatus($response->json()['status']);
                $invoice_request->setAdditionalStatus($response->json()['additionalStatus']);
                $invoice_request->setExpirationTime($response->json()['expirationTime']);

                if($invoice_request->getPayableType() == 'Order')
                    EmailJob::dispatch(new EmailInvoiceCreated($invoice_request->getUser(), $invoice_request), $invoice_request->getUser()->getEmail());

                if($invoice_request->getPayableType() == 'DepositWallet')
                    EmailJob::dispatch(new EmailWalletInvoiceCreated($invoice_request->getUser(), $invoice_request), $invoice_request->getUser()->getEmail());


                $this->createInvoiceModel($invoice_request);

                DB::commit();
                return [
                    true,
                    [
                        'payment_currency' => $invoice_request->getPaymentCurrency(),
                        'amount' => $invoice_request->getAmount(),
                        'checkout_link' => $invoice_request->getCheckoutLink(),
                        'transaction_id' => $invoice_request->getTransactionId(),
                        'expiration_time' => $invoice_request->getExpirationTime(),
                    ]
                ];

            }
            DB::rollBack();
            return [false,trans('payment.responses.payment-service.btc-pay-server-error')];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('PaymentService@payBtcServer error ' . $exception->getMessage());
            return [false,trans('payment.responses.payment-service.btc-pay-server-error')];
        }
    }

}
