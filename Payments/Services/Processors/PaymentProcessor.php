<?php

namespace Payments\Services\Processors;

use Carbon\Carbon;
use Giftcode\Services\Grpc\Giftcode;
use Giftcode\Services\GiftcodeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Orders\Services\Grpc\Order;
use Packages\Services\Grpc\Id;
use Packages\Services\PackageService;
use Payments\Jobs\EmailJob;
use Payments\Mail\Payment\EmailInvoiceCreated;
use Payments\Mail\Payment\Wallet\EmailWalletInvoiceCreated;
use Payments\Services\Grpc\Invoice;
use User\Models\User;
use User\Services\UserService;
use Wallets\Services\Grpc\Wallet;
use Wallets\Services\Grpc\WalletNames;
use Wallets\Services\WalletService;
use Wallets\Services\Grpc\Withdraw;

class PaymentProcessor
{


    /**
     * pay by every payment Driver
     * @inheritDoc
     */
    public function pay(Invoice $invoice_request, Order $order_service = null): array
    {
        switch ($invoice_request->getPaymentType()) {
            case 'purchase':
                return $this->payFromGateway($invoice_request);
            case 'giftcode':
                return $this->payFromGiftcode($invoice_request,$order_service);
                break;
            case 'deposit' :
                return $this->payFromDepositWallet($invoice_request,$order_service);
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
        Log::error('PaymentProcessor@payFromGateway switch case not found PaymentDriver, TransactionID: ' . $invoice_request->getTransactionId());
        return [false,trans('payment.responses.payment-service.gateway-error')];
    }

    private function payFromGiftcode(Invoice $invoice_request,Order $order_service): array
    {
        try {
            DB::beginTransaction();

            //check giftcode
            $giftcode_service = app(GiftcodeService::class);
            $giftcode_object = app(Giftcode::class);
            $giftcode_object->setCode(request()->get('giftcode'));
            $giftcode_response = $giftcode_service->getGiftcodeByCode($giftcode_object);

            if (!$giftcode_response->getId()) // code is not valid
                throw new \Exception(trans('payment.responses.giftcode.wrong-code'),406);

            if ($giftcode_response->getPackageId() != $order_service->getPackageId())
                throw new \Exception(trans('payment.responses.giftcode.wrong-package'),406);

            if (!empty($giftcode_response->getRedeemUserId()))
                throw new \Exception(trans('payment.responses.giftcode.used'),406);

            if (!empty($giftcode_response->getExpirationDate()) AND Carbon::parse($giftcode_response->getExpirationDate())->isPast())
                throw new \Exception(trans('payment.responses.giftcode.expired'),406);

            if (!empty($giftcode_response->getIsCanceled()))
                throw new \Exception(trans('payment.responses.giftcode.canceled'),406);

            if(is_numeric($order_service->getRegistrationFeeInPf()) AND $order_service->getRegistrationFeeInPf() > 0 AND empty($giftcode_response->getRegistrationFeeInPf()))
                throw new \Exception(trans('payment.responses.giftcode.giftcode-not-included-registration-fee'),406);

            //Redeem giftcode
            $giftcode_response->setOrderId($order_service->getId());
            $redeem_giftcode = $giftcode_service->redeemGiftcode($giftcode_response, $invoice_request->getUser());

            if (!$redeem_giftcode->getRedeemUserId())
                throw new \Exception(trans('payment.responses.something-went-wrong'),400);

            DB::commit();
            return [
                true,
                trans('payment.responses.giftcode.done')
            ];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('PaymentProcessor@payFromGiftcode error ' . $exception->getMessage());
            return [false,$exception->getMessage()];
        }
    }

    private function payFromDepositWallet(Invoice $invoice_request,Order $order_object): array
    {
        try {
            DB::beginTransaction();

            //Wallet service
            $wallet_service = app(WalletService::class);

            //Check wallet balance
            $wallet = app(Wallet::class);
            $wallet->setUserId($invoice_request->getUserId());
            $wallet->setName(WalletNames::DEPOSIT);
            $balance = $wallet_service->getBalance($wallet)->getBalance();
            if ($balance < $invoice_request->getPfAmount())
                throw new \Exception(trans('payment.responses.wallet.not-enough-balance'),406);

            $package_service = app(PackageService::class);
            $package_object = $package_service->packageFullById(app(Id::class)->setId($order_object->getPackageId()));

            $user = auth()->check() ? auth()->user() : User::query()->find($order_object->getUserId());
            $user_member_id = $user->member_id;

            if($user->id != $order_object->getUserId()) {
                $user_service = app(UserService::class);
                $user_object = $user_service->findByIdOrFail($order_object->getUserId());
                $user_member_id = $user_object->member_id;
            }

            $description = [
                'member_id' => $user_member_id,
                'package_name' => $package_object->getName() . '(' . $package_object->getShortName() .')',
                'order_id' => $order_object->getId()
            ];

            //Prepare withdraw message
            $withdraw_service = app(Withdraw::class);
            $withdraw_service->setUserId($invoice_request->getUserId());
            $withdraw_service->setWalletName(WalletNames::DEPOSIT);
            $withdraw_service->setType('Package purchased');
            $withdraw_service->setDescription(serialize($description));
            $withdraw_service->setAmount($invoice_request->getPfAmount());
            $withdraw_response = $wallet_service->withdraw($withdraw_service);

            //Do withdraw
            if (empty($withdraw_response->getTransactionId()))
                throw new \Exception(trans('payment.responses.something-went-wrong'),400);

            DB::commit();
            return [
                true,
                trans('payment.responses.wallet.done')
            ];
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('PaymentProcessor@payFromDepositWallet error Line =>  ' . $exception->getLine() . ' | MSG => ' . $exception->getMessage());
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
                        'amount' => pfToUsd($invoice_request->getPfAmount()),
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
                        'amount' => $invoice_request->getDueAmount(),
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
            Log::error('PaymentProcessor@payBtcServer error ' . $exception->getMessage());
            return [false,trans('payment.responses.payment-service.btc-pay-server-error')];
        }
    }

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

}
