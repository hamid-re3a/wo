<?php


namespace Giftcode\Services;


use Giftcode\Repository\GiftcodeRepository;
use Illuminate\Http\Request;
use User\Services\User;

class GiftcodeService
{
    public $giftcode_repository;

    public function __construct(GiftcodeRepository $giftcode_repository)
    {
        $this->giftcode_repository = $giftcode_repository;
    }

    public function getGiftcode(Giftcode $giftcode)
    {
        $giftcode_model = $this->giftcode_repository->getByUuid($giftcode->getUuid());
        if($giftcode_model)
            return $this->giftcode_repository->getGiftcodeService($giftcode_model);

        return new Giftcode();
    }

    public function redeemGiftcode(Giftcode $giftcode,Package $package,User $user)
    {
        try {
            $giftcode_model = $this->giftcode_repository->getById($giftcode->getId());
            if(!$giftcode_model)
                return new Giftcode();
            if($giftcode_model->package_id == $package->getId()) {
                $request = new Request();
                $request->merge([
                    'id' => $giftcode->getUuid(),
                    'user' => \User\Models\User::query()->find($user->getId())
                ]);
                $giftcode_model = $this->giftcode_repository->redeem($request);
            }
            return $this->giftcode_repository->getGiftcodeServiceByUuid($giftcode->getUuid());

        } catch (\Throwable $exception) {
            return new Giftcode();
        }
    }

    public function getUserCreatedGiftcodesCount(User $user)
    {
        $user_id = $user->getId();
        return $this->giftcode_repository->getUserCreatedGiftcodesCount($user_id);
    }

    public function getUserExpiredGiftcodesCount(User $user)
    {
        $user_id = $user->getId();
        return $this->giftcode_repository->getUserExpiredGiftcodesCount($user_id);
    }

    public function getUserCanceledGiftcodesCount(User $user)
    {
        $user_id = $user->getId();
        return $this->giftcode_repository->getUserCanceledGiftcodesCount($user_id);
    }

    public function getUserRedeemedGiftcodesCount(User $user)
    {
        $user_id = $user->getId();
        return $this->giftcode_repository->getUserRedeemedGiftcodesCount($user_id);
    }



}
