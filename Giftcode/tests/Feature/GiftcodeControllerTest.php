<?php


namespace Giftcode\tests\Feature;


use Giftcode\Mail\User\GiftcodeCanceledEmail;
use Giftcode\Models\Giftcode;
use Giftcode\tests\GiftcodeTest;
use Illuminate\Support\Facades\Mail;

class GiftcodeControllerTest extends GiftcodeTest
{

    /**
     * @test
     */
    public function create_new_giftcode()
    {
        Mail::fake();
        $response = $this->createGiftCode();
        $response->assertOk();
        $response->assertJsonStructure($this->getGiftcodeJsonStructure());
    }

    /**
     * @test
     */
    public function cancel_gift_code()
    {
        Mail::fake();
        $response = $this->createGiftCode();
        $response->assertOk();
        $giftcode = Giftcode::query()->first();
        $response = $this->postJson(route('giftcodes.customer.cancel'), [
            'id' => $giftcode->uuid
        ]);

        $response->assertOk();
        Mail::assertSent(GiftcodeCanceledEmail::class);
    }


}
