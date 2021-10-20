<?php

namespace Orders\Http\Controllers\Front;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Orders\Http\Requests\Front\Order\ListOrderRequest;
use Orders\Http\Requests\Front\Order\OrderTypeFilterRequest;
use Orders\Http\Requests\Front\Order\ShowRequest;
use Orders\Http\Resources\OrderResource;
use Orders\Services\OrderService;
use User\Models\User;

class DashboardController extends Controller
{

    private $order_service;

    public function __construct(OrderService $order_service)
    {
        $this->order_service = $order_service;
    }

    /**
     * get package overview count
     * @group
     * Public User > Orders Dashboard
     * @param OrderTypeFilterRequest $request
     * @return JsonResponse
     */
    public function packageOverviewCount(OrderTypeFilterRequest $request)
    {
        return api()->success(null, $this->order_service->packageOverviewCountForUser($request->type, auth()->user()));
    }

    /**
     * Get packages based on type
     * @group
     * Public User > Orders Dashboard
     * @param OrderTypeFilterRequest $request
     * @return JsonResponse
     */
    public function packageTypeCount(OrderTypeFilterRequest $request)
    {
        return api()->success(null, $this->order_service->packageTypeCountForUser($request->type, auth()->user()));
    }

    /**
     * Order Counts
     * @group
     * Public User > Orders Dashboard
     */
    public function counts()
    {
        $now = now()->toDateTimeString();
        $total_counts = User::query()->whereId(auth()->user()->id)->withCount([
            'orders AS total_orders',
            'orders AS total_paid' => function (Builder $query) {
                $query->whereNotNull('is_paid_at');
            },
            'orders AS total_expired_count' => function (Builder $query) use ($now) {
                $query->whereNotNull('is_paid_at');
                $query->where('expires_at', '<', $now);
            },
            'orders AS total_active_count' => function (Builder $query) use ($now) {
                $query->whereNotNull('is_paid_at');
                $query->where('expires_at', '>', $now);
            }
        ])->first()->toArray();

        return api()->success(null, [
            'total_orders' => $total_counts['total_orders'],
            'total_paid' => $total_counts['total_paid'],
            'total_expired_count' => $total_counts['total_expired_count'],
            'total_active_count' => $total_counts['total_active_count'],
        ]);
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
        /**@var $user User */
        $user = auth()->user();
        $list = $user->orders()->filter()->orderByDesc('id')->paginate();
        return api()->success(null, [
            'list' => OrderResource::collection($list),
            'pagination' => [
                'total' => $list->total(),
                'per_page' => $list->perPage(),
            ]
        ]);
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
        $order = auth()->user()->orders()->find($request->get('id'))->first();
        return api()->success(null, OrderResource::make($order));
    }


}
