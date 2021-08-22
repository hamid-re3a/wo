<?php


namespace Giftcode\Http\Controllers;

use App\Http\Controllers\Controller;
use Giftcode\Http\Resources\GiftcodeResource;
use Giftcode\Models\Giftcode;

class TestController extends Controller
{
    public function test()
    {
        return api()->success(null,GiftcodeResource::make(Giftcode::create([
            'package_id' => 3,
        ])));
    }
}
