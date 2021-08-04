<?php

namespace Orders\Services;


use Illuminate\Support\Facades\DB;

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
        DB::beginTransaction();
        $problem_level = 0;


        list($bool, $msg) = $this->isValid();
        if ($bool) {
            list($bool, $msg) = $this->addUserToNetwork();
            if ($bool) {
                DB::commit();
                return [true, 'resolve', $problem_level];
            } else {
                $problem_level = 2;
            }
        } else {
            $problem_level = 1;
        }

        DB::rollBack(0);
        return [false, $msg ?? 'resolve', $problem_level];
    }


    /**
     * @return array [bool,string]
     * @throws \Throwable
     */
    public function simulateValidation(): array
    {
        DB::beginTransaction();

        list($bool, $msg) = $this->isValid();
        if ($bool) {
            list($bool, $msg) = $this->addUserToNetwork(true);
            if ($bool) {
                DB::rollBack(0);
                return [true, 'resolve'];
            }

        }

        DB::rollBack(0);
        return [false, $msg ?? 'resolve'];

    }


    /**
     * @return array [bool,string]
     */
    public function isValid(): array
    {
        // check user if he is in tree
        return [true, trans('responses.isValid')];
    }


    /**
     * @param bool $simulate
     * @return array [bool,string]
     */

    private function addUserToNetwork($simulate = false): array
    {
        // Add user to network with rabbitmq job (MLM Tree stuff)
        // Check if it is possible to add user to network for simulation
    }


}
