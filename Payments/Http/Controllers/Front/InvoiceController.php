<?php

namespace Payments\Http\Controllers\Front;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Payments\Http\Requests\Invoice\CancelInvoiceRequest;
use Payments\Http\Requests\Invoice\ShowInvoiceRequest;
use Payments\Http\Requests\Invoice\ShowOrderTransactionsRequest;
use Payments\Http\Resources\InvoiceResource;
use Payments\Http\Resources\InvoiceTransactionResource;
use Payments\Services\InvoiceService;
use User\Models\User;

class InvoiceController extends Controller
{
    private $invoice_service;
    /**@var $user User */
    private $user;

    public function __construct(InvoiceService $invoice_service)
    {
        $this->invoice_service = $invoice_service;
        if (auth()->check())
            $this->user = auth()->user();
    }

    /**
     * Get user pending package invoice
     * @group
     * Public User > Payments > Invoices
     */
    public function pendingOrderInvoice()
    {

        $pending_invoice = $this->user->orderInvoices()
            ->with([
                'transactions',
                'refunder'
            ])
            ->where('is_paid', 0)
            ->where('expiration_time', '>', now()->toDateTimeString())
            ->first();

        if (!$pending_invoice) {
            $paid_invoice = $this->user->orderInvoices()->where('is_paid', 1)->count();
            if ($paid_invoice)
                return api()->success(null, [
                    'status' => 'confirmed'
                ]);

            return api()->error(null, [
                'has_invoice' => false,
            ], 400);
        }

        return api()->success(null, InvoiceResource::make($pending_invoice));
    }

    /**
     * Get user pending wallet invoice
     * @group
     * Public User > Payments > Invoices
     */
    public function pendingWalletInvoice()
    {
        $pending_invoice = $this->user->walletInvoices()
            ->with([
                'transactions',
                'refunder'
            ])
            ->where('is_paid', 0)
            ->where('expiration_time', '>', now()->toDateTimeString())
            ->first();

        if (!$pending_invoice) {
            $paid_invoice = $this->user->walletInvoices()->where('is_paid', 1)->count();
            if ($paid_invoice)
                return api()->success(null, [
                    'status' => 'confirmed'
                ]);

            return api()->error(null, null, 404);
        }

        return api()->success(null, InvoiceResource::make($pending_invoice));
    }

    /**
     * Get invoices list
     * @group
     * Public User > Payments > Invoices
     */
    public function index()
    {
        $list = $this->user->invoices()
            ->with([
                'transactions',
                'refunder'
            ])
            ->paginate();

        return api()->success(null, [
            'list' => InvoiceResource::collection($list),
            'pagination' => [
                'total' => $list->total(),
                'per_page' => $list->perPage(),
            ]
        ]);
    }

    /**
     * Get invoice details
     * @group
     * Public User > Payments > Invoices
     * @param ShowInvoiceRequest $request
     * @return JsonResponse
     */
    public function show(ShowInvoiceRequest $request)
    {
        $invoice = $this->user->invoices()
            ->with([
                'transactions',
                'refunder'
            ]);

        if ($request->has('transaction_id'))
            $invoice->when($request->has('transaction_id'), function (Builder $subQuery) use ($request) {
                return $subQuery->where('transaction_id', $request->get('transaction_id'));
            });
        else
            $invoice->when($request->has('order_id'), function (Builder $subQuery) use ($request) {
                return $subQuery->where('payable_id', $request->get('order_id'))->where('payable_type', '=', 'Order');
            });

        $invoice = $invoice->first();
        if (!$invoice)
            return api()->error(null, null, 404);

        return api()->success(null, InvoiceResource::make($invoice));
    }

    /**
     * Get invoice transactions
     * @group
     * Public User > Payments > Invoices
     * @param ShowOrderTransactionsRequest $request
     * @return JsonResponse
     */
    public function transactions(ShowOrderTransactionsRequest $request)
    {
        $invoice = $this->user->invoices()
            ->with([
                'transactions',
                'refunder'
            ]);

        if ($request->has('transaction_id'))
            $invoice->when($request->has('transaction_id'), function (Builder $subQuery) use ($request) {
                return $subQuery->where('transaction_id', $request->get('transaction_id'));
            });
        else
            $invoice->when($request->has('order_id'), function (Builder $subQuery) use ($request) {
                return $subQuery->where('payable_id', $request->get('order_id'))->where('payable_type', '=', 'Order');
            });
        $invoice = $invoice->first();

        if (!$invoice)
            return api()->error(null, null, 404);

        return api()->success(null, InvoiceTransactionResource::collection($invoice->transactions));
    }

    /**
     * Cancel invoice by user
     * @group
     * Public User > Invoices
     * @param ShowOrderTransactionsRequest $request
     * @return JsonResponse
     */
    public function cancelInvoice(CancelInvoiceRequest $request)
    {
        $this->invoice_service->cancelInvoice($request->transaction_id);
        return api()->success("invoice has been canceled", null);
    }
}
