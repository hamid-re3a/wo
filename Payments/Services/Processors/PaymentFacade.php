<?php


namespace Payments\Services\Processors;


use Illuminate\Support\Facades\Facade;
use Orders\Services\Grpc\Order;
use Payments\Services\Grpc\Invoice;

/**
 * @method static array pay(Invoice $invoice,Order $order = null)
 */

class PaymentFacade extends Facade
{
    public static function getFacadeAccessor()
    {
        return __CLASS__;
    }

    public static function shouldProxyTo($class)
    {
        return app()->singleton(self::getFacadeAccessor(),$class);
    }
}
