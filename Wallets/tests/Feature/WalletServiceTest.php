<?php


namespace Wallets\tests\Feature;


use User\Models\User;
use Wallets\Models\TransactionType;
use Wallets\Services\Deposit;
use Wallets\Services\Transaction;
use Wallets\Services\Transfer;
use Wallets\Services\Wallet;
use Wallets\Services\WalletService;
use Wallets\Services\Withdraw;
use Wallets\tests\WalletTest;

class WalletServiceTest extends WalletTest
{

    /**
     * @test
     */
    public function deposit_unconfirmed()
    {
        $wallet_service = app(WalletService::class);

        //Deposit Service
        $deposit_service = app(Deposit::class);
        $deposit_service->setConfirmed(false);
        $deposit_service->setUserId(1);
        $deposit_service->setAmount(10000000);
        $deposit_service->setType('Deposit');
        $deposit_service->setWalletName('Deposit Wallet');

        //Deposit transaction
        $deposit = $wallet_service->deposit($deposit_service);
        $this->assertIsObject($deposit);
        $this->assertFalse($deposit->getConfirmed());

    }

    /**
     * @test
     */
    public function deposit_wrong_wallet_name()
    {
        $wallet_service = app(WalletService::class);

        //Deposit Service
        $deposit_service = app(Deposit::class);
        $deposit_service->setConfirmed(false);
        $deposit_service->setUserId(1);
        $deposit_service->setAmount(10000000);
        $deposit_service->setType('Deposit');
        $deposit_service->setWalletName('Wrong Wallet Name');

        //Deposit transaction
        $deposit = $wallet_service->deposit($deposit_service);
        $this->assertIsObject($deposit);
        $this->assertFalse($deposit->getConfirmed());

    }

    /**
     * @test
     */
    public function deposit_confirmed($user_id = 1)
    {
        $wallet_service = app(WalletService::class);

        //Deposit Service
        $deposit_service = app(Deposit::class);
        $deposit_service->setConfirmed(true);
        $deposit_service->setUserId($user_id);
        $deposit_service->setAmount(10000000);
        $deposit_service->setType('Deposit');
        $deposit_service->setWalletName('Deposit Wallet');

        //Deposit transaction
        $deposit = $wallet_service->deposit($deposit_service);
        $this->assertIsObject($deposit);
        $this->assertTrue($deposit->getConfirmed());

    }

    /**
     * @test
     */
    public function deposit_with_sub_type_confirmed($user_id = 1)
    {
        $wallet_service = app(WalletService::class);

        //Deposit Service
        $deposit_service = app(Deposit::class);
        $deposit_service->setConfirmed(true);
        $deposit_service->setUserId($user_id);
        $deposit_service->setAmount(10000000);
        $deposit_service->setType('Commission');
        $deposit_service->setSubType('Indirect Commission');
        $deposit_service->setWalletName('Deposit Wallet');

        //Deposit transaction
        $deposit = $wallet_service->deposit($deposit_service);
        $this->assertIsObject($deposit);
        $this->assertTrue($deposit->getConfirmed());

    }

    /**
     * @test
     */
    public function withdraw_unconfirmed()
    {
        $wallet_service = app(WalletService::class);

        //Deposit
        $this->deposit_confirmed();

        //Withdraw
        $withdraw_service = app(Withdraw::class);
        $withdraw_service->setConfirmed(false);
        $withdraw_service->setUserId(1);
        $withdraw_service->setWalletName('Deposit Wallet');
        $withdraw_service->setType('Giftcode');
        $withdraw_service->setAmount(100);
        $withdraw = $wallet_service->withdraw($withdraw_service);
        $this->assertIsObject($withdraw);
        $this->assertFalse($withdraw->getConfirmed());
    }

    /**
     * @test
     */
    public function withdraw_wrong_wallet_name()
    {
        $wallet_service = app(WalletService::class);

        //Deposit
        $this->deposit_confirmed();

        //Withdraw
        $withdraw_service = app(Withdraw::class);
        $withdraw_service->setConfirmed(true);
        $withdraw_service->setUserId(1);
        $withdraw_service->setWalletName('Wrong Walelt Name');
        $withdraw_service->setType('Giftcode');
        $withdraw_service->setAmount(100);
        $withdraw = $wallet_service->withdraw($withdraw_service);
        $this->assertIsObject($withdraw);
        $this->assertFalse($withdraw->getConfirmed());
    }

    /**
     * @test
     */
    public function withdraw_confirmed()
    {
        $wallet_service = app(WalletService::class);

        //Deposit
        $this->deposit_confirmed();

        //Withdraw
        $withdraw_service = app(Withdraw::class);
        $withdraw_service->setConfirmed(true);
        $withdraw_service->setUserId(1);
        $withdraw_service->setWalletName('Deposit Wallet');
        $withdraw_service->setType('Giftcode');
        $withdraw_service->setAmount(100);
        $withdraw = $wallet_service->withdraw($withdraw_service);
        $this->assertIsObject($withdraw);
        $this->assertTrue($withdraw->getConfirmed());
    }

    /**
     * @test
     */
    public function transfer_fund()
    {
        //User
        $from_user = User::factory()->create();

        $to_user = User::factory()->create();

        $wallet_service = app(WalletService::class);
        //Deposit from user wallet
        $this->deposit_confirmed($from_user->id);

        //Prepare transfer
        $transfer_service = app(Transfer::class);
        $transfer_service->setFromUserId($from_user->id);
        $transfer_service->setFromWalletName('Deposit Wallet');
        $transfer_service->setToUserId($to_user->id);
        $transfer_service->setToWalletName('Deposit Wallet');
        $transfer_service->setAmount(1000);
        $transfer_service->setConfirmed(true);

        $transaction_response = $wallet_service->transfer($transfer_service);
        $this->assertIsObject($transaction_response);
        $this->assertTrue($transaction_response->getConfirmed());
    }

    /**
     * @test
     */
    public function get_wallet_balance()
    {
        //User
        $user = User::factory()->create();

        $wallet_service = app(WalletService::class);

        //Deposit
        $this->deposit_confirmed($user->id);

        //Prepare wallet
        $user_wallet_service = app(Wallet::class);
        $user_wallet_service->setUserId($user->id);
        $user_wallet_service->setName('Deposit Wallet');

        //Check balance
        $response_wallet = $wallet_service->getBalance($user_wallet_service);
        $this->assertIsObject($response_wallet);
        if (is_numeric($response_wallet->getBalance()) AND $response_wallet->getBalance() > 0)
            $this->assertTrue(TRUE);
        else
            $this->assertTrue(FALSE);

    }

}
