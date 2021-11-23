<?php

namespace Payments\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Payments\Http\Requests\Admin\RefundOrCancelOverPaidInvoiceRequest;
use Payments\Http\Requests\Invoice\ShowInvoiceRequest;
use Payments\Http\Requests\Invoice\ShowOrderTransactionsRequest;
use Payments\Http\Resources\Admin\InvoiceResource;
use Payments\Http\Resources\InvoiceTransactionResource;
use Payments\Models\Invoice;
use Wallets\Services\Grpc\Deposit;
use Wallets\Services\Grpc\WalletNames;
use Wallets\Services\WalletService;

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
        $list = Invoice::query()
            ->orders()
            ->paid()
            ->paidOverNotRefunded()
            ->with('transactions','user')
            ->paginate();
        return api()->success(null,[
            'list' => InvoiceResource::collection($list),
            'pagination' => [
                'total' => $list->total(),
                'per_page' => $list->perPage(),
            ]
        ]);
    }

    /**
     * Refund overpaid invoice
     * @group
     * Admin User > Payments > Invoices
     * @param RefundOrCancelOverPaidInvoiceRequest $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function refundOverPaidInvoice(RefundOrCancelOverPaidInvoiceRequest $request)
    {
        try {
            DB::beginTransaction();
            $invoice = Invoice::query()->whereTransactionId($request->get('transaction_id'))->first();
            $refundable_amount = $this->refundToWallet($invoice,true);

            $invoice->update([
                'refund_status' => 'user',
                'is_refund_at' => now()->toDateTimeString(),
                'refunder_user_id' => auth()->user()->id ,
                'deposit_amount' => $refundable_amount
            ]);

            DB::commit();

            return api()->success();
        } catch (\Throwable $exception) {
            Log::error('Payments\Http\Controllers\Admin\InvoiceController@refundOverPaidInvoice error => ' . $exception->getMessage());
            if(isset($invoice))
                Log::error('Payments\Http\Controllers\Admin\InvoiceController@refundOverPaidInvoice error => ' . $invoice->id);
            return api()->error(null,null,$exception->getCode(),[
                'subject' => $exception->getMessage()
            ]);
        }
    }

    /**
     * Cancel overpaid invoice
     * @group
     * Admin User > Payments > Invoices
     * @param RefundOrCancelOverPaidInvoiceRequest $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function cancelOverPaidInvoice(RefundOrCancelOverPaidInvoiceRequest $request)
    {
        try {
            DB::beginTransaction();
            $invoice = Invoice::query()->whereTransactionId($request->get('transaction_id'))->first();
            $this->refundToWallet($invoice,false);

            $invoice->update([
                'refund_status' => 'admin',
                'is_refund_at' => now()->toDateTimeString(),
                'refunder_user_id' => auth()->user()->id
            ]);

            DB::commit();

            return api()->success();
        } catch (\Throwable $exception) {
            Log::error('Payments\Http\Controllers\Admin\InvoiceController@cancelOverPaidInvoice error => ' . $exception->getMessage());
            if(isset($invoice))
                Log::error('Payments\Http\Controllers\Admin\InvoiceController@cancelOverPaidInvoice error => ' . $invoice->id);
            return api()->error(null,null,$exception->getCode(),[
                'subject' => $exception->getMessage()
            ]);
        }
    }

    /**
     * Get pending package invoices
     * @group
     * Admin User > Payments > Invoices
     */
    public function pendingOrderInvoices()
    {
        $list = Invoice::query()
            ->orders()
            ->notPaid()
            ->notExpired()
            ->with('user')
            ->paginate();

        return api()->success(null, [
            'list' => InvoiceResource::collection($list),
            'pagination' => [
                'total' => $list->total(),
                'per_page' => $list->perPage()
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
        $list = Invoice::query()
            ->depositWallets()
            ->notPaid()
            ->notExpired()
            ->with('user')
            ->paginate();

        return api()->success(null, [
            'list' => InvoiceResource::collection($list),
            'pagination' => [
                'total' => $list->total(),
                'per_page' => $list->perPage()
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
        $invoice = Invoice::query()->with('transactions');

        if($request->has('transaction_id'))
            $invoice->when($request->has('transaction_id'), function(Builder $subQuery) use($request) {
                return $subQuery->where('transaction_id',$request->get('transaction_id'));
            });
        else
            $invoice->when($request->has('order_id'), function(Builder $subQuery) use($request) {
                return $subQuery->where('payable_id',$request->get('order_id'))->where('payable_type','=','Order');
            });

        $invoice = $invoice->first();

        return api()->success(null, InvoiceResource::make($invoice));
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
        $invoice = Invoice::query()->with('transactions');

        if($request->has('transaction_id'))
            $invoice->when($request->has('transaction_id'), function(Builder $subQuery) use($request) {
                return $subQuery->where('transaction_id',$request->get('transaction_id'));
            });
        else
            $invoice->when($request->has('order_id'), function(Builder $subQuery) use($request) {
                return $subQuery->where('payable_id',$request->get('order_id'))->where('payable_type','=','Order');
            });
        $invoice = $invoice->first();

        return api()->success(null,InvoiceTransactionResource::collection($invoice->transactions));
    }

    /**
     * @param $invoice
     * @param bool $user_wallet
     * @return float
     * @throws \Exception
     */
    private function refundToWallet(Invoice $invoice,$user_wallet = true)
    {
        $to_user = $user_wallet ? $invoice->user_id : 1;
        $wallet_service = app(WalletService::class);
        //Deposit Service
        $deposit_service = app(Deposit::class);
        $deposit_service->setUserId($to_user);
        $deposit_service->setAmount($invoice->getRefundableAmount());
        $deposit_service->setType('Refund overpaid invoice');
        $deposit_service->setDescription('Refund Invoice #' . $invoice->transaction_id);
        $deposit_service->setWalletName(WalletNames::DEPOSIT);

        //Deposit transaction
        /**
         * @var $deposit Deposit
         */
        $deposit = $wallet_service->deposit($deposit_service);

        //Deposit check
        if (!is_string($deposit->getTransactionId()))
            throw new \Exception('Refund error',400);

        return $invoice->getRefundableAmount();
    }

}
