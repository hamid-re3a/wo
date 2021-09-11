<?php

namespace Payments\Services;

use Carbon\Carbon;
use Giftcode\Services\Giftcode;
use Giftcode\Services\GiftcodeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Orders\Services\Order;
use Orders\Services\OrderService;
use Payments\Jobs\EmailJob;
use Payments\Mail\Payment\EmailInvoiceCreated;
use Payments\Mail\Payment\Wallet\EmailWalletInvoiceCreated;
use Payments\Repository\PaymentCurrencyRepository;
use Payments\Repository\PaymentDriverRepository;
use Payments\Repository\PaymentTypesRepository;
use Wallets\Services\Wallet;
use Wallets\Services\WalletService;
use Wallets\Services\Withdraw;

class PaymentService implements PaymentsServiceInterface
{
    private $payment_type_repository;
    private $payment_currency_repository;
    private $payment_driver_repository;

    public function __construct(PaymentTypesRepository $payment_type_repository, PaymentDriverRepository $payment_driver_repository, PaymentCurrencyRepository $payment_currency_repository)
    {
        $this->payment_type_repository = $payment_type_repository;
        $this->payment_driver_repository = $payment_driver_repository;
        $this->payment_currency_repository = $payment_currency_repository;
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
            $id_object = app(\Orders\Services\Id::class);
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
            $id_object = app(\Orders\Services\Id::class);
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
            $withdraw_object->setConfirmed(true);
            $withdraw_object->setUserId(request()->user->id);
            $withdraw_object->setWalletName('Deposit Wallet');
            $withdraw_object->setType('Purchase');
            $withdraw_object->setDescription('Purchase order #' . $invoice_request->getPayableId());
            $withdraw_object->setAmount($invoice_request->getPfAmount());
            $withdraw_response = $wallet_service->withdraw($withdraw_object);
            //Do withdraw
            if (!$withdraw_response->getConfirmed())
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

    /**
     * @inheritDoc
     */
    public function getInvoiceById(Id $request): Invoice
    {

        $response_invoice = new Invoice;
        $invoice = \Payments\Models\Invoice::query()->find($request->getId());
        $invoice->refresh();
        $response_invoice->setPayableId((int)$invoice->payable_id);
        $response_invoice->setPayableType($invoice->payable_type);
        $response_invoice->setPfAmount((double)$invoice->pf_amount);
        $response_invoice->setAmount((double)$invoice->amount);
        $response_invoice->setTransactionId($invoice->transaction_id);
        $response_invoice->setCheckoutLink($invoice->checkout_link);
        $response_invoice->setStatus($invoice->status);
        $response_invoice->setAdditionalStatus($invoice->additional_status);
        $response_invoice->setPaidAmount((double)$invoice->paid_amount);
        $response_invoice->setDueAmount((double)$invoice->due_amount);
        if ($invoice->expiration_time)
            $response_invoice->setExpirationTime((string)Carbon::make($invoice->expiration_time)->timestamp);
        $response_invoice->setIsPaid((boolean)$invoice->is_paid);

        return $response_invoice;


    }

    /**
     * @inheritDoc
     */
    public function getPaymentCurrencies(EmptyObject $request): PaymentCurrencies
    {
        $payment_currency_data = $this->payment_currency_repository->getAll();
        $payment_currencies = new PaymentCurrencies();
        $payment_currencies->setPaymentCurrencies($this->mapPaymentCurrency($payment_currency_data));
        return $payment_currencies;
    }


    /**
     * @inheritDoc
     */
    public function getPaymentTypes(EmptyObject $request): PaymentTypes
    {
        $payment_types_data = $this->payment_type_repository->getAll();
        $payment_types = new PaymentTypes();
        $payment_types->setPaymentTypes($this->mapPaymentType($payment_types_data));
        return $payment_types;
    }

    /**
     * this function get collection of data paymentCurrency to array of paymentCurrency class
     * @param $payment_currencies
     * @return mixed
     */
    private function mapPaymentCurrency($payment_currencies)
    {
        $data_array =  $payment_currencies->map(function ($item) {
            $payment_currency = new PaymentCurrency();
            $payment_currency->setId($item->id);
            $payment_currency->setName($item->name);
            $payment_currency->setIsActive($item->is_active);
            $payment_currency->setPaymentDriver($this->mapPaymentDriver($item->paymentDriver));
            return $payment_currency;
        });
        return $data_array->toArray();
    }

    /**
     * this function get collection of data paymentCurrency to array of paymentCurrency class
     * @param PaymentCurrency $paymentCurrency
     * @return mixed
     */
    public function filterNamePaymentCurrency(PaymentCurrency $paymentCurrency)
    {
        return $paymentCurrency->getName();
    }

    /**
     * this function for get paymentDriver collection to array of paymentDriver class
     * @param $payment_drivers
     * @return mixed
     */
    private function mapPaymentDriver($payment_drivers)
    {
        $data_array = $payment_drivers->map(function ($item) {
            $payment_driver = new PaymentDriver();
            $payment_driver->setId($item->id);
            $payment_driver->setName($item->name);
            $payment_driver->setIsActive($item->is_active);
            return $payment_driver;
        });
        return $data_array->toArray();
    }

    /**
     * this function get collection of data paymentType to array of paymentType class
     * @param $payment_types
     * @return mixed
     */
    private function mapPaymentType($payment_types)
    {
        $data_array = $payment_types->map(function ($item){
            $payment_type = new PaymentType();
            $payment_type->setId($item->id);
            $payment_type->setName($item->name);
            $payment_type->setIsActive($item->is_active);
            return $payment_type;
        });
        return $data_array->toArray();
    }

    /**
     * createCurrency
     * @param $payment_currency
     * @return mixed
     */
    public function createPaymentCurrency(PaymentCurrency $payment_currency): PaymentCurrency
    {
        $payment_currency_data = $this->payment_currency_repository->create($payment_currency);
        $payment_currency->setIsActive($payment_currency_data->is_active);
        $payment_currency->setName($payment_currency_data->name);
        $payment_currency->setId($payment_currency_data->id);
        $payment_currency->setCreatedAt($payment_currency_data->created_at->format("Y-m-d H:s:m"));
        $payment_currency->setUpdatedAt($payment_currency_data->updated_at->format("Y-m-d H:s:m"));
        return $payment_currency;
    }

    /**
     * createCurrency
     * @param $payment_currency
     * @return mixed
     */
    public function updatePaymentCurrency(PaymentCurrency $payment_currency): PaymentCurrency
    {
        $payment_currency_data = $this->payment_currency_repository->update($payment_currency);
        $payment_currency->setIsActive($payment_currency_data->is_active);
        $payment_currency->setName($payment_currency_data->name);
        $payment_currency->setId($payment_currency_data->id);
        $payment_currency->setCreatedAt($payment_currency_data->created_at->format("Y-m-d H:s:m"));
        $payment_currency->setUpdatedAt($payment_currency_data->updated_at->format("Y-m-d H:s:m"));
        return $payment_currency;
    }

    /**
     * createCurrency
     * @param $payment_currency
     * @return mixed
     */
    public function deletePaymentCurrency(PaymentCurrency $payment_currency)
    {
        return $this->payment_currency_repository->delete($payment_currency);
    }

    /**
     * createCurrency
     * @param $payment_currency
     * @return mixed
     */
    public function createPaymentDriver(PaymentDriver $payment_driver): PaymentDriver
    {
        $payment_driver_data = $this->payment_driver_repository->create($payment_driver);
        $payment_driver->setIsActive($payment_driver_data->is_active);
        $payment_driver->setName($payment_driver_data->name);
        $payment_driver->setId($payment_driver_data->id);
        $payment_driver->setCreatedAt($payment_driver_data->created_at->format("Y-m-d H:s:m"));
        $payment_driver->setUpdatedAt($payment_driver_data->updated_at->format("Y-m-d H:s:m"));
        return $payment_driver;
    }

    /**
     * createDriver
     * @param $payment_driver
     * @return mixed
     */
    public function updatePaymentDriver(PaymentDriver $payment_driver): PaymentDriver
    {
        $payment_driver_data = $this->payment_driver_repository->update($payment_driver);
        $payment_driver->setIsActive($payment_driver_data->is_active);
        $payment_driver->setName($payment_driver_data->name);
        $payment_driver->setId($payment_driver_data->id);
        $payment_driver->setCreatedAt($payment_driver_data->created_at->format("Y-m-d H:s:m"));
        $payment_driver->setUpdatedAt($payment_driver_data->updated_at->format("Y-m-d H:s:m"));
        return $payment_driver;
    }

    /**
     * delete driver
     * @param $payment_driver
     * @return mixed
     */
    public function deletePaymentDriver(PaymentDriver $payment_driver)
    {
        return $this->payment_driver_repository->delete($payment_driver);
    }

    /**
     * create PaymentType
     * @param $payment_currency
     * @return mixed
     */
    public function createPaymentType(PaymentType $payment_type): PaymentType
    {
        $payment_type_data = $this->payment_type_repository->create($payment_type);
        $payment_type->setIsActive($payment_type_data->is_active);
        $payment_type->setName($payment_type_data->name);
        $payment_type->setId($payment_type_data->id);
        $payment_type->setCreatedAt($payment_type_data->created_at->format("Y-m-d H:s:m"));
        $payment_type->setUpdatedAt($payment_type_data->updated_at->format("Y-m-d H:s:m"));
        return $payment_type;
    }

    /**
     * update PaymentType
     * @param $payment_type
     * @return mixed
     */
    public function updatePaymentType(PaymentType $payment_type): PaymentType
    {
        $payment_type_data = $this->payment_type_repository->update($payment_type);
        $payment_type->setIsActive($payment_type_data->is_active);
        $payment_type->setName($payment_type_data->name);
        $payment_type->setId($payment_type_data->id);
        $payment_type->setCreatedAt($payment_type_data->created_at->format("Y-m-d H:s:m"));
        $payment_type->setUpdatedAt($payment_type_data->updated_at->format("Y-m-d H:s:m"));
        return $payment_type;
    }

    /**
     * delete PaymentType
     * @param $payment_type
     * @return mixed
     */
    public function deletePaymentType(PaymentType $payment_type)
    {
        return $this->payment_type_repository->delete($payment_type);
    }
}
