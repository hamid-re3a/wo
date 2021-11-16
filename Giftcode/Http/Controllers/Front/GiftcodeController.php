<?php


namespace Giftcode\Http\Controllers\Front;


use App\Http\Controllers\Controller;
use Giftcode\Http\Requests\User\CancelGiftcodeRequest;
use Giftcode\Http\Requests\User\CreateGiftcodeRequest;
use Giftcode\Http\Resources\GiftcodeResource;
use Giftcode\Jobs\UpdatePackages;
use Giftcode\Models\Giftcode;
use Giftcode\Repository\GiftcodeRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use User\Models\User;

class GiftcodeController extends Controller
{

    private $giftcode_repository;

    public function __construct(GiftcodeRepository $giftcodeRepository)
    {
        $this->giftcode_repository = $giftcodeRepository;
    }

    /**
     * Giftcode counts
     * @group Public User > Giftcode
     */
    public function counts()
    {
        $total_giftcodes = User::query()->whereId(auth()->user()->id)->withCount([
            'giftcodes as total_created',
            'giftcodes as total_used' => function(Builder $query) {
                $query->whereNotNull('redeem_user_id');
            },
            'giftcodes as total_expired' => function(Builder $query) {
                $query->where('expiration_date' , '<' , now()->toDateTimeString());
            },
            'giftcodes as total_canceled' => function(Builder $query) {
                $query->where('is_canceled',true);
            }
        ])->first()->toArray();
        return api()->success(null,[
            'total_created' => $total_giftcodes['total_created'],
            'total_available' => $total_giftcodes['total_created'] - ($total_giftcodes['total_used'] + $total_giftcodes['total_canceled'] + $total_giftcodes['total_expired']),
            'total_used' => $total_giftcodes['total_used'],
            'total_canceled' => $total_giftcodes['total_canceled'],
            'total_expired' => $total_giftcodes['total_expired'],
        ]);
    }

    /**
     * Giftcodes list
     * @group Public User > Giftcode
     */
    public function index()
    {
        /**@var $user User*/
        $user = auth()->user();
        $list = $user->giftcodes()->orderBy('created_at', 'DESC')->paginate();

        return api()->success(null, [
            'list' => GiftcodeResource::collection($list),
            'pagination' => [
                'total' => $list->total(),
                'per_page' => $list->perPage(),
            ]
        ]);
    }

    /**
     * Create giftcode
     * @group Public User > Giftcode
     * @param CreateGiftcodeRequest $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function store(CreateGiftcodeRequest $request)
    {
        UpdatePackages::dispatchSync();
        try {
            $giftcode = $this->giftcode_repository->create($request->merge([
                'package_id' => $request->get('package_id'),
            ]));
            //Successful
            return api()->success(null, GiftcodeResource::make($giftcode));

        } catch (\Throwable $exception) {
            //Handle exceptions
            Log::error('GiftcodeController@store  => ' . $exception->getMessage() . ' Line => ' . $exception->getLine());
            return api()->error(null,null,$exception->getCode(),[
                'subject' => $exception->getMessage()
            ]);
        }
    }

    /**
     * Cancel Giftcode
     * @group Public User > Giftcode
     * @param CancelGiftcodeRequest $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function cancel(CancelGiftcodeRequest $request)
    {

        try {

            $giftcode = $this->giftcode_repository->cancel($request);
            return api()->success(trans('giftcode.responses.giftcode-canceled-amount-refunded', ['amount' => (double)$giftcode->getRefundAmount()]));

        } catch (\Throwable $exception) {
            //Handle exceptions
            Log::error('Cancel giftcode error,gift code uuid => <' . $request->get('uuid') . '>');
            return api()->error(null,null,$exception->getCode(),[
                'subject' => $exception->getMessage()
            ]);

        }
    }

}
