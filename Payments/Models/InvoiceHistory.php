<?php

namespace Payments\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Payments\Models\InvoiceHistory
 *
 * @property int $id
 * @property int|null $order_id
 * @property int $amount
 * @property string|null $transaction_id
 * @property string|null $reference_id
 * @property mixed|null $payload
 * @property int $is_paid
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceHistory whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceHistory whereIsPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceHistory whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceHistory wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceHistory whereReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceHistory whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceHistory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class InvoiceHistory extends Model
{
    use HasFactory;
}
