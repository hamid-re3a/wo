<?php

namespace Payments\Http\Controllers\Front;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Payments\Http\Requests\Invoice\ShowOrderTransactionsRequest;
use Payments\Http\Resources\InvoiceTransactionResource;
use Payments\Models\InvoiceTransaction;

class InvoiceController extends Controller
{

    /**
     * Get Invoice transactions by order id
     * @group
     * Public User > Invoices
     * @param ShowOrderTransactionsRequest $request
     * @return JsonResponse
     */
    public function getTransactionsByOrderId(ShowOrderTransactionsRequest $request)
    {
        $transactions = InvoiceTransaction::query()->whereHas('invoice', function($subQuery) use($request) {
            $subQuery->where('order_id',$request->get('order_id'));
        })->get();
        return api()->success(null,InvoiceTransactionResource::collection($transactions));
    }
}
