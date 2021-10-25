<?php


namespace Orders\Http\Controllers\Front;


use App\Http\Controllers\Controller;
use Orders\Http\Resources\PackageResource;
use Packages\Services\PackageService;
use User\Models\User;

class PackageController extends Controller
{
    private $packageService;

    public function __construct()
    {
        $this->packageService = app(PackageService::class);
    }

    /**
     * Get paid packages
     * @group
     * Public User > Orders
     */
    public function paidPackages()
    {
        $packages = collect();
        $orders = auth()->user()->paidOrders()->get();
        foreach ($orders AS $order) {
            $packageDetails = $this->getPackage($order->package_id);
            $packageExpireDate = $order->created_at->addDays($packageDetails->getValidityInDays());
            if ($packageExpireDate > now())
                $packages->push([
                    'id' => $packageDetails->getId(),
                    'name' => $packageDetails->getName(),
                    'short_name' => $packageDetails->getShortName(),
                    'is_paid_at' => $order->is_paid_at->timestamp,
                    'expire_date' => $packageExpireDate->timestamp
                ]);
        }
        return api()->success(null, PackageResource::collection($packages));
    }


    /**
     * Has paid package
     * @group
     * Public User > Orders
     */
    public function hasPaidPackage()
    {
        /**
         * @var $user User
         */
        $user = auth()->user();
        if($user->paidOrders()->count())
            return api()->success(null, [
                'has_paid_package' => true
            ]);
        return api()->success(null, [
            'has_paid_package' => false
        ]);
    }

    /**
     * Has valid package
     * @group
     * Public User > Orders
     */
    public function hasValidPackage()
    {
        /**
         * @var $user User
         */
        $user = auth()->user();
        $orders = $user->paidOrders()->get();
        foreach ($orders AS $order) {
            $packageDetails = $this->getPackage($order->package_id);
            $packageExpireDate = $order->created_at->addDays($packageDetails->getValidityInDays());
            if ($packageExpireDate > now())
                return api()->success(null, [
                    'has_valid_package' => true
                ]);
        }
        return api()->success(null, [
            'has_valid_package' => false
        ]);
    }

    private function getPackage($id)
    {
        $request = new \Packages\Services\Grpc\Id();
        $request->setId($id);
        return $this->packageService->packageFullById($request);
    }

}
