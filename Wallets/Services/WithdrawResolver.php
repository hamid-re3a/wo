<?php


namespace Wallets\Services;


use User\Models\User;
use Wallets\Models\WithdrawProfit;

class WithdrawResolver
{
    public $withdrawRequest;
    private $user_service;

    public function __construct(WithdrawProfit $withdrawRequest)
    {
        $this->withdrawRequest = $withdrawRequest;
        /**@var $user_db User */
        $user_db = $this->withdrawRequest->user()->first();
        $this->user_service = $user_db->getUserService();

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

        //Check daily limit
        list($flag, $response) = $this->checkDailyLimit();
        if (!$flag)
            return [$flag, $response];

        //Check system token distribution conditions
        list($flag, $response) = $this->checkDistribution();
        if (!$flag)
            return [$flag, $response];

        return [true, null];


    }

    private function checkDailyLimit()
    {

        //get user today's withdraw sum
        $db_sum = WithdrawProfit::query()->where('user_id', $this->withdrawRequest->user_id)
                ->whereBetween('created_at', [now()->startOfDay()->toDateTimeString(), now()->endOfDay()->toDateTimeString()])
                ->sum('pf_amount');

        //Get user rank from MLM service
        $user_rank = MlmClientFacade::getUserRank($this->user_service);
        if (( $db_sum + $this->withdrawRequest->pf_amount ) > $user_rank->getWithdrawalLimit())
            return [false, trans('wallet.responses.withdraw-profit-request.withdraw_rank_limit', ['amount' => $user_rank->getWithdrawalLimit() - $db_sum])];

        return [true, null];
    }

    private function checkDistribution()
    {
        if (!walletGetSetting('withdrawal_distribution_is_enabled'))
            return [true, null];

        $distribution_setting = $this->getDistributionSetting();

        $user_rank = MlmClientFacade::getUserRank($this->user_service);


        $sum_withdraw_requests_for_currency_for_today = WithdrawProfit::query()->where('user_id', $this->withdrawRequest->user_id)
            ->where('currency', $this->withdrawRequest->currency)
            ->whereBetween('created_at', [now()->startOfDay()->toDateTimeString(), now()->endOfDay()->toDateTimeString()])
            ->sum('pf_amount');


        $daily_limit_for_currency = ($distribution_setting * $user_rank->getWithdrawalLimit()) / 100;

        if ( $daily_limit_for_currency <= ($sum_withdraw_requests_for_currency_for_today + $this->withdrawRequest->pf_amount) )
            return [false, trans('wallet.responses.withdraw-profit-request.withdraw_distribution_limit', ['currency' => $this->withdrawRequest->currency])];

        return [true, null];

    }

    private function getDistributionSetting()
    {
        if ($this->withdrawRequest->currency == 'BTC')
            $distribution = walletGetSetting('withdrawal_distribution_in_btc');
        else
            $distribution = walletGetSetting('withdrawal_distribution_in_janex');

        if ($distribution > 100 OR $distribution < 0)
            throw new \Exception('Distribution error');
        return $distribution;
    }
}
