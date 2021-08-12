<?php


namespace Wallets\Services;

use Bavix\Wallet\Interfaces\WalletFloat;
use Illuminate\Support\Str;

class BankService
{
    private $owner;

    public function __construct(WalletFloat $owner)
    {
        $this->owner = $owner->load(['wallets','transactions']);
    }

    public function getWallet($wallet_name)
    {
        $slug = Str::slug($wallet_name);

        if (!property_exists($this, $wallet_name)) {
            if (!$this->owner->hasWallet($slug))
                $this->owner->createWallet([
                    'name' => $wallet_name,
                    'slug' => $slug
                ]);

            $this->$slug = $this->owner->getWallet($slug);
        }
        return $this->$slug;
    }

    public function getAllWallets()
    {
        return $this->owner->wallets()->get();
    }

    public function deposit($wallet_name, $amount, $meta = null, $confirmed = true)
    {
        if (is_string($meta))
            $meta = ['description' => $meta];

        return $this->getWallet($wallet_name)->depositFloat($amount, $meta, $confirmed);
    }

    public function withdraw($wallet_name,$amount, $meta = null)
    {
        if (is_string($meta))
            $meta = ['description' => $meta];
        return $this->getWallet($wallet_name)->withdrawFloat($amount, $meta);
    }

    public function forceWithdraw($wallet_name,$amount, $meta = null)
    {
        if (is_string($meta))
            $meta = ['description' => $meta];
        return $this->getWallet($wallet_name)->forceWithdrawFloat($amount, $meta);
    }

    public function transfer($from_wallet , $to_wallet, $amount, $meta = null)
    {
        if(!$from_wallet instanceof WalletFloat)
            $from_wallet = $this->getWallet($from_wallet);

        if(!$to_wallet instanceof WalletFloat)
            $to_wallet = $this->getWallet($to_wallet);

        if (is_string($meta))
            $meta = ['description' => $meta];

        return $from_wallet->transferFloat($to_wallet, $amount, $meta);
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
        $transactionQuery = $this->owner->transactions()->whereHas('wallet', function($query) use($wallet_name){
            $query->where('name', $wallet_name);
        });

        if(request()->has('transaction_id'))
            $transactionQuery->where('uuid', request()->get('transaction_id'));

        if(request()->has('type'))
            $transactionQuery->whereType(request()->get('type'));

        if(request()->has('amount'))
            $transactionQuery->whereAmount(request()->get('amount'));

        if(request()->has('amount_from'))
            $transactionQuery->whereRaw('ABS(amount) >= ?' , [request()->get('amount_from')]);
        if (request()->has('amount_to'))
            $transactionQuery->whereRaw('ABS(amount) <= ?' , [request()->get('amount_to')]);


        if(request()->has('from_date'))
            $transactionQuery->whereDate('created_at', '>=' , request()->get('from_date'));
        else if (request()->has('created_at'))
            $transactionQuery->whereDate('created_at', '<=' , request()->get('to_date'));

        if(request()->has('description')) {
            $words = explode(' ', request()->get('description'));
            foreach($words AS $word)
                $transactionQuery->where('meta->description', 'LIKE', "%{$word}%");
        }

        return $transactionQuery;
    }

    public function getTransfers($wallet_name)
    {
        return $this->getWallet($wallet_name)->transfers();
    }


}
