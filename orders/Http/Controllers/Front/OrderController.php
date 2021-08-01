<?php

namespace Orders\Http\Controllers\Front;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mix\Grpc\Context;
use Orders\Http\Requests\Front\Order\OrderRequest;
use Orders\Models\Order;
use Payments\Services\PaymentService;

class OrderController extends Controller
{
    /**
     * Submit new Order
     * @group
     * Public User > Orders
     */
    public function newOrder(OrderRequest $request)
    {
//        $this->validatePackages($request);
//
//            $order_db = Order::query()->create([
//                "user_id" => user($request)->getId(),
//                "total_cost_in_usd" => 10,
//                "payment_type" => $request->payment_type,
//                "payment_currency" => $request->payment_currency,
//                "payment_driver" => $request->payment_driver,
//            ]);
//
//        $payment_service = new PaymentService;
//
//        $order = new \Orders\Services\Order();
//        $order->setId((int)$order_db->id);
//        $order->setTotalCostInUsd($order_db->total_cost_in_usd);
//        $order->setPaymentDriver($order_db->payment_driver);
//        $order->setPaymentType($order_db->payment_type);
//        $order->setPaymentCurrency($order_db->payment_currency);
//
//        $invoice = $payment_service->pay(new Context(), $order);

//        return api()->success('success',$invoice);
        return api()->success();
    }

    private function validatePackages(Request $request)
    {
        $rules = [
            'items.*.id' => 'required',
            'items.*.qty' => 'required|numeric|min:1',
        ];
    }
}
