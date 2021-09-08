<?php

namespace Orders\Http\Controllers\Admin;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Orders\Http\Requests\Front\Order\ListOrderRequest;
use Orders\Http\Resources\OrderResource;
use Orders\Http\Resources\SubscriptionsCountResource;
use Orders\Services\OrderService;
use Packages\Services\PackageService;
use Payments\Services\PaymentService;

class OrderController extends Controller
{
    use  ValidatesRequests;

    private $payment_service;
    private $package_service;
    private $order_service;

    public function __construct(PaymentService $payment_service,PackageService $package_service, OrderService $order_service)
    {
        $this->payment_service = $payment_service;
        $this->package_service = $package_service;
        $this->order_service = $order_service;
    }

    /**
     * get count subscriptions
     * @group
     * Public User > Orders > admin
     * @param ListOrderRequest $request
     * @return JsonResponse
     */
    public function getCountSubscriptions()
    {
        return api()->success(null,new SubscriptionsCountResource($this->order_service->getCountPackageSubscriptions()));
    }
}
