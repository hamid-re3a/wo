<?php


namespace Wallets\tests\Feature;


use User\Models\User;
use Wallets\Services\Deposit;
use Wallets\Services\Transaction;
use Wallets\Services\Transfer;
use Wallets\Services\Wallet;
use Wallets\Services\WalletService;
use Wallets\Services\Withdraw;
use Wallets\tests\WalletTest;

class WalletServiceTest extends WalletTest
{

    private function getTransaction($confirmed = TRUE)
    {
        $transaction_service = app(Transaction::class);
        $transaction_service->setConfiremd($confirmed);
        $transaction_service->setAmount(1000);
        $transaction_service->setToWalletName('Deposit Wallet');
        $transaction_service->setToUserId(1);
        $transaction_service->setType('Deposit');
        $transaction_service->setDescription(serialize([
            'description' => 'Deposit Test #12'
        ]));

        return $transaction_service;
    }

    /**
     * @test
     */
    public function deposit_unconfirmed_transaction()
    {
        $user = User::factory()->create();
        $user_object = $user->getUserService();

        $wallet_service = app(WalletService::class);

        //Deposit Service
        $deposit_service = app(Deposit::class);
        $deposit_service->setTransaction($this->getTransaction(FALSE));
        $deposit_service->setUser($user_object);

        //Deposit transaction
        $deposit_transaction = $wallet_service->deposit($deposit_service);
        $this->assertIsObject($deposit_transaction);
        $this->assertFalse($deposit_transaction->getConfiremd());

    }

    /**
     * @test
     */
    public function deposit_confirmed_transaction()
    {
        $user = User::factory()->create();
        $user_object = $user->getUserService();

        $wallet_service = app(WalletService::class);

        //Deposit Service
        $deposit_service = app(Deposit::class);
        $deposit_service->setTransaction($this->getTransaction(TRUE));
        $deposit_service->setUser($user_object);

        //Deposit transaction
        $deposit_transaction = $wallet_service->deposit($deposit_service);
        $this->assertIsObject($deposit_transaction);
        $this->assertTrue($deposit_transaction->getConfiremd());

    }

    /**
     * @test
     */
    public function withdraw_unconfirmed_transaction()
    {
        //User
        $user = User::factory()->create();
        $user_object = $user->getUserService();

        $wallet_service = app(WalletService::class);

        //Deposit
        $deposit_service = app(Deposit::class);
        $deposit_service->setTransaction($this->getTransaction(TRUE));
        $deposit_service->setUser($user_object);
        $wallet_service->deposit($deposit_service);

        //Withdraw
        $withdraw_service = app(Withdraw::class);
        $withdraw_service->setTransaction($this->getTransaction(FALSE));
        $withdraw_service->setUser($user_object);
        $withdraw_transaction = $wallet_service->withdraw($withdraw_service);
        $this->assertIsObject($withdraw_transaction);
        $this->assertFalse($withdraw_transaction->getConfiremd());

    }

    /**
     * @test
     */
    public function withdraw_confirmed_transaction()
    {
        //User
        $user = User::factory()->create();
        $user_object = $user->getUserService();

        $wallet_service = app(WalletService::class);

        //Deposit
        $deposit_service = app(Deposit::class);
        $deposit_service->setTransaction($this->getTransaction(TRUE));
        $deposit_service->setUser($user_object);
        $wallet_service->deposit($deposit_service);

        //Withdraw
        $withdraw_service = app(Withdraw::class);
        $withdraw_service->setTransaction($this->getTransaction(TRUE));
        $withdraw_service->setUser($user_object);
        $withdraw_transaction = $wallet_service->withdraw($withdraw_service);
        $this->assertIsObject($withdraw_transaction);
        $this->assertFalse($withdraw_transaction->getConfiremd());
    }

    /**
     * @test
     */
    public function transfer_fund_with_unconfirmed_transaction()
    {
        //User
        $from_user = User::factory()->create();
        $from_user_object = $from_user->getUserService();

        $to_user = User::factory()->create();
        $to_user_object = $to_user->getUserService();

        $wallet_service = app(WalletService::class);

        //Deposit from user wallet
        $deposit_service = app(Deposit::class);
        $deposit_service->setTransaction($this->getTransaction(TRUE));
        $deposit_service->setUser($from_user_object);
        $deposit_transaction = $wallet_service->deposit($deposit_service);
        $this->assertTrue($deposit_transaction->getConfiremd());

        //Prepare transfer
        $transfer_service = app(Transfer::class);
        $transfer_service->setFromUser($from_user_object);
        $transfer_service->setToUser($to_user_object);

        $transaction_service = app(Transaction::class);
        $transaction_service->setConfiremd(FALSE);
        $transaction_service->setAmount(100);

        //From User
        $transaction_service->setFromUserId($from_user->id);
        $transaction_service->setFromWalletName('Deposit Wallet');

        //To User
        $transaction_service->setToUserId($to_user->id);
        $transaction_service->setToWalletName('Deposit Wallet');

        //finalize transfer service
        $transfer_service->setTransaction($transaction_service);

        $transaction_response = $wallet_service->transfer($transfer_service);
        $this->assertIsObject($transaction_response);
        $this->assertFalse($transaction_response->getConfiremd());
    }

    /**
     * @test
     */
    public function transfer_fund_with_confirmed_transaction()
    {
        //User
        $from_user = User::factory()->create();
        $from_user_object = $from_user->getUserService();

        $to_user = User::factory()->create();
        $to_user_object = $to_user->getUserService();

        $wallet_service = app(WalletService::class);

        //Deposit from user wallet
        $deposit_service = app(Deposit::class);
        $deposit_service->setTransaction($this->getTransaction(TRUE));
        $deposit_service->setUser($from_user_object);
        $deposit_transaction = $wallet_service->deposit($deposit_service);
        $this->assertTrue($deposit_transaction->getConfiremd());

        //Prepare transfer
        $transfer_service = app(Transfer::class);
        $transfer_service->setFromUser($from_user_object);
        $transfer_service->setToUser($to_user_object);

        $transaction_service = app(Transaction::class);
        $transaction_service->setConfiremd(TRUE);
        $transaction_service->setAmount(100);

        //From User
        $transaction_service->setFromUserId($from_user->id);
        $transaction_service->setFromWalletName('Deposit Wallet');

        //To User
        $transaction_service->setToUserId($to_user->id);
        $transaction_service->setToWalletName('Deposit Wallet');

        //finalize transfer service
        $transfer_service->setTransaction($transaction_service);

        $transaction_response = $wallet_service->transfer($transfer_service);
        $this->assertIsObject($transaction_response);
        $this->assertTrue($transaction_response->getConfiremd());
    }

    /**
     * @test
     */
    public function get_wallet_balance()
    {
        //User
        $user = User::factory()->create();
        $user_object = $user->getUserService();

        $wallet_service = app(WalletService::class);

        //Deposit
        $deposit_service = app(Deposit::class);
        $deposit_service->setTransaction($this->getTransaction(TRUE));
        $deposit_service->setUser($user_object);
        $wallet_service->deposit($deposit_service);

        //Prepare wallet
        $user_wallet_service = app(Wallet::class);
        $user_wallet_service->setUser($user_object);
        $user_wallet_service->setName('Deposit Wallet');

        //Check balance
        $response_wallet = $wallet_service->getBalance($user_wallet_service);
        $this->assertIsObject($response_wallet);
        if(is_numeric($response_wallet->getBalance()) AND $response_wallet->getBalance() > 0)
            $this->assertTrue(TRUE);
        else
            $this->assertTrue(FALSE);

    }

}
