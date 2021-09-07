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
use Giftcode\Repository\GiftcodeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class GiftcodeController extends Controller
{

    private $giftcode_repository;

    public function __construct(GiftcodeRepository $giftcodeRepository)
    {
        $this->giftcode_repository = $giftcodeRepository;
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

            $giftcode = $this->giftcode_repository->create($request->merge([
                'package_id' => $request->get('package_id')
            ]));

            //Successful
            return api()->success(null,GiftcodeResource::make($giftcode));

        } catch (\Throwable $exception) {
            //Handle exceptions
            return api()->error(null,null,$exception->getCode(),[
                'subject' => $exception->getMessage()
            ]);
        }
    }

    /**
     * Get giftcode detail
     * @group Public User > Giftcode
     */
    public function show()
    {

        $giftcode = $this->giftcode_repository->getByUuid(request()->uuid);

        if(!$giftcode OR $giftcode->user_id != request()->user->id)
            return api()->error(null,null,404);

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

        try {

            $giftcode = $this->giftcode_repository->cancel($request);
            return api()->success(trans('giftcode.responses.giftcode-canceled-amount-refunded', ['amount' => (double) $giftcode->getRefundAmount()]));

        } catch (\Throwable $exception) {
            //Handle exceptions
            Log::error('Cancel giftcode error,iftcode uuid => <' . $request->get('uuid') . '>');
            return api()->error(null,null,$exception->getCode(),[
                'subject' => $exception->getMessage()
            ]);

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

            $giftcode = $this->giftcode_repository->redeem($request);

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

            return api()->success(null,GiftcodeResource::make($giftcode));
        } catch (\Throwable $exception) {
            Log::error('Giftcode redeem error > ' . $exception->getMessage());
            return api()->error(null,null,500,[
                'subject' => $exception->getMessage()
            ]);
        }
    }

}
