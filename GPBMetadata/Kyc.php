<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: kyc.proto

namespace GPBMetadata;

class Kyc
{
    public static $is_initialized = false;

    public static function initOnce() {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();

        if (static::$is_initialized == true) {
          return;
        }
        \GPBMetadata\User::initOnce();
        $pool->internalAddGeneratedFile(
            '
�
	kyc.protoKyc.services.grpc"B
Acknowledge
status (
message (	

created_at (	2Z

KycServiceL
checkKYCStatus.user.services.grpc.User.Kyc.services.grpc.Acknowledge" bproto3'
        , true);

        static::$is_initialized = true;
    }
}

