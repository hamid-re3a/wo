<?php


namespace Giftcode\Http\Controllers;


use App\Http\Controllers\Controller;
use Giftcode\Http\Requests\User\CreateGiftcodeRequest;
use Giftcode\Http\Resources\GiftcodeResource;
use Giftcode\Models\Giftcode;
use Wallets\Services\Deposit;
use Wallets\Services\Transaction;
use Wallets\Services\Wallet;
use Wallets\Services\WalletService;
use Wallets\Services\Withdraw;

class GiftcodeController extends Controller
{

    /**
     * Giftcodes list
     * @group Public User > Giftcode
     */
    public function index()
    {
        $giftcodes = request()->user->giftcodes()->simplePaginate();
        return api()->success(null,GiftcodeResource::collection($giftcodes)->response()->getData());
    }

    /**
     * Create giftcode
     * @group Public User > Giftcode
     * @param CreateGiftcodeRequest $request
     */
    public function store(CreateGiftcodeRequest $request)
    {


    }

    /**
     * Get giftcode detail
     * @group Public User > Giftcode
     */
    public function show()
    {

    }

    /**
     * Cancel Giftcode
     * @group Public User > Giftcode
     */
    public function cancel()
    {

    }


}
