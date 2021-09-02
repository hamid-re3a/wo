<?php


namespace Giftcode\Repository;


use Giftcode\Models\Giftcode;
use Illuminate\Http\Request;
use User\Models\User;
use Wallets\Services\Deposit;
use Wallets\Services\Transaction;
use Wallets\Services\WalletService;
use Wallets\Services\Withdraw;

class WalletRepository
{
    private $wallet_service;

    public function __construct(WalletService $walletService)
    {
        $this->wallet_service = $walletService;
    }

    public function withdrawUserWallet(Giftcode $giftcode)
    {
        $preparedUser = request()->user->getUserService();

        //Prepare Transaction
        $transactionService = app(Transaction::class);
        $transactionService->setConfiremd(true);
        $transactionService->setAmount($giftcode->total_cost_in_usd);
        $transactionService->setFromWalletName(request()->get('wallet'));
        $transactionService->setFromUserId(request()->user->id);
        $transactionService->setType('Giftcode');
        $transactionService->setDescription('Create Giftcode #' . $giftcode->uuid);

        //Prepare Withdraw Service
        $withdrawService = app(Withdraw::class);
        $withdrawService->setTransaction($transactionService);
        $withdrawService->setUser($preparedUser);
        //Withdraw transaction
        return $finalTransaction = $this->wallet_service->withdraw($withdrawService);
    }

    public function depositUserWallet(Giftcode $giftcode, $description = 'Giftcode')
    {
        $preparedUser = request()->user->getUserService();

        //Prepare Transaction
        $transactionService = app(Transaction::class);
        $transactionService->setConfiremd(true);
        $transactionService->setAmount($giftcode->getRefundAmount());
        $transactionService->setToWalletName('Deposit Wallet');
        $transactionService->setToUserId(request()->user->id);
        $transactionService->setType('Giftcode');
        $transactionService->setDescription($description);

        //Prepare Deposit Service
        $depositService = app(Deposit::class);
        $depositService->setTransaction($transactionService);
        $depositService->setUser($preparedUser);
        //Deposit transaction

        //Deposit fee to admin wallet
        if($giftcode->total_cost_in_usd != $giftcode->getRefundAmount())
            $this->depositToAdminWallet($giftcode,$description);

        return $finalTransaction = $this->wallet_service->deposit($depositService);
    }

    public function checkUserBalance(Request $request)
    {
        $wallet = prepareGiftcodeWallet(request()->user,$request->get('wallet'));
        return $this->wallet_service->getBalance($wallet)->getBalance();
    }

    private function depositToAdminWallet($giftcode, $description = 'Giftcode')
    {
        $admin_user = User::query()->find(1);
        $transactionService = app(Transaction::class);
        $transactionService->setConfiremd(true);
        $transactionService->setAmount( ($giftcode->total_cost_in_usd - $giftcode->getRefundAmount()) );
        $transactionService->setToWalletName('Deposit Wallet');
        $transactionService->setToUserId(1);
        $transactionService->setType('Giftcode');
        $transactionService->setDescription($description);

        //Prepare Deposit Service
        $depositService = app(Deposit::class);
        $depositService->setTransaction($transactionService);
        $depositService->setUser($admin_user->getUserService());
        //Deposit transaction
        $this->wallet_service->deposit($depositService);
    }


}
