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
            $giftcode = Giftcode::create([
                'package_id' => $request->get('package_id')
            ]);


            //Check wallet balance
            $wallet = prepareGiftcodeWallet(request()->user,$request->get('wallet'));
            $walletService = app(WalletService::class);
            $wallet = $walletService->getBalance($wallet);

            if($wallet->getBalance() < $giftcode->total_cost_in_usd)
                throw new \Exception(trans('giftcode.validation.inefficient-account-balance',['amount' => (float)$giftcode->total_cost_in_usd ]),422);


            /**
             * Start Withdraw
             */
            $preparedUser = prepareGiftcodeUser($request->user);

            //Prepare Transaction
            $transactionService = app(Transaction::class);
            $transactionService->setConfiremd(true);
            $transactionService->setAmount($giftcode->total_cost_in_usd);
            $transactionService->setFromWalletName($request->get('wallet'));
            $transactionService->setFromUserId($request->user->id);
            $transactionService->setDescription(trans('giftcode.phrases.buy-a-giftcode'));

            //Prepare Withdraw Service
            $withdrawService = app(Withdraw::class);
            $withdrawService->setTransaction($transactionService);
            $withdrawService->setUser($preparedUser);

            //Withdraw transaction
            $finalTransaction = $walletService->withdraw($withdrawService);

            /**
             * End withdraw
             */

            //Wallet transaction failed [Server error]
            if(!$finalTransaction->getConfiremd())
                throw new \Exception(trans('giftcode.validation.wallet-withdrawal-error'),500);


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

    }

    /**
     * Cancel Giftcode
     * @group Public User > Giftcode
     */
    public function cancel()
    {

    }

}
