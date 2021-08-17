<?php


namespace Giftcode_\Http\Controllers;

use App\Http\Controllers\Controller;
use Giftcode_\Models\Giftcode;

class TestController extends Controller
{
    public function test()
    {
        return giftcodeGetSetting('characters');
    }
}
