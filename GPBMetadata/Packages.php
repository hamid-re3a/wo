<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: packages.proto

namespace GPBMetadata;

class Packages
{
    public static $is_initialized = false;

    public static function initOnce() {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();

        if (static::$is_initialized == true) {
          return;
        }
        $pool->internalAddGeneratedFile(
            '
�
packages.protopackages.services.grpc"
Id

id ("C
PackageCheck
package_index_id (
package_to_buy_id ("B
Acknowledge
status (
message (	

created_at (	"�
Package

id (
name (	

short_name (	
validity_in_days (
price (
roi_percentage (
direct_percentage (
binary_percentage (
category_id	 (

deleted_at
 (	

created_at (	

updated_at (	F
IndirectCommission (2*.packages.services.grpc.IndirectCommission"7
IndirectCommission
level (

percentage (2�
PackagesServiceL
packageById.packages.services.grpc.Id.packages.services.grpc.Package" p
!packageIsInBiggestPackageCategory$.packages.services.grpc.PackageCheck#.packages.services.grpc.Acknowledge" bproto3'
        , true);

        static::$is_initialized = true;
    }
}

