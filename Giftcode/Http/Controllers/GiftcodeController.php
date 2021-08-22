<?php


namespace Giftcode\Http\Controllers;


use App\Http\Controllers\Controller;
use Giftcode\Http\Requests\User\CreateGiftcodeRequest;
use Giftcode\Http\Resources\GiftcodeResource;
use Giftcode\Jobs\UpdatePackages;
use Giftcode\Models\Giftcode;
use Illuminate\Support\Facades\DB;
use Wallets\Services\Transaction;
use Wallets\Services\WalletService;
use Wallets\Services\Withdraw;

class GiftcodeController extends Controller
{

    private $walletService;
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateGiftcodeRequest $request)
    {
        UpdatePackages::dispatchSync();
        try {
            DB::beginTransaction();

            //All stuff fixed in GiftcodeObserver
            $giftcode = $request->user->giftcodes()->create([
                'package_id' => $request->get('package_id')
            ]);


            /**
             * Start User wallet process
             */
            $this->walletService = app(WalletService::class);

            //Check User Balance
            if($this->checkUserBalance($request) < $giftcode->total_cost_in_usd)
                throw new \Exception(trans('giftcode.validation.inefficient-account-balance',['amount' => (float)$giftcode->total_cost_in_usd ]),422);

            //Withdraw Balance
            $finalTransaction = $this->withdrawUserWallet($giftcode);

            //Wallet transaction failed [Server error]
            if(!$finalTransaction->getConfiremd())
                throw new \Exception(trans('giftcode.validation.wallet-withdrawal-error'),500);
            /**
             * End User wallet process
             */

            //Successful
            DB::commit();
            return api()->success(null,GiftcodeResource::make($giftcode));

        } catch (\Throwable $exception) {
            //Handle exceptions
            DB::rollBack();
            return api()->error($exception->getMessage(),null,$exception->getCode());
        }
    }

    /**
     * Get giftcode detail
     * @group Public User > Giftcode
     */
    public function show()
    {

        $giftcode = request()->user->giftcodes()->where('uuid',request()->uuid)->first();

        if(!$giftcode)
            return api()->error(trans('giftcode.responses.not-valid-giftcode-id'),null,404);

        return api()->success(null,GiftcodeResource::make($giftcode));
    }

    /**
     * Cancel Giftcode
     * @group Public User > Giftcode
     */
    public function cancel()
    {

    }

    private function withdrawUserWallet($giftcode)
    {
        $preparedUser = prepareGiftcodeUser(request()->user);

        //Prepare Transaction
        $transactionService = app(Transaction::class);
        $transactionService->setConfiremd(true);
        $transactionService->setAmount($giftcode->total_cost_in_usd);
        $transactionService->setFromWalletName(request()->get('wallet'));
        $transactionService->setFromUserId(request()->user->id);
        $transactionService->setDescription(trans('giftcode.phrases.buy-a-giftcode'));

        //Prepare Withdraw Service
        $withdrawService = app(Withdraw::class);
        $withdrawService->setTransaction($transactionService);
        $withdrawService->setUser($preparedUser);

        //Withdraw transaction
        return $finalTransaction = $this->walletService->withdraw($withdrawService);


    }

    private function checkUserBalance($request)
    {
        $wallet = prepareGiftcodeWallet(request()->user,$request->get('wallet'));
        return $this->walletService->getBalance($wallet)->getBalance();
    }

}
