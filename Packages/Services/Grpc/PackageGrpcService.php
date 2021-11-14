<?php


namespace Packages\Services\Grpc;

use Mix\Grpc;
use Mix\Grpc\Context;
use Packages\Services\PackageService;

class PackageGrpcService implements \Packages\Services\Grpc\PackagesServiceInterface
{
    /**@var $package_service PackageService*/
    private $package_service;

    public function __construct()
    {
        $this->package_service = app(PackageService::class);
    }

    /**
     * @inheritDoc
     */
    public function packageById(Context $context, Id $id): Package
    {
        return $this->package_service->packageFullById($id);
    }

    /**
     * @inheritDoc
     */
    public function packageIsInBiggestPackageCategory(Context $context, PackageCheck $request): Acknowledge
    {
        $ack = new Acknowledge;
        $ack->setStatus(true);
        $index_package = $this->package_service->packageById($request->getPackageIndexId());
        $to_buy_package = $this->package_service->packageById($request->getPackageToBuyId());

        if($to_buy_package->getPrice() >= $index_package->getPrice()){
            return $ack;
        } else if($to_buy_package->getCategoryId() == $index_package->getCategoryId()){
            return $ack;
        }
        $ack->setStatus(false);
        return  $ack;
    }
}
