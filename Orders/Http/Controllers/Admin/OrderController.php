<?php

namespace Orders\Http\Controllers\Admin;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Orders\Http\Requests\Front\Order\ListOrderRequest;
use Orders\Http\Requests\Front\Order\OrderTypeFilterRequest;
use Orders\Http\Resources\CountDataResource;
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
     * Admin User > Orders
     * @param ListOrderRequest $request
     * @return JsonResponse
     */
    public function getCountSubscriptions()
    {
        return api()->success(null,new CountDataResource($this->order_service->getCountPackageSubscriptions()));
    }

    /**
     * get count active package
     * @group
     * Admin User > Orders
     * @param ListOrderRequest $request
     * @return JsonResponse
     */
    public function activePackageCount()
    {
        return api()->success(null,new CountDataResource($this->order_service->activePackageCount()));
    }

    /**
     * get count deactivate package
     * @group
     * Admin User > Orders
     * @param ListOrderRequest $request
     * @return JsonResponse
     */
    public function deactivatePackageCount()
    {
        return api()->success(null,new CountDataResource($this->order_service->deactivatePackageCount()));
    }

    /**
     * get package overview count
     * @group
     * Admin User > Orders
     * @param OrderTypeFilterRequest $request
     * @return JsonResponse
     */
    public function packageOverviewCount(OrderTypeFilterRequest $request)
    {
        return api()->success(null,$this->order_service->packageOverviewCount($request->type));
    }
}
