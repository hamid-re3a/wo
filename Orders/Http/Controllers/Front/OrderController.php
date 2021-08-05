<?php

namespace Orders\Http\Controllers\Front;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mix\Grpc\Context;
use Orders\Http\Requests\Front\Order\OrderRequest;
use Orders\Models\Order;
use Packages\Services\Id;
use Packages\Services\PackageService;
use Payments\Services\PaymentService;

class OrderController extends Controller
{
    use  ValidatesRequests;

    /**
     * Submit new Order
     * @group
     * Public User > Orders
     */
    public function newOrder(OrderRequest $request,PackageService $package_service,PaymentService $payment_service)
    {
        $this->validatePackages($request,$package_service);

        $order_db = Order::query()->create([
            "user_id" => user($request)->getId(),
            "payment_type" => $request->payment_type,
            "payment_currency" => $request->payment_currency,
            "payment_driver" => $request->payment_driver,
        ]);

        $ids = [];
        foreach ($request->items as $item) {
            for ($i = 0; $i < $item['qty']; $i++)
                $ids[] = ['package_id' => $item['id']];
        }
        $order_db->packages()->createMany($ids);
        $order_db->reCalculateCosts();
        $order_db->refresh();



        $order = new \Orders\Services\Order();
        $order->setId((int)$order_db->id);
        $order->setTotalCostInUsd($order_db->total_cost_in_usd);
        $order->setPaymentDriver($order_db->payment_driver);
        $order->setPaymentType($order_db->payment_type);
        $order->setPaymentCurrency($order_db->payment_currency);

        $order->setUser(user($request));
        $invoice = $payment_service->pay(new Context(), $order);

        return api()->success('success', ['invoice_id' => $invoice->getTransactionId(), 'checkout_link' => $invoice->getCheckoutLink()]);
    }

    private function validatePackages(Request $request, PackageService $package_service)
    {
        $rules = [
            'items.*.id' => 'required',
            'items.*.qty' => 'required|numeric|min:1|max:1',
        ];

        $this->validate($request, $rules);

        foreach ($request->items as $item) {
            $id = new Id;
            $id->setId($item['id']);
            $package = $package_service->packageById(new Context(), $id);
            if (!$package->getId()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'items' => ['Package does not exist'],
                ]);
            }
        }
    }
}
