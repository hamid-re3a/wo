<?php

namespace Payments\Services\Processors;

use Payments\Jobs\ProcessBTCPayServerPayoutsJob;

class PayoutProcessor
{

    /**
     * pay by every payment Driver
     * @inheritDoc
     */
    public function pay($method,array $address_amounts,array $ids = []): array
    {
        switch ($method) {
            case 'btc-pay-server':
                return $this->payoutBtcPayServer($address_amounts,$ids);
            default:
                break;
        }

        return [false,trans('payment.responses.something-went-wrong')];

    }

    private function payoutBtcPayServer($address_amounts,$ids)
    {
        ProcessBTCPayServerPayoutsJob::dispatch($address_amounts,$ids);
        return [true,null];
    }

}
