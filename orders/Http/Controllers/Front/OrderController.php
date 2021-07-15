<?php

namespace User\Http\Controllers\Front;


use App\Http\Requests\Front\Order\OrderRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class OrderController extends Controller
{
    /**
     * Submit new Order
     * @group
     * Public User > Orders
     */
    public function store(OrderRequest $request)
    {

        $this->validatePackages($request);


        return api()->success(trans('user.responses'));
    }

    private function validatePackages(Request $request)
    {
        $rules = [
            'items.*.id' => 'required',
            'items.*.qty' => 'required|numeric|min:1',
        ];
    }
}
