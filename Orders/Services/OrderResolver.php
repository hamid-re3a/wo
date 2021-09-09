<?php

namespace Orders\Services;


use App\Jobs\Order\MLMOrderJob;

class OrderResolver
{
    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @return array [bool,string]
     * @throws \Throwable
     */
    public function handle(): array
    {

        $processed = [true, 'handle'];
        if ($this->order->getIsResolvedAt())
            return $processed;
        if ($this->order->getIsPaidAt()) {

            list($bool, $msg, $problem_level) = $this->resolve();
//            if($this->plan->getName() == PLAN_BINARY)
//            dd([$bool, $msg, $problem_level]);
//            return [$bool, $msg];
        } else {
            $processed = [false, 'handle.notPaid'];
        }
        list($bool, $msg) = $processed;
        if ($bool)
            $this->order->setIsResolvedAt(now()->timestamp);
        return $processed;
    }

    /**
     * @return array [bool,string,int]
     * @throws \Throwable
     */
    public function resolve(): array
    {
        $problem_level = 0;


        list($bool, $msg) = $this->isValid();
        if ($bool) {
            list($bool, $msg) = $this->addUserToNetwork();
            if ($bool) {
                return [true, 'resolve', $problem_level];
            } else {
                $problem_level = 2;
            }
        } else {

            $problem_level = 1;
        }

        return [false, $msg ?? 'resolve', $problem_level];
    }


    /**
     * @return array [bool,string]
     * @throws \Throwable
     */
    public function simulateValidation(): array
    {
        list($bool, $msg) = $this->isValid();
        if ($bool) {
            return [true, 'resolve'];
        }

        return [false, $msg];

    }


    /**
     * @return array [bool,string]
     */
    public function isValid(): array
    {
        if ($this->order->getPlan() != ORDER_PLAN_START) {
            $check_start_plan = request()->user->paidOrders()->where('plan', '=', ORDER_PLAN_START)->count();
            if (!$check_start_plan)
                return [false, trans('order.responses.you-should-order-starter-plan-first')];
        }

        return [true, trans('order.responses.isValid')];

    }


    /**
     * @param bool $simulate
     * @return array [bool,string]
     */

    private function addUserToNetwork($simulate = false): array
    {
        MLMOrderJob::dispatch(serialize($this->order))->onConnection('rabbit')->onQueue('mlm');
        return [true, ''];
    }


}
