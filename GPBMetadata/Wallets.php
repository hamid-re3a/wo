<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: wallets.proto

namespace GPBMetadata;

class Wallets
{
    public static $is_initialized = false;

    public static function initOnce() {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();

        if (static::$is_initialized == true) {
          return;
        }
        $pool->internalAddGeneratedFile(
            '
�
wallets.protowallets.services"�
Deposit
user_id (
type (	
sub_type (	
amount (
description (	
wallet_name (	
service_name (	

payload_id (
	confirmed	 ("�
Withdraw
user_id (
type (	
sub_type (	
amount (
description (	
wallet_name (	
service_name (	

payload_id (
	confirmed	 ("�
Transfer
from_user_id (

to_user_id (
amount (
description (	
from_wallet_name (	
to_wallet_name (	
service_name (	

payload_id (
	confirmed	 ("D
Wallet

id (
user_id (
balance (
name (	2�
WalletServiceA
deposit.wallets.services.Deposit.wallets.services.Deposit" D
withdraw.wallets.services.Withdraw.wallets.services.Withdraw" D
transfer.wallets.services.Transfer.wallets.services.Transfer" B

getBalance.wallets.services.Wallet.wallets.services.Wallet" bproto3'
        , true);

        static::$is_initialized = true;
    }
}

