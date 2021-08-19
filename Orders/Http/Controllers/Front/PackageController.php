<?php


namespace Orders\Http\Controllers\Front;


use App\Http\Controllers\Controller;
use Orders\Http\Resources\PackageResource;
use Packages\Services\PackageService;

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
        $orders = request()->order_user->paidOrders()->whereHas('packages')->with('packages')->get();
        foreach($orders AS $order) {
            foreach($order->packages AS $key => $package) {
                $packageDetails = $this->getPackage($package->package_id);
                $packageExpireDate = $order->created_at->addDays($packageDetails->getValidityInDays());
                if( $packageExpireDate > now() )
                    $packages->push([
                        'id' => $packageDetails->getId(),
                        'name' => $packageDetails->getName(),
                        'short_name' => $packageDetails->getShortName(),
                        'expire_date' => $packageExpireDate->timestamp
                    ]);
            }
        }
        return api()->success(null,PackageResource::collection($packages));
    }

    /**
     * Has a valid package
     * @group
     * Public User > Orders
     */
    public function hasValidPackage()
    {
        $orders = request()->order_user->paidOrders()->whereHas('packages')->with('packages')->get();
        foreach($orders AS $order) {
            foreach($order->packages AS $key => $package) {
                $packageDetails = $this->getPackage($package->package_id);
                $packageExpireDate = $order->created_at->addDays($packageDetails->getValidityInDays());
                if( $packageExpireDate > now() )
                    return api()->success(true,null);

            }
        }
        return api()->success(false,null);
    }

    private function getPackage($id)
    {
        $request = new \Packages\Services\Id();
        $request->setId($id);
        return $this->packageService->packageFullById($request);
    }

}
