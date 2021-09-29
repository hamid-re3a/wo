<?php

namespace Orders\Http\Controllers\Front;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Orders\Http\Requests\Front\Order\ListOrderRequest;
use Orders\Http\Requests\Front\Order\OrderRequest;
use Orders\Http\Requests\Front\Order\ShowRequest;
use Orders\Http\Resources\OrderResource;
use Orders\Models\Order;
use Orders\Services\MlmClientFacade;
use Packages\Services\Grpc\Id;
use Packages\Services\PackageService;
use Payments\Services\Grpc\Invoice;
use Payments\Services\Processors\PaymentFacade;
use User\Models\User;

class DashboardController extends Controller
{
    /**
     * Order Counts
     * @group
     * Public User > Orders
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
        $orders = auth()->user()->orders()->filter()->simplePaginate();
        return api()->success(null, OrderResource::collection($orders)->response()->getData());
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
