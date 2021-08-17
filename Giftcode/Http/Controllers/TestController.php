<?php


namespace Giftcode\Http\Controllers;

use App\Http\Controllers\Controller;
use Giftcode\Models\Giftcode;

class TestController extends Controller
{
    public function test()
    {
        return giftcodeGetSetting('characters');
    }
}
