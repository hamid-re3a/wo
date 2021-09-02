<?php


namespace Giftcode\Repository;


use Exception;
use Giftcode\Models\Giftcode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use User\Models\User;

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
            $giftcode = $this->model->query()->create($request->all());

            $request->user = User::query()->find(User::query()->find($request->get('user_id')));

            /**
             * Start User wallet process
             */
            //Check User Balance
            if($this->wallet_repository->checkUserBalance($request) < $giftcode->total_cost_in_usd)
                throw new \Exception(trans('giftcode.validation.inefficient-account-balance',['amount' => (float)$giftcode->total_cost_in_usd ]),406);

            //Withdraw Balance
            $finalTransaction = $this->wallet_repository->withdrawUserWallet($giftcode);

            //Wallet transaction failed [Server error]
            if(!$finalTransaction->getConfiremd())
                throw new \Exception(trans('giftcode.validation.wallet-withdrawal-error'),500);
            /**
             * End User wallet process
             */
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
            if(!$finalTransaction->getConfiremd())
                throw new \Exception(trans('giftcode.validation.wallet-withdrawal-error'),500);
            /**
             * End refund
             */


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
                'redeem_user_id' => request()->user->id,
                'redeem_date' => now()->toDateTimeString()
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
        $giftcode = $this->model->query()->where('id',$uuid)->first();
        if($giftcode)
            return $this->getGiftcodeService($giftcode);

        return null;
    }

    public function getGiftcodeService($giftcode)
    {
        $giftcode_service = new \Giftcode\Services\Giftcode();
        $giftcode_service->setId($giftcode->id);
        $giftcode_service->setUuid($giftcode->uuid);
        $giftcode_service->setUserId($giftcode->user_id);
        $giftcode_service->setPackageId($giftcode->package_id);
        $giftcode_service->setPackagesCostInUsd($giftcode->packages_cost_in_usd);
        $giftcode_service->setRegistrationFeeInUsd($giftcode->registration_fee_in_usd);
        $giftcode_service->setTotalCostInUsd($giftcode->total_cost_in_usd);
        $giftcode_service->setCode($giftcode->code);
        $giftcode_service->setExpirationDate($giftcode->expiration_date);
        $giftcode_service->setIsCanceled($giftcode->is_canceled);
        $giftcode_service->setCreatedAt($giftcode->created_at);
        $giftcode_service->setUpdatedAt($giftcode->updated_at);
        $giftcode_service->setCreator($giftcode->creator->getUserService());
        if(!empty($giftcode->redeem_user_id)) {
            $giftcode_service->setRedeemer($giftcode->redeemer->getUserService());
            $giftcode_service->setRedeemUserId($giftcode->redeem_user_id);
            $giftcode_service->setRedeemDate($giftcode->redeem_date);
        }

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
        return $this->model->query()->where('user_id', $user_id)->whereNotNull('is_canceled')->count();
    }

    public function getUserRedeemedGiftcodesCount($user_id)
    {
        return $this->model->query()->where('redeem_user_id',$user_id)->whereNotNull('is_canceled')->count();
    }


}
