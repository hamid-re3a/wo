<?php

namespace Orders\Http\Controllers\Front;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Orders\Http\Requests\Front\Order\ListOrderRequest;
use Orders\Http\Requests\Front\Order\ShowRequest;
use Orders\Http\Resources\OrderResource;
use User\Models\User;

class DashboardController extends Controller
{
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
