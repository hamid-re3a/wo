<?php

namespace Orders\Http\Controllers\Admin;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Orders\Http\Requests\Front\Order\ListOrderRequest;
use Orders\Http\Requests\Front\Order\OrderTypeFilterRequest;
use Orders\Http\Resources\SubscriptionCountDataResource;
use Orders\Services\OrderService;

class DashboardController extends Controller
{
    //TODO Refactor whole controller :|
    use  ValidatesRequests;

    private $order_service;

    public function __construct(OrderService $order_service)
    {
        $this->order_service = $order_service;
    }

    /**
     * Sums orders
     * @group
     * Admin User > Orders Dashboard
     */
    public function sums()
    {
        return api()->success(null, [
            'paid_orders_sum' => $this->order_service->getPaidOrdersSum(),
        ]);
    }

    /**
     * Counts orders
     * @group
     * Admin User > Orders Dashboard
     */
    public function counts()
    {
        return api()->success(null, [
            'total_orders' => $this->order_service->getCountOrders(),
            'active_orders' => $this->order_service->getActiveOrdersCount(),
            'expired_orders' => $this->order_service->getExpiredOrders(),
        ]);
    }


    /**
     * Packages overview count chart
     * @group
     * Admin User > Orders Dashboard
     * @param OrderTypeFilterRequest $request
     * @return JsonResponse
     */
    public function packageOverviewCount(OrderTypeFilterRequest $request)
    {
        return api()->success(null, $this->order_service->packageOverviewCountChart($request->type));
    }

    /**
     * Packages based on type chart
     * @group
     * Admin User > Orders Dashboard
     * @param OrderTypeFilterRequest $request
     * @return JsonResponse
     */
    public function packageTypeCount(OrderTypeFilterRequest $request)
    {
        return api()->success(null, $this->order_service->packageTypeCountChart($request->type));
    }

    /**
     * Packages percentage based on type chart
     * @group
     * Admin User > Orders Dashboard
     * @param OrderTypeFilterRequest $request
     * @return JsonResponse
     */
    public function packageTypePercentCount(OrderTypeFilterRequest $request)
    {
        return api()->success(null, $this->order_service->packageTypePercentageCountChart($request->type));
    }

}
