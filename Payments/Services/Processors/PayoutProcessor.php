<?php

namespace Payments\Services\Processors;

use Payments\Jobs\ProcessBTCPayServerPayoutsJob;

class PayoutProcessor
{

    /**
     * pay by every payment Driver
     * @inheritDoc
     */
    public function pay($currency,$payout_requests): array
    {
        switch ($currency) {
            case 'BTC':
                return $this->payoutBtc($payout_requests);
            default:
                break;
        }

        return [false,trans('payment.responses.something-went-wrong')];

    }

    private function payoutBtc($payout_requests)
    {
        ProcessBTCPayServerPayoutsJob::dispatch($payout_requests);
        return [true,null];
    }

}
