<?php


namespace Giftcode\Services;


use Giftcode\Repository\GiftcodeRepository;
use Giftcode\Services\Grpc\Giftcode;
use Illuminate\Http\Request;
use User\Services\Grpc\User;

class GiftcodeService
{
    public $giftcode_repository;

    public function __construct(GiftcodeRepository $giftcode_repository)
    {
        $this->giftcode_repository = $giftcode_repository;
    }

    public function getGiftcodeByCode(Giftcode $giftcode)
    {
        $giftcode_model = $this->giftcode_repository->getByCode($giftcode->getCode());
        if($giftcode_model)
            return $this->giftcode_repository->getGiftcodeService($giftcode_model);

        return new Giftcode();

    }

    public function getGiftcodeByUuid(Giftcode $giftcode)
    {
        $giftcode_model = $this->giftcode_repository->getByUuid($giftcode->getUuid());
        if($giftcode_model)
            return $this->giftcode_repository->getGiftcodeService($giftcode_model);

        return new Giftcode();
    }

    public function redeemGiftcode(Giftcode $giftcode,User $user) : Giftcode
    {
        try {
            $giftcode_model = $this->giftcode_repository->getById($giftcode->getId());
            if(!$giftcode_model)
                return new Giftcode();
                $request = new Request();
                $request->merge([
                    'id' => $giftcode->getUuid(),
                    'user' => \User\Models\User::query()->find($user->getId()),
                    'order_id' => $giftcode->getOrderId()
                ]);
                $this->giftcode_repository->redeem($giftcode_model,$user->getId(),$giftcode->getOrderId());
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
