<?php

namespace Payments\Models;

use Illuminate\Database\Eloquent\Model;
use Payments\Services\PaymentTransaction;

/**
 * Payments\Models\Invoice
 *
 * @property int $id
 * @property int|null $invoice_id
 * @property string $hash
 * @property string $value
 * @property string $fee
 * @property string $status
 * @property string $destination
 * @property \Illuminate\Support\Carbon|null $received_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $full_status
 * @property-read \Payments\Models\Invoice|null $invoice
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceTransaction whereDestination($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceTransaction whereFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceTransaction whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceTransaction whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceTransaction whereReceivedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceTransaction whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceTransaction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceTransaction whereValue($value)
 * @mixin \Eloquent
 */
class InvoiceTransaction extends Model
{
    protected $fillable = [
        'invoice_id',
        'hash',
        'received_date',
        'value',
        'fee',
        'status',
        'destination'
    ];

    protected $casts = [
        'invoice_id' => 'integer',
        'received_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    protected $table = 'payment_invoice_transactions';

    public function invoice()
    {
        return $this->belongsTo(Invoice::class,'invoice_id','id');
    }

    /**
     * Methods
     */
    public function getService()
    {
        $service = app(PaymentTransaction::class);
        $service->setId($this->id);
        $service->setInvoiceId($this->attributes['invoice_id']);
        $service->setHash($this->attributes['hash']);
        $service->setReceivedDate($this->attributes['received_date']);
        $service->setValue($this->attributes['value']);
        $service->setFee($this->attributes['fee']);
        $service->setStatus($this->attributes['status']);
        $service->setDestination($this->attributes['destination']);

        return $service;
    }

}
