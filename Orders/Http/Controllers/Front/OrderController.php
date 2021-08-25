<?php

namespace Orders\Http\Controllers\Front;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Orders\Http\Requests\Front\Order\ListOrderRequest;
use Orders\Http\Requests\Front\Order\OrderRequest;
use Orders\Http\Resources\OrderResource;
use Orders\Models\Order;
use Packages\Services\Id;
use Packages\Services\PackageService;
use Payments\Services\Invoice;
use Payments\Services\PaymentService;

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
        $orders = Order::query()->filter()->simplePaginate();
        return api()->success(null,OrderResource::collection($orders)->response()->getData());
    }

    /**
     * Submit new Order
     * @group
     * Public User > Orders
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

        $order_db->reCalculateCosts();
        $order_db->refresh();



        $invoice_request = new Invoice();
        $invoice_request->setOrderId((int)$order_db->id);
        $invoice_request->setPfAmount($order_db->total_cost_in_usd);
        $invoice_request->setPaymentDriver($order_db->payment_driver);
        $invoice_request->setPaymentType($order_db->payment_type);
        $invoice_request->setPaymentCurrency($order_db->payment_currency);

        $invoice_request->setUser(user($request->header('X-user-id')));
        $invoice = $this->payment_service->pay( $invoice_request);

        return api()->success('success', [
            'payment_currency'=>$invoice->getPaymentCurrency(),
            'amount' => $invoice->getAmount(),
            'checkout_link' => $invoice->getCheckoutLink(),
            'transaction_id' => $invoice->getTransactionId(),
            'expiration_time' => $invoice->getExpirationTime(),
        ]);
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
}
