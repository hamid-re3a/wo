<?php


namespace Wallets\Services;

use Bavix\Wallet\Interfaces\WalletFloat;
use Illuminate\Support\Str;
use User\Models\User;
use Wallets\Models\Transaction;

class BankService
{
    /**
     * @var $owner User
     */
    private $owner;

    public function __construct(WalletFloat $owner)
    {
        $this->owner = $owner;
    }

    public function getWallet($wallet_name)
    {

        $slug = Str::slug($wallet_name);
        $wallet = $this->owner->getWallet($slug);
        if (!$wallet) {
            $wallet = $this->owner->createWallet([
                'name' => $wallet_name,
                'slug' => $slug
            ]);
        }

        return $wallet;

    }

    public function getAllWallets()
    {
        return $this->owner->wallets()->orderByDesc('id')->get();
    }

    public function deposit($wallet_name, $amount, $description = null, $confirmed = true, $type = 'Deposit', $sub_type = null)
    {
        /**@var $transaction Transaction */
        $balance = $this->getBalance($wallet_name);
        $data = [
            'wallet_before_balance' => $balance,
            'wallet_after_balance' => (double)$balance + (double)$amount,
            'type' => $type,
            'sub_type' => $sub_type
        ];
        $transaction = $this->getWallet($wallet_name)->depositFloat($amount, $this->createMeta($description), $confirmed);
        $transaction->syncMetaData($data);

        return $transaction;
    }

    public function withdraw($wallet_name, $amount, $description = null, $type = 'Withdraw', $sub_type = null, $confirmed = true,$to_admin_wallet = true)
    {
        if (!$this->getWallet($wallet_name)->holder->canWithdraw($amount))
            throw new \Exception();

        $balance = $this->getBalance($wallet_name);
        $data = [
            'wallet_before_balance' => $balance,
            'wallet_after_balance' => $balance - $amount,
            'type' => $type,
            'sub_type' => $sub_type
        ];
        $transaction = $this->getWallet($wallet_name)->withdrawFloat($amount, $this->createMeta($description), $confirmed);
        if ($this->owner->id != 1 AND $to_admin_wallet)
            $this->toAdminDepositWallet($transaction, $amount, $description, $type);
        $transaction->syncMetaData($data);
        return $transaction;

    }

    public function forceWithdraw($wallet_name, $amount, $description = null)
    {
        return $this->getWallet($wallet_name)->forceWithdrawFloat($amount, $this->createMeta($description));
    }

    public function transfer($from_wallet, $to_wallet, $amount, $description = null)
    {
        if (!$from_wallet instanceof WalletFloat)
            $from_wallet = $this->getWallet($from_wallet);

        if (!$to_wallet instanceof WalletFloat)
            $to_wallet = $this->getWallet($to_wallet);

        $withdrawMeta = [
            'wallet_before_balance' => $from_wallet->balanceFloat,
            'wallet_after_balance' => $from_wallet->balanceFloat - $amount,
            'type' => 'Funds transferred'
        ];

        $depositMeta = [
            'wallet_before_balance' => $to_wallet->balanceFloat,
            'wallet_after_balance' => $to_wallet->balanceFloat + $amount,
            'type' => 'Funds received'
        ];

        $transfer = $from_wallet->transferFloat($to_wallet, $amount, $this->createMeta($description));
        $transfer->withdraw->syncMetaData($withdrawMeta);
        $transfer->deposit->syncMetaData($depositMeta);
        return $transfer;
    }

    public function getBalance($wallet_name)
    {
        $wallet = $this->getWallet($wallet_name);
        $wallet->refreshBalance();
        return $wallet->balanceFloat;
    }

    public function getTransaction($uuid)
    {
        return $this->owner->transactions()->whereUuid($uuid)->first();
    }

    public function getTransactions($wallet_name)
    {
        $transactionQuery = $this->owner->transactions()->whereHas('wallet', function ($query) use ($wallet_name) {
            $query->where('name', $wallet_name);
        });

        if (request()->has('transaction_id'))
            $transactionQuery->where('uuid', request()->get('transaction_id'));

        if (request()->has('type'))
            $transactionQuery->where(function($query){
                $query->whereHas('metaData', function ($query) {
                    $query->where('wallet_transaction_types.name', '=', trim(request()->get('type')));
                })->orWhere('type','=',request()->get('type'));
            });

        if (request()->has('amount'))
            $transactionQuery->whereAmount(request()->get('amount'));

        if (request()->has('amount_from'))
            $transactionQuery->whereRaw('ABS(amount) >= ?', [request()->get('amount_from')]);
        if (request()->has('amount_to'))
            $transactionQuery->whereRaw('ABS(amount) <= ?', [request()->get('amount_to')]);


        if (request()->has('from_date'))
            $transactionQuery->whereDate('created_at', '>=', request()->get('from_date'));
        else if (request()->has('created_at'))
            $transactionQuery->whereDate('created_at', '<=', request()->get('to_date'));

        if (request()->has('description')) {
            $words = explode(' ', request()->get('description'));
            foreach ($words AS $word)
                $transactionQuery->where('meta->description', 'LIKE', "%{$word}%");
        }


        return $transactionQuery->orderBy('id','desc');
    }

    public function getTransfers($wallet_name)
    {
        return $this->getWallet($wallet_name)->transfers()->where('to_id', '!=', 1);
    }

    public function toAdminDepositWallet($transaction, $amount, $description, $type)
    {
        $this->owner = User::query()->find(1);
        $admin_wallet = $this->getWallet(WALLET_NAME_DEPOSIT_WALLET);


        //Prepare description
        $description = $this->createMeta($description);
        $description['user_transaction_id'] = $transaction->id;
        $data = [
            'wallet_before_balance' => $admin_wallet->balanceFloat,
            'wallet_after_balance' => $admin_wallet->balanceFloat + $amount,
            'type' => $type
        ];

        $charity_amount = 0;
        if($type == 'Package purchased') {
            $charity_wallet = $this->getWallet(WALLET_NAME_CHARITY_WALLET);
            $charity_amount = calculateCharity($amount);
            if($charity_amount > 0)
                $charity_wallet->depositFloat($charity_amount,$this->createMeta($description));
        }

        $transaction = $admin_wallet->depositFloat(($amount - $charity_amount), $this->createMeta($description));
        $transaction->syncMetaData($data);

    }

    private function createMeta($meta)
    {
        $meta = ($meta == 'b:0;' || @unserialize($meta) !== false) ? unserialize($meta) : $meta;
        if (is_array($meta))
            return $meta;

        return [
            'description' => $meta
        ];
    }


}
