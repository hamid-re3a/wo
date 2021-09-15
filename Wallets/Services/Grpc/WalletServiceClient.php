<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Wallets\Services\Grpc;

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
     * @param \Wallets\Services\Grpc\Deposit $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Wallets\Services\Grpc\Deposit
     */
    public function deposit(\Wallets\Services\Grpc\Deposit $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/wallets.services.grpc.WalletService/deposit',
        $argument,
        ['\Wallets\Services\Grpc\Deposit', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Wallets\Services\Grpc\Withdraw $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Wallets\Services\Grpc\Withdraw
     */
    public function withdraw(\Wallets\Services\Grpc\Withdraw $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/wallets.services.grpc.WalletService/withdraw',
        $argument,
        ['\Wallets\Services\Grpc\Withdraw', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Wallets\Services\Grpc\Transfer $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Wallets\Services\Grpc\Transfer
     */
    public function transfer(\Wallets\Services\Grpc\Transfer $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/wallets.services.grpc.WalletService/transfer',
        $argument,
        ['\Wallets\Services\Grpc\Transfer', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Wallets\Services\Grpc\Wallet $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Wallets\Services\Grpc\Wallet
     */
    public function getBalance(\Wallets\Services\Grpc\Wallet $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/wallets.services.grpc.WalletService/getBalance',
        $argument,
        ['\Wallets\Services\Grpc\Wallet', 'decode'],
        $metadata, $options);
    }

}
