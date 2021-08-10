<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: payments.proto

namespace GPBMetadata;

class Payments
{
    public static $is_initialized = false;

    public static function initOnce() {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();

        if (static::$is_initialized == true) {
          return;
        }
        \GPBMetadata\User::initOnce();
        \GPBMetadata\Orders::initOnce();
        $pool->internalAddGeneratedFile(
            '
�
payments.protopayments.servicesorders.proto"
Id

id ("
EmptyObject"v
PaymentType

id (
name (	
	is_active (

deleted_at (	

created_at (	

updated_at (	"E
PaymentTypes5
payment_types (2.payments.services.PaymentType"x
PaymentDriver

id (
name (	
	is_active (

deleted_at (	

created_at (	

updated_at (	"�
PaymentCurrency

id (
name (	
	is_active (

deleted_at (	

created_at (	

updated_at (	8
payment_driver (2 .payments.services.PaymentDriver"S
PaymentCurrencies>
payment_currencies (2".payments.services.PaymentCurrency"�
Invoice
order_id (
amount (
transaction_id (	
checkout_link (	
status (	
additional_status (	
paid_amount (

due_amount (
is_paid	 (
expiration_time
 (	
payment_type (	
payment_currency (	
payment_driver (	

deleted_at (	

created_at (	

updated_at (	%
order (2.orders.services.Order!
user (2.user.services.User2�
PaymentsServiceE
getInvoiceById.payments.services.Id.payments.services.Invoice" ?
pay.payments.services.Invoice.payments.services.Invoice" ^
getPaymentCurrencies.payments.services.EmptyObject$.payments.services.PaymentCurrencies" T
getPaymentTypes.payments.services.EmptyObject.payments.services.PaymentTypes" bproto3'
        , true);

        static::$is_initialized = true;
    }
}

