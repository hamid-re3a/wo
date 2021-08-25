<?php


namespace Wallets\Http\Controllers\Admin;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Wallets\Services\WalletService;

class InvoiceWalletController extends Controller
{

    private $wallet_service;

    public function __construct(WalletService $wallet_service)
    {
        $this->wallet_service = $wallet_service;
    }

    public function invoiceWallet(Request $request)
    {
        $wallet_service = $this->wallet_service->invoiceWallet($request);
        return api()->success('success', [
            'payment_currency'=>$wallet_service->getPaymentCurrency(),
            'amount' => $wallet_service->getAmount(),
            'checkout_link' => $wallet_service->getCheckoutLink(),
            'transaction_id' => $wallet_service->getTransactionId(),
            'expiration_time' => $wallet_service->getExpirationTime(),
        ]);
    }
}
