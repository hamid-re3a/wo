<?php


namespace Packages\Services;

use Illuminate\Support\Carbon;
use Packages\Repository\PackageRepository;
use Packages\Services\Grpc\Id;
use Packages\Services\Grpc\Package;

class PackageService implements PackagesServiceInterface
{
    private $package_repository;

    public function __construct(PackageRepository $package_repository)
    {
        $this->package_repository = $package_repository;
    }

    /**
     * @inheritDoc
     */
    public function packageById(Id $id): Package
    {
        $package = $this->package_repository->getById($id);
        if (is_null($package))
            return new Package();
        return $this->setPackage($package);
    }

    /**
     * @inheritDoc
     */
    public function packageFullById(Id $id): Package
    {
        $package = $this->package_repository->getById($id);
        if (is_null($package))
            return new Package();

        if(empty($package->validity_in_days))
            $package->validity_in_days = $package->category->package_validity_in_days;

        if(empty($package->roi_percentage))
            $package->roi_percentage = $package->category->roi_percentage;

        if(empty($package->direct_percentage))
            $package->direct_percentage = $package->category->direct_percentage;

        if(empty($package->binary_percentage))
            $package->binary_percentage = $package->category->binary_percentage;
        return $this->setPackage($package);
    }



    /**
     * @param $packeage
     * @return Package
     */
    private function setPackage($packeage): Package
    {
        $response_package = new Package();
        $response_package->setId($packeage->id);
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

    public function editPackage(Id $id, Package $package): \Packages\Models\Package
    {
        return $this->package_repository->edit($id, $package);
    }

    public function getPackages()
    {
        return $this->package_repository->getAll();
    }

    public function getCountPackageByDate($type)
    {
        switch ($type) {
            case "week":
                $data_count = $this->package_repository->getCountAllPackageByMonth(Carbon::now()->subDay(7),Carbon::now());
                $array_count = [];
                foreach (range(1, 7) as $month) {
                    $array_count[] = ["time" =>Carbon::now()->startOfDay()->subDay($month)->timestamp ,"a" => $data_count->where('category_id',3)->whereBetween('created_at',[Carbon::now()->startOfDay()->subDay($month),Carbon::now()->endOfDay()->subDay($month)])->count()];
                    $array_count[] = ["time" =>Carbon::now()->startOfDay()->subDay($month)->timestamp ,"b" => $data_count->where('category_id',1)->whereBetween('created_at',[Carbon::now()->startOfDay()->subDay($month),Carbon::now()->endOfDay()->subDay($month)])->count()];
                    $array_count[] = ["time" =>Carbon::now()->startOfDay()->subDay($month)->timestamp ,"p" => $data_count->where('category_id',4)->whereBetween('created_at',[Carbon::now()->startOfDay()->subDay($month),Carbon::now()->endOfDay()->subDay($month)])->count()];
                    $array_count[] = ["time" =>Carbon::now()->startOfDay()->subDay($month)->timestamp,"i" => $data_count->where('category_id',2)->whereBetween('created_at',[Carbon::now()->startOfDay()->subDay($month),Carbon::now()->endOfDay()->subDay($month)])->count()];
                }
                return $array_count;
                break;
            case "month":
                $data_count = $this->package_repository->getCountAllPackageByMonth(Carbon::now()->endOfMonth()->subMonth(6), Carbon::now());
                $array_count = [];
                foreach (range(6, 12) as $month) {
                    $array_count[] = ["time" =>Carbon::now()->startOfMonth()->subMonth($month)->timestamp ,"a" => $data_count->where('category_id',3)->whereBetween('created_at',[Carbon::now()->startOfMonth()->subMonth($month),Carbon::now()->endOfMonth()->subMonth($month)])->count()];
                    $array_count[] = ["time" =>Carbon::now()->startOfMonth()->subMonth($month)->timestamp ,"b" => $data_count->where('category_id',1)->whereBetween('created_at',[Carbon::now()->startOfMonth()->subMonth($month),Carbon::now()->endOfMonth()->subMonth($month)])->count()];
                    $array_count[] = ["time" =>Carbon::now()->startOfMonth()->subMonth($month)->timestamp ,"p" => $data_count->where('category_id',4)->whereBetween('created_at',[Carbon::now()->startOfMonth()->subMonth($month),Carbon::now()->endOfMonth()->subMonth($month)])->count()];
                    $array_count[] = ["time" =>Carbon::now()->startOfMonth()->subMonth($month)->timestamp,"i" => $data_count->where('category_id',2)->whereBetween('created_at',[Carbon::now()->startOfMonth()->subMonth($month),Carbon::now()->endOfMonth()->subMonth($month)])->count()];
                }
                return $array_count;
                break;
            case "year":
                $data_count = $this->package_repository->getCountAllPackageByMonth(Carbon::now()->endOfYear()->subYear(3), Carbon::now());
                $array_count = [];
                foreach (range(1, 3) as $year) {
                    $array_count[] = ["time" =>Carbon::now()->startOfYear()->subYear($year)->timestamp ,"a" => $data_count->where('category_id',3)->whereBetween('created_at',[Carbon::now()->startOfYear()->subYear($year),Carbon::now()->endOfYear()->subYear($year)])->count()];
                    $array_count[] = ["time" =>Carbon::now()->startOfYear()->subYear($year)->timestamp ,"b" => $data_count->where('category_id',1)->whereBetween('created_at',[Carbon::now()->startOfYear()->subYear($year),Carbon::now()->endOfYear()->subYear($year)])->count()];
                    $array_count[] = ["time" =>Carbon::now()->startOfYear()->subYear($year)->timestamp ,"p" => $data_count->where('category_id',4)->whereBetween('created_at',[Carbon::now()->startOfYear()->subYear($year),Carbon::now()->endOfYear()->subYear($year)])->count()];
                    $array_count[] = ["time" =>Carbon::now()->startOfYear()->subYear($year)->timestamp,"i" => $data_count->where('category_id',2)->whereBetween('created_at',[Carbon::now()->startOfYear()->subYear($year),Carbon::now()->endOfYear()->subYear($year)])->count()];
                }
                return $array_count;
                break;
        }

    }
}
