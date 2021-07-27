<?php


namespace Packages\Services;

use Illuminate\Database\Capsule\Manager as Capsule;
use Mix\Grpc\Context;
use Packages\PackageConfigure;

class PackageService extends GrpcMainService implements PackagesServiceInterface
{
    /**
     * @inheritDoc
     */
    public function packageById(Context $context, Id $request): Package
    {
        $packeage = \Packages\Models\Package::query()->find($request->getId());
        if (is_null($packeage))
            return new Package();
        $response_package = new Package();
        $response_package->setName($packeage->name);
        $response_package->setShortName($packeage->short_name);
        $response_package->setValidityInDays((int)$packeage->validity_in_days);
        $response_package->setPrice((double)$packeage->price);
        $response_package->setRoiPercentage((int)$packeage->roi_percentage);
        $response_package->setDirectPercentage((int)$packeage->direct_percentage);
        $response_package->setBinaryPercentage((int)$packeage->binary_percentage);
        $response_package->setCategoryId((int)$packeage->category_id);
        return $response_package;
    }
}
