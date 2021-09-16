<?php


namespace Giftcode\Repository;


use Exception;
use Giftcode\Jobs\UrgentEmailJob;
use Giftcode\Mail\User\GiftcodeCanceledEmail;
use Giftcode\Mail\User\GiftcodeCreatedEmail;
use Giftcode\Models\Giftcode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GiftcodeRepository
{
    private $model;
    private $wallet_repository;

    public function __construct(Giftcode $giftcode,WalletRepository $wallet_repository)
    {
        $this->model = $giftcode;
        $this->wallet_repository = $wallet_repository;
    }

    public function create(Request $request)
    {
        try {
            DB::beginTransaction();
            //All stuff fixed in GiftcodeObserver

            $giftcode = $this->model->query()->create([
                'package_id' => $request->get('package_id'),
                'user_id' => $request->get('user_id')
            ]);
            /**
             * Start User wallet process
             */
            //Check User Balance
            if($this->wallet_repository->checkUserBalance() < $giftcode->total_cost_in_usd)
                throw new \Exception(trans('giftcode.validation.inefficient-account-balance',['amount' => (float)$giftcode->total_cost_in_usd ]),406);
            //Withdraw Balance
            $finalTransaction = $this->wallet_repository->withdrawUserWallet($giftcode);

            //Wallet transaction failed [Server error]
            if(!is_string($finalTransaction->getTransactionId()))
                throw new \Exception(trans('giftcode.validation.wallet-withdrawal-error'),500);
            /**
             * End User wallet process
             */

            UrgentEmailJob::dispatch(new GiftcodeCreatedEmail($giftcode->creator, $giftcode), $giftcode->creator->email);

            DB::commit();

            return $giftcode;
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function getById($id)
    {
        return $this->model->query()->find($id);
    }

    public function getByUuid($uuid)
    {
        return $this->model->query()->where('uuid',$uuid)->first();
    }

    public function getByCode($code)
    {
        return $this->model->query()->where('code',$code)->first();
    }

    public function cancel(Request $request)
    {
        try {
            DB::beginTransaction();
            $giftcode = $this->getByUuid($request->get('id'));

            if($giftcode->is_canceled == true)
                throw new \Exception(trans('giftcode-is-canceled-and-user-cant-cancel'),406);


            if(!empty($giftcode->expiration_date) AND $giftcode->expiration_date->isPast())
                throw new Exception(trans('giftcode.responses.giftcode-is-expired-and-user-cant-cancel'),406);

            $giftcode->update([
                'is_canceled' => true
            ]);

            /**
             * Refund Giftcode total paid - cancelation fee
             */
            //Refund giftcode pay fee
            $finalTransaction = $this->wallet_repository->depositUserWallet($giftcode,'Cancel Giftcode #' . $giftcode->uuid);

            //Wallet transaction failed [Server error]
            if(!is_string($finalTransaction->getTransactionId()))
                throw new \Exception(trans('giftcode.validation.wallet-withdrawal-error'),500);
            /**
             * End refund
             */
            UrgentEmailJob::dispatch(new GiftcodeCanceledEmail($giftcode->creator,$giftcode),$giftcode->creator->email);


            DB::commit();
            return $giftcode->fresh();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function redeem(Request $request)
    {
        try {
            DB::beginTransaction();
            $giftcode = $this->getByUuid($request->get('id'));

            if($giftcode->is_canceled === true)
                throw new \Exception(trans('giftcode.responses.giftcode-is-canceled-and-user-cant-redeem'),406);


            if(!empty($giftcode->redeem_user_id))
                throw new \Exception(trans('giftcode.responses.giftcode-is-used-and-user-cant-redeem'),406);


            if(!empty($giftcode->expiration_date) AND $giftcode->expiration_date->isPast())
                throw new \Exception(trans('giftcode.responses.giftcode-is-expired-and-user-cant-redeem'),406);

            $giftcode->update([
                'redeem_user_id' => auth()->user()->id,
                'redeem_date' => now()->toDateTimeString(),
                'order_id' => $request->get('order_id')
            ]);
            DB::commit();
            return $giftcode->fresh();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;

        }
    }

    public function getGiftcodeServiceById($id)
    {
        $giftcode = $this->model->query()->where('id',$id)->first();
        if($giftcode)
            return $this->getGiftcodeService($giftcode);

        return null;
    }

    public function getGiftcodeServiceByUuid($uuid)
    {
        $giftcode = $this->model->query()->where('uuid',$uuid)->first();
        if($giftcode)
            return $this->getGiftcodeService($giftcode);

        return null;
    }

    public function getGiftcodeService($giftcode)
    {
        $giftcode_service = new \Giftcode\Services\Grpc\Giftcode();
        if($giftcode instanceof Giftcode AND !empty($giftcode->id))
            return $giftcode->getGiftcodeService();

        return $giftcode_service;
    }

    public function getUserCreatedGiftcodesCount($user_id)
    {
        return $this->model->query()->where('user_id',$user_id)->count();
    }

    public function getUserExpiredGiftcodesCount($user_id)
    {
        return $this->model->query()->where('user_id',$user_id)->where('expiration_date', '>', now()->toDateTimeString())->count();
    }

    public function getUserCanceledGiftcodesCount($user_id)
    {
        return $this->model->query()->where('user_id', $user_id)->where('is_canceled','!=',0)->count();
    }

    public function getUserRedeemedGiftcodesCount($user_id)
    {
        return $this->model->query()->where('redeem_user_id',$user_id)->where('is_canceled','!=',0)->count();
    }


}
