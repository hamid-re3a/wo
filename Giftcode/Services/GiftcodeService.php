<?php


namespace Giftcode\Services;


use Giftcode\Repository\GiftcodeRepository;
use Illuminate\Http\Request;
use Packages\Services\Package;
use User\Services\User;

class GiftcodeService
{
    public $giftcode_repository;

    public function __construct(GiftcodeRepository $giftcode_repository)
    {
        $this->giftcode_repository = $giftcode_repository;
    }

    public function getGiftcodeById(Id $id)
    {
        $giftcode = $this->giftcode_repository->getById($id->getId());
        if($giftcode)
            return $this->giftcode_repository->getGiftcodeService($giftcode);

        return new Giftcode();
    }

    public function getGiftcodeByUuid(Uuid $uuid)
    {
        $giftcode = $this->giftcode_repository->getByUuid($uuid->getUuid());
        if($giftcode)
            return $this->giftcode_repository->getGiftcodeService($giftcode);

        return new Giftcode();
    }

    public function redeemGiftcode(Giftcode $giftcode,Package $package,User $user)
    {
        try {
            $giftcode_model = $this->giftcode_repository->getById($giftcode->getId());
            if(!$giftcode_model)
                return new Giftcode();

            if($giftcode_model->package_id != $package->getId()) {
                $request = new Request();
                $request->uuid = $giftcode->getUuid();
                $request->user = \User\Models\User::query()->find($user->getId());
                $this->giftcode_repository->redeem($request);
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

    public function getUserCanceledGiftcodesCount(User $user)
    {
        $user_id = $user->getId();
        return $this->giftcode_repository->getUserCreatedGiftcodesCount($user_id);
    }

    public function getUserRedeemedGiftcodesCount(User $user)
    {
        $user_id = $user->getId();
        return $this->giftcode_repository->getUserCreatedGiftcodesCount($user_id);
    }



}
