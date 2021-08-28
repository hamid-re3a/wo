<?php

namespace Orders\Http\Controllers\Front;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Orders\Http\Requests\Front\Order\ListOrderRequest;
use Orders\Http\Requests\Front\Order\OrderRequest;
use Orders\Http\Requests\Front\Order\ShowRequest;
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
        $this->validatePackages($request);

        $order_db = Order::query()->create([
            "user_id" => user($request->header('X-user-id'))->getId(),
            "payment_type" => $request->payment_type,
            "payment_currency" => $request->payment_currency,
            "payment_driver" => $request->payment_driver,
        ]);

        $ids = [];
        foreach ($request->package_ids as $item) {
            for ($i = 0; $i < $item['qty']; $i++)
                $ids[] = ['package_id' => $item['id']];
        }
        $order_db->packages()->createMany($ids);
        $order_db->reCalculateCosts();
        $order_db->refresh();

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

    private function validatePackages(Request $request)
    {
        $rules = [
            'package_ids.*.id' => 'required',
            'package_ids.*.qty' => 'required|numeric|min:1|max:1',
        ];

        $this->validate($request, $rules);

        foreach ($request->package_ids as $item) {
            $id = new Id;
            $id->setId($item['id']);
            $package = $this->package_service->packageById( $id);
            if (!$package->getId()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'package_ids' => ['Package does not exist'],
                ]);
            }
        }
    }
}
