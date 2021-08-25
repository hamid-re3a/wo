<?php

namespace Payments\Models;

use Illuminate\Database\Eloquent\Model;

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

}
