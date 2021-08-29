<?php


namespace Giftcode\Http\Controllers\User;


use App\Http\Controllers\Controller;
use Giftcode\Http\Requests\User\CancelGiftcodeRequest;
use Giftcode\Http\Requests\User\CreateGiftcodeRequest;
use Giftcode\Http\Requests\User\RedeemGiftcodeRequest;
use Giftcode\Http\Resources\GiftcodeResource;
use Giftcode\Jobs\TrivialEmailJob;
use Giftcode\Jobs\UpdatePackages;
use Giftcode\Jobs\UrgentEmailJob;
use Giftcode\Mail\User\GiftcodeCanceledEmail;
use Giftcode\Mail\User\RedeemedGiftcodeCreatorEmail;
use Giftcode\Mail\User\RedeemedGiftcodeRedeemerEmail;
use Giftcode\Models\Giftcode;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Wallets\Services\Deposit;
use Wallets\Services\Transaction;
use Wallets\Services\WalletService;
use Wallets\Services\Withdraw;

class GiftcodeController extends Controller
{

    private $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

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
     * @return JsonResponse
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
     * @param CancelGiftcodeRequest $request
     * @return JsonResponse
     */
    public function cancel(CancelGiftcodeRequest $request)
    {
        $giftcode = request()->user->giftcodes()->where('uuid',$request->get('id'))->first();

        if($giftcode->is_canceled === true)
            return api()->error(trans('giftcode.responses.giftcode-is-used-and-user-cant-cancel'),null,406);


        if(!empty($giftcode->expiration_date) AND $giftcode->expiration_date->isPast())
            return api()->error(trans('giftcode.responses.giftcode-is-expired-and-user-cant-cancel'),null,406);

        try {
            DB::beginTransaction();
            $giftcode->update([
                'is_canceled' => true
            ]);


            /**
             * Refund Giftcode total paid - cancelation fee
             */
            //Refund giftcode pay fee
            $finalTransaction = $this->depositUserWallet($giftcode);

            //Wallet transaction failed [Server error]
            if(!$finalTransaction->getConfiremd())
                throw new \Exception(trans('giftcode.validation.wallet-withdrawal-error'),500);
            /**
             * End refund
             */

            UrgentEmailJob::dispatch(new GiftcodeCanceledEmail(request()->user,$giftcode),request()->user->email);

            DB::commit();
            return api()->success(trans('giftcode.responses.giftcode-canceled-amount-refunded', ['amount' => (float) $giftcode->getRefundAmount()]));

        } catch (\Throwable $exception) {
            //Handle exceptions
            DB::rollBack();
            Log::error('Cancel giftcode error,Giftcode uuid => <' . $giftcode->uuid . '>');
            return api()->error($exception->getMessage(),null,$exception->getCode());

        }
    }

    /**
     * Redeem a giftcode
     * @group Public User > Giftcode
     * @param RedeemGiftcodeRequest $request
     * @return JsonResponse
     */
    public function redeem(RedeemGiftcodeRequest $request)
    {
        try {
            DB::beginTransaction();
            $giftcode = Giftcode::where('uuid', $request->get('id'))->first();

            if($giftcode->is_canceled === true)
                return api()->error(trans('giftcode.responses.giftcode-is-canceled-and-user-cant-redeem'),null,406);

            if(!empty($giftcode->redeem_user_id))
                return api()->error(trans('giftcode.responses.giftcode-is-used-and-user-cant-redeem'),null,406);


            if(!empty($giftcode->expiration_date) AND $giftcode->expiration_date->isPast())
                return api()->error(trans('giftcode.responses.giftcode-is-expired-and-user-cant-redeem'),null,406);

            $giftcode->update([
                'redeem_user_id' => request()->user->id,
                'redeem_date' => now()->toDateTimeString()
            ]);

            /**
             * Start sending emails
             */
            //Redeemer email
            TrivialEmailJob::dispatch(new RedeemedGiftcodeRedeemerEmail($request->user,$giftcode), $request->user->email);

            //Creator email
            if($giftcode->user_id != $giftcode->redeem_user_id) //Check if creator used his own giftcode,We wont send any email for creator
                TrivialEmailJob::dispatch(new RedeemedGiftcodeCreatorEmail($giftcode->creator,$giftcode), $giftcode->creator->email);
            /**
             * End sending emails
             */

            DB::commit();

            return api()->success(null,GiftcodeResource::make($giftcode));
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Giftcode redeem error > ' . $exception->getMessage());
            return api()->error(trans('giftcode.responses.something-went-wrong'));
        }
    }

    private function withdrawUserWallet($giftcode)
    {
        $preparedUser = request()->user->getUserService();

        //Prepare Transaction
        $transactionService = app(Transaction::class);
        $transactionService->setConfiremd(true);
        $transactionService->setAmount($giftcode->total_cost_in_usd);
        $transactionService->setFromWalletName(request()->get('wallet'));
        $transactionService->setFromUserId(request()->user->id);
        $transactionService->setType('Giftcode');
        $transactionService->setDescription('Create Giftcode #' . $giftcode->uuid);

        //Prepare Withdraw Service
        $withdrawService = app(Withdraw::class);
        $withdrawService->setTransaction($transactionService);
        $withdrawService->setUser($preparedUser);

        //Withdraw transaction
        return $finalTransaction = $this->walletService->withdraw($withdrawService);
    }

    private function depositUserWallet($giftcode)
    {
        $preparedUser = request()->user->getUserService();

        //Prepare Transaction
        $transactionService = app(Transaction::class);
        $transactionService->setConfiremd(true);
        $transactionService->setAmount($giftcode->getRefundAmount());
        $transactionService->setToWalletName('Deposit Wallet');
        $transactionService->setToUserId(request()->user->id);
        $transactionService->setType('Giftcode');
        $transactionService->setDescription('Refund Giftcode #' . $giftcode->uuid);

        //Prepare Deposit Service
        $depositService = app(Deposit::class);
        $depositService->setTransaction($transactionService);
        $depositService->setUser($preparedUser);
        //Deposit transaction
        return $finalTransaction = $this->walletService->deposit($depositService);
    }

    private function checkUserBalance($request)
    {
        $wallet = prepareGiftcodeWallet(request()->user,$request->get('wallet'));
        return $this->walletService->getBalance($wallet)->getBalance();
    }

}
