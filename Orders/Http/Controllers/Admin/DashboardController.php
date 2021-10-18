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
     * get count subscriptions
     * @group
     * Admin User > Orders Dashboard
     * @param ListOrderRequest $request
     * @return JsonResponse
     */
    public function getCountSubscriptions()
    {
        return api()->success(null, SubscriptionCountDataResource::make($this->order_service->getCountPackageSubscriptions()));
    }

    /**
     * get count active package
     * @group
     * Admin User > Orders Dashboard
     * @param ListOrderRequest $request
     * @return JsonResponse
     */
    public function activePackageCount()
    {
        return api()->success(null, SubscriptionCountDataResource::make($this->order_service->activePackageCount()));
    }

    /**
     * get count expired package
     * @group
     * Admin User > Orders Dashboard
     * @param ListOrderRequest $request
     * @return JsonResponse
     */
    public function expiredPackageCount()
    {
        return api()->success(null, SubscriptionCountDataResource::make($this->order_service->ExpiredPackageCount()));
    }

    /**
     * get package overview count
     * @group
     * Admin User > Orders Dashboard
     * @param OrderTypeFilterRequest $request
     * @return JsonResponse
     */
    public function packageOverviewCount(OrderTypeFilterRequest $request)
    {
        return api()->success(null, $this->order_service->packageOverviewCount($request->type));
    }

    /**
     * Get packages based on type
     * @group
     * Admin User > Orders Dashboard
     * @param OrderTypeFilterRequest $request
     * @return JsonResponse
     */
    public function packageTypeCount(OrderTypeFilterRequest $request)
    {
        return api()->success(null, $this->order_service->packageTypeCount($request->type));
    }



}
