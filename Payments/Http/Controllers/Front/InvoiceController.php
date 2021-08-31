<?php

namespace Payments\Http\Controllers\Front;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Payments\Http\Requests\Invoice\ShowInvoiceRequest;
use Payments\Http\Requests\Invoice\ShowOrderTransactionsRequest;
use Payments\Http\Resources\InvoiceResource;
use Payments\Http\Resources\InvoiceTransactionResource;
use Payments\Models\Invoice;
use Payments\Models\InvoiceTransaction;

class InvoiceController extends Controller
{

    /**
     * Get user pending package invoice
     * @group
     * Public User > Invoices
     */
    public function pendingOrderInvoice()
    {
        $invoice = request()->user->invoices()->where('payable_type','Order')->whereNull('is_paid')->where('expiration_time','>',now()->toDateTimeString());

        if(!$invoice)
            return api()->error(null,null,406);

        return api()->success('success', InvoiceResource::make($invoice));
    }

    /**
     * Get invoices list
     * @group
     * Public User > Invoices
     */
    public function index()
    {
        $invoices = Invoice::query()->where('user_id',request()->header('X-user-id'))->simplePaginate();
        return api()->success(null,InvoiceResource::collection($invoices)->response()->getData());
    }

    /**
     * Get invoice details
     * @group
     * Public User > Invoices
     * @param ShowInvoiceRequest $request
     * @return JsonResponse
     */
    public function show(ShowInvoiceRequest $request)
    {
        $invoice = Invoice::query()->where('transaction_id',$request->get('transaction_id'))->where('user_id',$request->header('X-user-id'))->first();
        return api()->success(null,InvoiceResource::make($invoice));
    }

    /**
     * Get invoice transactions
     * @group
     * Public User > Invoices
     * @param ShowOrderTransactionsRequest $request
     * @return JsonResponse
     */
    public function transactions(ShowOrderTransactionsRequest $request)
    {
        $invoice = Invoice::query()->where('transaction_id',$request->get('transaction_id'))->where('user_id',$request->header('X-user-id'))->with('transactions')->first();
        return api()->success(null,InvoiceTransactionResource::collection($invoice->transactions));
    }
}
