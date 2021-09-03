<?php
namespace User\Services;

use User\Repository\GatewayServiceRepository;

class GatewayService
{
    private $gateway_services_repository;

    public function __construct(GatewayServiceRepository $gateway_services_repository)
    {
        $this->gateway_services_repository = $gateway_services_repository;
    }

    public function getAllGatewayServices()
    {
        return $this->gateway_services_repository->getAllGatewayServices();
    }


    public function editGatewayService($request)
    {
        return $this->gateway_services_repository->edit($request);
    }
}
