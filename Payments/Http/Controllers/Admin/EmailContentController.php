<?php


namespace Payments\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Wallets\Http\Requests\Admin\UpdateEmailContentRequest;
use Payments\Http\Resources\Admin\EmailContentResource;
use Payments\Models\EmailContentSetting;

class EmailContentController extends Controller
{

    /**
     * Get emails list
     * @group Admin User > Payments > EmailContents
     */
    public function index()
    {
        $emails = EmailContentSetting::all();
        return api()->success(null,EmailContentResource::collection($emails));
    }

    /**
     * Update emails
     * @group Admin User > Payments > EmailContents
     * @param UpdateEmailContentRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(UpdateEmailContentRequest $request)
    {
        $email = EmailContentSetting::query()->where('key',$request->get('key'))->first();
        $email->update($request->except(['key','id','type']));
        $emails = EmailContentSetting::all()->toArray();
        cache(['payments_email_contents' =>  $emails]);

        return api()->success(null, EmailContentResource::make($email->refresh()));
    }
}
