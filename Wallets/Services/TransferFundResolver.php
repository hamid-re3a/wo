<?php


namespace Wallets\Services;


use Bavix\Wallet\Models\Transfer;
use User\Models\User;

class TransferFundResolver
{
    private $transfer;
    /**@var $from_user User*/
    private $from_user;
    /**@var $to_user User*/
    private $to_user;

    public function __construct(Transfer $transfer)
    {
        $this->transfer = $transfer;
        $this->from_user = $transfer->withdraw->payable;
        $this->to_user = $transfer->deposit->payable;
    }

    public function resolve() : array
    {
        if(!$this->checkHasValidOrExpiredPackage($this->to_user))
            return [false,trans('wallet.transfer_funds.user-has-not-valid-package')];

        return [true,null];
    }

    private function checkHasValidOrExpiredPackage(User $user)
    {
        $mlm_grpc = MlmClientFacade::hasValidPackage($user->getUserService());
        return $mlm_grpc->getStatus();
    }


}
