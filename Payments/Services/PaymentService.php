<?php

namespace Payments\Services;

use Payments\Repository\PaymentCurrencyRepository;
use Payments\Repository\PaymentDriverRepository;
use Payments\Repository\PaymentTypesRepository;
use Payments\Services\Grpc\PaymentDriver;

class PaymentService
{
    private $payment_type_repository;
    private $payment_currency_repository;
    private $payment_driver_repository;

    public function __construct(PaymentTypesRepository $payment_type_repository, PaymentDriverRepository $payment_driver_repository, PaymentCurrencyRepository $payment_currency_repository)
    {
        $this->payment_type_repository = $payment_type_repository;
        $this->payment_driver_repository = $payment_driver_repository;
        $this->payment_currency_repository = $payment_currency_repository;
    }




    public function getPaymentCurrencies($filter = '')
    {
        return $this->payment_currency_repository->getAll($filter);
    }

    public function getAllPaymentTypes()
    {
        return $this->payment_type_repository->getAll();
    }
    public function getActivePaymentTypes()
    {
        return $this->payment_type_repository->getAllActives();
    }


    public function createPaymentCurrency($payment_currency)
    {
        return $this->payment_currency_repository->create($payment_currency);
    }


    public function updatePaymentCurrency($payment_currency)
    {
        return $this->payment_currency_repository->update($payment_currency);
    }


    public function deletePaymentCurrency($payment_currency)
    {
        return $this->payment_currency_repository->delete($payment_currency);
    }

    public function getPaymentDrivers()
    {
        return $this->payment_driver_repository->getAll();
    }

    public function createPaymentDriver($payment_driver)
    {
        return $this->payment_driver_repository->create($payment_driver);
    }

    public function updatePaymentDriver($payment_driver)
    {
        return $this->payment_driver_repository->update($payment_driver);
    }

    public function deletePaymentDriver(PaymentDriver $payment_driver)
    {
        return $this->payment_driver_repository->delete($payment_driver);
    }

    public function createPaymentType($payment_type)
    {
        return $this->payment_type_repository->create($payment_type);
    }

    public function updatePaymentType($payment_type)
    {
        return $this->payment_type_repository->update($payment_type);
    }

    public function deletePaymentType($payment_type)
    {
        return $this->payment_type_repository->delete($payment_type);
    }
}
