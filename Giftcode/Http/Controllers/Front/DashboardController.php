<?php


namespace Giftcode\Http\Controllers\Front;


use App\Http\Controllers\Controller;
use Giftcode\Http\Requests\ChartTypeRequest;
use Giftcode\Repository\ChartRepository;

class DashboardController extends Controller
{

    private $chart_repository;

    public function __construct(ChartRepository $chartRepository)
    {
        $this->chart_repository = $chartRepository;
    }

    public function giftcodesVsTimeChart(ChartTypeRequest $request)
    {
        return api()->success(null,$this->chart_repository->getGiftcodeVsTimeChart($request->get('type'),auth()->user()->id));
    }

    public function packageVsTimeChart(ChartTypeRequest $request)
    {
        return api()->success(null,$this->chart_repository->getPackageVsTimeChart($request->get('type'),auth()->user()->id));
    }

}
