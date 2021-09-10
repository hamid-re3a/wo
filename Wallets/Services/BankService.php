<?php


namespace Wallets\Services;

use Bavix\Wallet\Interfaces\WalletFloat;
use Illuminate\Support\Str;
use User\Models\User;
use Wallets\Models\TransactionType;

class BankService
{
    private $owner;

    public function __construct(WalletFloat $owner)
    {
        $this->owner = $owner;
    }

    public function getWallet($wallet_name)
    {
        $slug = Str::slug($wallet_name);

        if (!$this->owner->hasWallet($slug))
            $this->owner->createWallet([
                'name' => $wallet_name,
                'slug' => $slug
            ]);

        return $this->owner->getWallet($slug);
    }

    public function getAllWallets()
    {
        return $this->owner->wallets()->get();
    }

    public function deposit($wallet_name, $amount, $description = null, $confirmed = true, $type = 'Deposit', $sub_type = null)
    {
        $data = [
            'wallet_before_balance' => $this->getBalance($wallet_name),
            'wallet_after_balance' => $this->getBalance($wallet_name) + $amount,
            'type' => $type,
            'sub_type' => $sub_type
        ];

        $transaction = $this->getWallet($wallet_name)->depositFloat($amount, $this->createMeta($description), $confirmed);
        $transaction->syncMetaData($data);

        return $transaction;
    }

    public function withdraw($wallet_name, $amount, $description = null, $type = 'Withdraw', $sub_type = null)
    {

        if (!$this->getWallet($wallet_name)->holder->canWithdraw($amount))
            throw new \Exception();

        $data = [
            'wallet_before_balance' => $this->getBalance($wallet_name),
            'wallet_after_balance' => $this->getBalance($wallet_name) - $amount,
            'type' => $type,
            'sub_type' => $sub_type
        ];
        $transaction = $this->getWallet($wallet_name)->withdrawFloat($amount, $this->createMeta($description));
        $this->toAdminDepositWallet($transaction,$amount,$description,$type);
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
            'type' => 'Transfer'
        ];

        $depositMeta = [
            'wallet_before_balance' => $to_wallet->balanceFloat,
            'wallet_after_balance' => $to_wallet->balanceFloat + $amount,
            'type' => 'Transfer'
        ];

        $transfer = $from_wallet->transferFloat($to_wallet, $amount, $this->createMeta($description));
        $transfer->withdraw->syncMetaData($withdrawMeta);
        $transfer->deposit->syncMetaData($depositMeta);
        return $transfer;
    }

    public function getBalance($wallet_name)
    {
        $wallet = $this->getWallet($wallet_name);
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
            $transactionQuery->whereType(request()->get('type'));

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


        return $transactionQuery->orderBy('created_at','DESC');
    }

    public function getTransfers($wallet_name)
    {
        return $this->getWallet($wallet_name)->transfers()->where('to_id','!=',1);
    }

    public function toAdminDepositWallet($transaction,$amount,$description,$type)
    {
        $this->owner = User::query()->find(1);
        $admin_wallet = $this->getWallet(Str::slug('Deposit Wallet'));

        //Prepare description
        $description = $this->createMeta($description);
        $description['user_transaction_id'] = $transaction->id;
        $data = [
            'wallet_before_balance' => $admin_wallet->balanceFloat,
            'wallet_after_balance' => $admin_wallet->balanceFloat + $amount,
            'type' => $type
        ];
        $transaction = $admin_wallet->depositFloat($amount, $description);
        $transaction->syncMetaData($data);

    }

    private function createMeta($meta)
    {
        if (is_array($meta))
            return $meta;

        return [
            'description' => $meta
        ];
    }


}
