<?php

namespace Orders\Http\Controllers\Front;


use Carbon\Carbon;
use Giftcode\Services\Giftcode;
use Giftcode\Services\GiftcodeService;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Orders\Http\Requests\Front\Order\ListOrderRequest;
use Orders\Http\Requests\Front\Order\OrderRequest;
use Orders\Http\Requests\Front\Order\OrderRequestWithGiftcode;
use Orders\Http\Requests\Front\Order\ShowRequest;
use Orders\Http\Resources\OrderResource;
use Orders\Models\Order;
use Packages\Services\Id;
use Packages\Services\PackageService;
use Payments\Services\Invoice;
use Payments\Services\PaymentService;
use Wallets\Services\Wallet;
use Wallets\Services\WalletService;
use Wallets\Services\Withdraw;

class OrderController extends Controller
{
    use  ValidatesRequests;

    private $payment_service;
    private $package_service;

    public function __construct(PaymentService $payment_service,PackageService $package_service)
    {
        $this->payment_service = $payment_service;
        $this->package_service = $package_service;
    }

    /**
     * List orders
     * @group
     * Public User > Orders
     * @param ListOrderRequest $request
     * @return JsonResponse
     */
    public function index(ListOrderRequest $request)
    {
        $orders = $request->user->orders()->filter()->simplePaginate();
        return api()->success(null,OrderResource::collection($orders)->response()->getData());
    }

    /**
     * Get order details
     * @group
     * Public User > Orders
     * @param ShowRequest $request
     * @return JsonResponse
     */
    public function showOrder(ShowRequest $request)
    {
        $order = $request->user->orders()->find($request->get('id'))->first();
        return api()->success(null,OrderResource::make($order));
    }

    /**
     * Submit new Order
     * @group
     * Public User > Orders
     * @param OrderRequest $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function newOrder(OrderRequest $request)
    {
        $this->validatePackage($request);
        $order_db = Order::query()->create([
            "user_id" => user($request->header('X-user-id'))->getId(),
            "payment_type" => $request->payment_type,
            "payment_currency" => $request->payment_currency,
            "payment_driver" => $request->payment_driver,
            "package_id" => $request->package_id
        ]);
        $order_db->refreshOrder();

        $invoice_request = new Invoice();
        $invoice_request->setPayableId((int)$order_db->id);
        $invoice_request->setPayableType('Order');
        $invoice_request->setPfAmount($order_db->total_cost_in_usd);
        $invoice_request->setPaymentDriver($order_db->payment_driver);
        $invoice_request->setPaymentType($order_db->payment_type);
        $invoice_request->setPaymentCurrency($order_db->payment_currency);
        $invoice_request->setUser($request->user->getUserService());

        $invoice = $this->payment_service->pay($invoice_request);

        return api()->success('success', [
            'payment_currency'=>$invoice->getPaymentCurrency(),
            'amount' => $invoice->getAmount(),
            'checkout_link' => $invoice->getCheckoutLink(),
            'transaction_id' => $invoice->getTransactionId(),
            'expiration_time' => $invoice->getExpirationTime(),
        ]);
    }

    public function newOrderWithGiftcode(OrderRequestWithGiftcode $request)
    {
        //check giftcode
        $giftcode_service = app(GiftcodeService::class);
        $giftcode_object = app(Giftcode::class);
        $giftcode_object->setCode($request->get('giftcode'));
        $giftcode_response = $giftcode_service->getGiftcodeByCode($giftcode_object);

        //if any errors occurs, returns exception like validatePackage method
        $this->checkGiftcode($giftcode_response);

        //Redeem giftcode
        $redeem_giftcode = $giftcode_service->redeemGiftcode($giftcode_response,$request->user->getUserService());

        if(!$redeem_giftcode->getRedeemUserId())
            throw new \Exception(trans('order.responses.something-went-wrong'),500);

        /*
         * Giftcode redeemed, we can do other stuff of order
         */

    }

    public function newOrderWithWallet(Order $order)
    {
        /*
         * We can use db transaction , first of all create Order and after that handle wallet by calling this method
         */
        try {
            $wallet_service = app(WalletService::class);

            //Check wallet balance
            $wallet = app(Wallet::class);
            $wallet->setUserId(request()->user->id);
            $wallet->setName('Deposit Wallet');
            $balance = $wallet_service->getBalance($wallet)->getBalance();
            if($balance < $order->total_cost_in_usd)
                throw new \Exception(trans('order.responses.wallet.not-enough-balance'),406);

            //Withdraw user wallet
            $withdraw_object = app(Withdraw::class);
            $withdraw_object->setConfirmed(true);
            $withdraw_object->setUserId(request()->user->id);
            $withdraw_object->setWalletName('Deposit Wallet');
            $withdraw_object->setType('Purchase');
            $withdraw_object->setDescription('Purchase order #' . $order->id);
            $withdraw_object->setAmount($order->total_cost_in_usd);
            $withdraw_response = $wallet_service->withdraw($withdraw_object);
            if(!$withdraw_response->getConfirmed())
                throw new \Exception(trans('order.responses.something-went-wrong'),500);

            //Done.

        } catch (\Throwable $exception) {
            Log::error('Order Module => OrderController | Purchase order with deposit wallet error');
            throw new \Exception(trans('order.responses.something-went-wrong'),500);
        }
    }
    private function validatePackage(Request $request)
    {

        $id = new Id;
        $id->setId($request->package_id);
        $package = $this->package_service->packageById( $id);
        if (!$package->getId()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'package_ids' => ['Package does not exist'],
            ]);
        }
    }

    private function checkGiftcode(Giftcode $giftcode)
    {
        if(!$giftcode->getId()) // code is not valid
            throw \Illuminate\Validation\ValidationException::withMessages([
                'giftcode' => [trans('order.responses.giftcode.wrong-code')],
            ]);

        if($giftcode->getPackageId() != request()->get('package_id'))
            throw \Illuminate\Validation\ValidationException::withMessages([
                'giftcode' => [trans('order.responses.giftcode.wrong-package')],
            ]);

        if($giftcode->getRedeemUserId()) // code is not valid
            throw \Illuminate\Validation\ValidationException::withMessages([
                'giftcode' => [trans('order.responses.giftcode.used')],
            ]);

        if(Carbon::parse($giftcode->getExpirationDate())->isPast())
            throw \Illuminate\Validation\ValidationException::withMessages([
                'giftcode' => [trans('order.responses.giftcode.expired')],
            ]);
    }
}
