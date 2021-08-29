<?php

namespace Wallets\Models;

use function array_merge;
use function config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Transfer.
 *
 * @property string $status
 * @property int $discount
 * @property int $deposit_id
 * @property int $withdraw_id
 * @property string $from_type
 * @property int $from_id
 * @property string $to_type
 * @property int $to_id
 * @property string $uuid
 * @property int $fee
 * @property Transaction $deposit
 * @property Transaction $withdraw
 */
class Transfer extends \Bavix\Wallet\Models\Transfer
{

    public function deposit() : BelongsTo
    {
        return $this->belongsTo(config('wallet.transaction.model', \Bavix\Wallet\Models\Transaction::class), 'deposit_id');
    }

    public function withdraw() : BelongsTo
    {
        return $this->belongsTo(config('wallet.transaction.model', \Bavix\Wallet\Models\Transaction::class), 'withdraw_id');
    }
}
