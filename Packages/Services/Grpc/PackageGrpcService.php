<?php


namespace Packages\Services\Grpc;

use Mix\Grpc;
use Mix\Grpc\Context;
use Packages\Services\PackageService;

class PackageGrpcService implements \Packages\Services\Grpc\PackagesServiceInterface
{
    /**@var $package_service PackageService */
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
        $id = new Id;
        $id->setId($request->getPackageIndexId());

        $id_to_buy = new Id;
        $id_to_buy->setId($request->getPackageToBuyId());

        $index_package = $this->package_service->packageById($id);
        $to_buy_package = $this->package_service->packageById($id_to_buy);

        if ($to_buy_package->getPrice() >= $index_package->getPrice()) {
            return $ack;
        } else if ($to_buy_package->getCategoryId() == $index_package->getCategoryId()) {
            return $ack;
        }
        $ack->setStatus(false);
        return $ack;
    }
}
