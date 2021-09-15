<?php


namespace Wallets\tests\Feature;

use Giftcode\Models\Giftcode;
use Giftcode\Services\GiftcodeService;
use Giftcode\tests\GiftcodeTest;
use Illuminate\Support\Facades\Mail;
use User\Models\User;

class GiftcodeServiceTest extends GiftcodeTest
{
    /**
     * @test
     */
    public function getGiftcodeByCode()
    {

        Mail::fake();
        $response = $this->createGiftCode();
        $response->assertOk();
        $giftcode_model = Giftcode::query()->first();
        $giftcode_object = $giftcode_model->getGiftcodeService();
        $giftcode_service = app(GiftcodeService::class);
        $giftcode_object = $giftcode_service->getGiftcodeByCode($giftcode_object);
        $this->assertIsInt($giftcode_object->getId());

    }
    /**
     * @test
     */
    public function getGiftcodeByUuid()
    {

        Mail::fake();
        $response = $this->createGiftCode();
        $response->assertOk();
        $giftcode_model = Giftcode::query()->first();
        $giftcode_object = $giftcode_model->getGiftcodeService();
        $giftcode_service = app(GiftcodeService::class);
        $giftcode_object = $giftcode_service->getGiftcodeByUuid($giftcode_object);
        $this->assertIsInt($giftcode_object->getId());

    }

    /**
     * @test
     */
    public function redeemGiftcode()
    {
        Mail::fake();
        $response = $this->createGiftCode();
        $response->assertOk();
        $giftcode_model = Giftcode::query()->first();
        $giftcode_object = $giftcode_model->getGiftcodeService();
        $giftcode_object->setOrderId(1);
        $user = User::query()->first();
        $user_object = $user->getUserService();
        $giftcode_service = app(GiftcodeService::class);
        $response = $giftcode_service->redeemGiftcode($giftcode_object,$user_object);
        $this->assertIsInt($response->getId());
        $this->assertIsInt($response->getRedeemUserId());
        $this->assertEquals($response->getRedeemUserId(),$user_object->getId());

    }

    /**
     * @test
     */
    public function getUserCreatedGiftcodesCount()
    {
        Mail::fake();
        $response = $this->createGiftCode();
        $response->assertOk();

        $user = User::query()->first();
        $user_object = $user->getUserService();

        $giftcode_service = app(GiftcodeService::class);
        $response = $giftcode_service->getUserCreatedGiftcodesCount($user_object);
        $this->assertIsInt($response);
        $this->assertEquals($response,1);
    }

    /**
     * @test
     */
    public function getUserExpiredGiftcodesCount()
    {
        Mail::fake();
        $response = $this->createGiftCode();
        $response->assertOk();

        $user = User::query()->first();
        $user_object = $user->getUserService();

        $giftcode_service = app(GiftcodeService::class);
        $response = $giftcode_service->getUserExpiredGiftcodesCount($user_object);
        $this->assertIsInt($response);
        $this->assertEquals($response,0);
    }

    /**
     * @test
     */
    public function getUserCanceledGiftcodesCount()
    {
        Mail::fake();
        $response = $this->createGiftCode();
        $response->assertOk();

        $user = User::query()->first();
        $user_object = $user->getUserService();

        $giftcode_service = app(GiftcodeService::class);
        $response = $giftcode_service->getUserCanceledGiftcodesCount($user_object);
        $this->assertIsInt($response);
        $this->assertEquals($response,0);
    }

    /**
     * @test
     */
    public function getUserRedeemedGiftcodesCount()
    {
        Mail::fake();
        $response = $this->createGiftCode();
        $response->assertOk();

        $user = User::query()->first();
        $user_object = $user->getUserService();

        $giftcode_service = app(GiftcodeService::class);
        $response = $giftcode_service->getUserRedeemedGiftcodesCount($user_object);
        $this->assertIsInt($response);
        $this->assertEquals($response,0);
    }


}
