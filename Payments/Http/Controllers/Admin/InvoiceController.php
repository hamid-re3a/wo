<?php

namespace Payments\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Payments\Http\Requests\Invoice\CancelInvoiceRequest;
use Payments\Http\Requests\Invoice\ShowInvoiceRequest;
use Payments\Http\Requests\Invoice\ShowOrderTransactionsRequest;
use Payments\Http\Resources\Admin\InvoiceResource;
use Payments\Http\Resources\InvoiceTransactionResource;
use Payments\Models\Invoice;
use Payments\Services\InvoiceService;

class InvoiceController extends Controller
{

    //TODO refactor and use repository
    /**
     * Get all invoices
     * @group
     * Admin User > Payments > Invoices
     */
    public function index()
    {
        $list = Invoice::query()->paginate();
        return api()->success(null,[
            'list' => InvoiceResource::collection($list),
            'pagination' => [
                'total' => $list->total(),
                'per_page' => $list->perPage(),
            ]
        ]);
    }

    /**
     * Get overpaid invoices
     * @group
     * Admin User > Payments > Invoices
     */
    public function overPaidInvoices()
    {
        $list = Invoice::query()->where('additional_status','=','PaidOver')->paginate();
        return api()->success(null,[
            'list' => InvoiceResource::collection($list),
            'pagination' => [
                'total' => $list->total(),
                'per_page' => $list->perPage(),
            ]
        ]);
    }

    /**
     * Get pending package invoices
     * @group
     * Admin User > Payments > Invoices
     */
    public function pendingOrderInvoices()
    {
        $list = Invoice::query()->with('user')->where('payable_type','Order')->where('is_paid',0)->where('expiration_time','>',now()->toDateTimeString())->paginate();

        return api()->success(null, [
            'list' => InvoiceResource::collection($list),
            'pagination' => [
                'total' => $list->total(),
                'perPage' => $list->perPage()
            ]
        ]);
    }

    /**
     * Get pending wallet invoices
     * @group
     * Admin User > Payments > Invoices
     */
    public function pendingWalletInvoices()
    {
        $list = Invoice::query()->with('user')->where('payable_type','DepositWallet')->where('is_paid',0)->where('expiration_time','>',now()->toDateTimeString())->paginate();

        return api()->success(null, [
            'list' => InvoiceResource::collection($list),
            'pagination' => [
                'total' => $list->total(),
                'perPage' => $list->perPage()
            ]
        ]);
    }

    /**
     * Get invoice details
     * @group
     * Admin User > Payments > Invoices
     * @param ShowInvoiceRequest $request
     * @return JsonResponse
     */
    public function show(ShowInvoiceRequest $request)
    {
        $invoice = Invoice::query()->where('transaction_id',$request->get('transaction_id'))->first();

        return api()->success(null,InvoiceResource::make($invoice));
    }

    /**
     * Get invoice transactions
     * @group
     * Admin User > Payments > Invoices
     * @param ShowOrderTransactionsRequest $request
     * @return JsonResponse
     */
    public function transactions(ShowOrderTransactionsRequest $request)
    {
        $invoice = Invoice::query()->where('transaction_id',$request->get('transaction_id'))->with('transactions')->first();
        return api()->success(null,InvoiceTransactionResource::collection($invoice->transactions));
    }
}
