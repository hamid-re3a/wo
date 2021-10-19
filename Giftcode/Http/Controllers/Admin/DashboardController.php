<?php


namespace Giftcode\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Giftcode\Http\Requests\ChartTypeRequest;
use Giftcode\Http\Resources\GiftcodeResource;
use Giftcode\Models\Giftcode;
use Giftcode\Repository\ChartRepository;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{

    private $chart_repository;

    public function __construct(ChartRepository $chartRepository)
    {
        $this->chart_repository = $chartRepository;
    }

    /**
     * Giftcode counts
     * @group Admin User > Giftcode
     */
    public function counts()
    {
        $model = app(Giftcode::class);

        $response = [
            'total_created' => $model->all()->count(),
            'total_used' => $model->whereNotNull('redeem_user_id')->count(),
            'total_expired' => $model->where('expiration_date' , '<' , now()->toDateTimeString())->count(),
            'total_canceled' => $model->where('is_canceled',true)->count(),
        ];

        return api()->success(null,[
            'total_created' => $response['total_created'],
            'total_available' => $response['total_created'] - ($response['total_used'] + $response['total_canceled'] + $response['total_expired']),
            'total_used' => $response['total_used'],
            'total_canceled' => $response['total_canceled'],
            'total_expired' => $response['total_expired'],
        ]);
    }

    /**
     * Giftcodes list
     * @group Admin User > Giftcode
     */
    public function index()
    {
        $list = Giftcode::query()->orderBy('created_at', 'DESC')->paginate();

        return api()->success(null, [
            'list' => GiftcodeResource::collection($list),
            'pagination' => [
                'total' => $list->total(),
                'per_page' => $list->perPage(),
            ]
        ]);
    }


    /**
     * Giftcode vs Time chart
     * @group Admin User > Giftcode
     * @param ChartTypeRequest $request
     * @return JsonResponse
     */
    public function giftcodesVsTimeChart(ChartTypeRequest $request)
    {
        return api()->success(null,$this->chart_repository->getGiftcodeVsTimeChart($request->get('type')));
    }


    /**
     * Package vs Time chart
     * @group Admin User > Giftcode
     * @param ChartTypeRequest $request
     * @return JsonResponse
     */
    public function packageVsTimeChart(ChartTypeRequest $request)
    {
        return api()->success(null,$this->chart_repository->getPackageVsTimeChart($request->get('type')));
    }

}
