<?php

namespace Payments\Http\Controllers\Front;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use MLM\Services\Grpc\Acknowledge;
use Orders\Http\Controllers\Front\OrderController;
use Orders\Services\Grpc\Id;
use Orders\Services\Grpc\Order;
use Orders\Services\OrderService;
use Payments\Http\Requests\Invoice\CancelInvoiceRequest;
use Payments\Http\Requests\Invoice\ShowInvoiceRequest;
use Payments\Http\Requests\Invoice\ShowOrderTransactionsRequest;
use Payments\Http\Resources\InvoiceResource;
use Payments\Http\Resources\InvoiceTransactionResource;
use Payments\Models\Invoice;
use Payments\Models\InvoiceTransaction;
use Payments\Services\InvoiceService;
use Payments\Services\Processors\PaymentProcessor;
use User\Models\User;

class InvoiceController extends Controller
{
    private $invoice_service;

    public function __construct(InvoiceService $invoice_service)
    {
        $this->invoice_service = $invoice_service;
    }

    /**
     * Get user pending package invoice
     * @group
     * Public User > Payments > Invoices
     */
    public function pendingOrderInvoice()
    {
        try {
            DB::beginTransaction();

            /**@var $user User*/
            $user = auth()->user();
            $now = now()->toDateTime();

            $paid_invoice = $user->orderInvoices()->where('is_paid',1)->count();
            if($paid_invoice) {
                DB::rollBack();
                return api()->success(null,[
                    'status' => 'confirmed'
                ]);
            }


            //check for pending invoice
            $pending_invoice = $user->orderInvoices()->where('is_paid',0)->where('expiration_time','>',$now)->where('payment_type','=','purchase')->first();

            if(!$pending_invoice) {
                //Check if user has expired invoice
                $expired_invoice = $user->orderInvoices()->where('expiration_time','>', $now)->where('payment_type','=','purchase')->first();
                if($expired_invoice) {
                    $this->createNewInvoice($user,$expired_invoice);
                    $pending_invoice = $user->orderInvoices()->where('is_paid',0)->where('expiration_time','>',$now)->where('payment_type','=','purchase')->first();
                } else {
                    DB::rollBack();
                    return api()->error(null,null,404);
                }
            }

            DB::commit();
            return api()->success(null, InvoiceResource::make($pending_invoice));
        } catch (\Throwable $exception) {
            DB::rollBack();
            return api()->error(null,[
                'subject' => trans('payment.responses.something-went-wrong')
            ]);
        }
    }

    /**
     * Get user pending wallet invoice
     * @group
     * Public User > Payments > Invoices
     */
    public function pendingWalletInvoice()
    {
        $pending_invoice = auth()->user()->invoices()->where('payable_type','DepositWallet')->where('is_paid',0)->where('expiration_time','>',now()->toDateTimeString())->first();

        if(!$pending_invoice) {
            $paid_invoice = auth()->user()->invoices()->where('payable_type','DepositWallet')->where('is_paid',1)->count();
            if($paid_invoice)
                return api()->success(null,[
                    'status' => 'confirmed'
                ]);

            return api()->error(null,null,404);
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
        $invoices = Invoice::query()->where('user_id',request()->header('X-user-id'))->simplePaginate();
        return api()->success(null,InvoiceResource::collection($invoices)->response()->getData());
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
        $invoice = Invoice::query()->where('transaction_id',$request->get('transaction_id'))->where('user_id',$request->header('X-user-id'))->first();
        if(!$invoice)
            return api()->error(null,null,404);

        return api()->success(null,InvoiceResource::make($invoice));
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
        $invoice = Invoice::query()->where('transaction_id',$request->get('transaction_id'))->where('user_id',$request->header('X-user-id'))->with('transactions')->first();
        return api()->success(null,InvoiceTransactionResource::collection($invoice->transactions));
    }

    /**
     * Cancel invoice by user
     * @group
     * Public User > Payments > Invoices
     * @param ShowOrderTransactionsRequest $request
     * @return JsonResponse
     */
    public function cancelInvoice(CancelInvoiceRequest $request)
    {
        $this->invoice_service->cancelInvoice($request->transaction_id);
        return api()->success("invoice has been canceled",null);
    }

    private function createNewInvoice($user,$expired_invoice)
    {
        /**
         * @var $expired_invoice Invoice
         * @var $expired_order Order
         * @var $user User
         */
        //Retrieve Order
        $order_service = app(OrderService::class);
        $order_id = new Id();
        $order_id->setId($expired_invoice->payable_id);
        $order_object = $order_service->OrderById($order_id);


        //Prepare invoice object
        $invoice_request = new \Payments\Services\Grpc\Invoice();
        $invoice_request->setPayableId((int)$order_object->getId());
        $invoice_request->setPayableType('Order');
        $invoice_request->setPfAmount((double)$order_object->getTotalCostInPf());
        $invoice_request->setPaymentDriver($order_object->getPaymentDriver());
        $invoice_request->setPaymentType($order_object->getPaymentType());
        $invoice_request->setPaymentCurrency($order_object->getPaymentCurrency());
        $invoice_request->setUser($user->getUserService());
        $invoice_request->setUserId((int)auth()->user()->id);

        $payment_processor = app(PaymentProcessor::class);
        list($payment_flag, $payment_response) = $payment_processor->pay($invoice_request);


        if (!$payment_flag)
            throw new \Exception($payment_response, 406);

        return $payment_response;

    }
}
