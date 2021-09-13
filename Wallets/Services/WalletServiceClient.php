<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Wallets\Services;

/**
 */
class WalletServiceClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Wallets\Services\Deposit $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Wallets\Services\Deposit
     */
    public function deposit(\Wallets\Services\Deposit $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/wallets.services.WalletService/deposit',
        $argument,
        ['\Wallets\Services\Deposit', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Wallets\Services\Withdraw $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Wallets\Services\Withdraw
     */
    public function withdraw(\Wallets\Services\Withdraw $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/wallets.services.WalletService/withdraw',
        $argument,
        ['\Wallets\Services\Withdraw', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Wallets\Services\Transfer $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Wallets\Services\Transfer
     */
    public function transfer(\Wallets\Services\Transfer $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/wallets.services.WalletService/transfer',
        $argument,
        ['\Wallets\Services\Transfer', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Wallets\Services\Wallet $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Wallets\Services\Wallet
     */
    public function getBalance(\Wallets\Services\Wallet $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/wallets.services.WalletService/getBalance',
        $argument,
        ['\Wallets\Services\Wallet', 'decode'],
        $metadata, $options);
    }

}
