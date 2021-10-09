<?php


namespace Wallets\Services;


use User\Models\User;
use Wallets\Models\WithdrawProfit;

class WithdrawResolver
{
    public $withdrawRequest;

    public function __construct(WithdrawProfit $withdrawRequest)
    {
        $this->withdrawRequest = $withdrawRequest;
    }

    /**
     * We check here that withdraw request is valid or not,
     * For example, User can withdraw this amount ?
     * We will check any condition in whole system, Like rank,Maximum withdraw request for today and etc.
     * @return array
     * @throws \Exception
     */
    public function verifyWithdrawRequest()
    {
        /**@var $user_db User */
        $user_db = $this->withdrawRequest->user()->first();
        $user_service = $user_db->getUserService();

        //get user today's withdraw sum
        $db_sum = WithdrawProfit::query()->where('user_id', $this->withdrawRequest->user_id)
            ->whereBetween('created_at', [now()->startOfDay()->toDateTimeString(), now()->endOfDay()->toDateTimeString()])
            ->sum('pf_amount');

        //Get user rank from MLM service
        $user_rank = MlmClientFacade::getUserRank($user_service);
        if ($db_sum > $user_rank->getWithdrawalLimit())
            return [false, trans('wallet.responses.withdraw-profit-request.withdraw_rank_limit', ['amount' => $user_rank->getWithdrawalLimit() - $db_sum])];

        //Check system token distribution conditions
        list($flag,$response) = $this->checkDistribution();
        if(!$flag)
            return [$flag,$response];

        return [true,''];


    }

    private function checkDistribution()
    {
        try {
            if (walletGetSetting('withdrawal_distribution_is_enabled')) {

                $sum_withdraw_requests_for_today = WithdrawProfit::query()->where('user_id', $this->withdrawRequest->user_id)
                    ->whereBetween('created_at', [now()->startOfDay()->toDateTimeString(), now()->endOfDay()->toDateTimeString()])
                    ->sum('pf_amount');

                $sum_withdraw_requests_for_today_for_currency = WithdrawProfit::query()->where('user_id', $this->withdrawRequest->user_id)
                    ->where('currency', $this->withdrawRequest->currency)
                    ->whereBetween('created_at', [now()->startOfDay()->toDateTimeString(), now()->endOfDay()->toDateTimeString()])
                    ->sum('pf_amount');

                if ($this->withdrawRequest->currency == 'BTC')
                    $distribution = walletGetSetting('withdrawal_distribution_in_btc');
                else
                    $distribution = walletGetSetting('withdrawal_distribution_in_janex');

                $currency_distribution_for_user = ($sum_withdraw_requests_for_today_for_currency / $sum_withdraw_requests_for_today) * 100 ;

                if($currency_distribution_for_user >= $distribution)
                    return [false,trans('wallet.responses.withdraw-profit-request.withdraw_distribution_limit', ['currency' => $this->withdrawRequest->currency])];

                return [true,''];

            }
        } catch (\Exception $e) {
        }
    }
}
