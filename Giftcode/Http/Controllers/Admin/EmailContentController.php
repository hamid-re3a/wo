<?php


namespace Giftcode\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Wallets\Http\Requests\Admin\UpdateEmailContentRequest;
use Giftcode\Http\Resources\Admin\EmailContentResource;
use Giftcode\Models\EmailContent;

class EmailContentController extends Controller
{

    /**
     * Get emails list
     * @group Admin User > Giftcode > EmailContents
     */
    public function index()
    {
        $emails = EmailContent::all();
        return api()->success(null,EmailContentResource::collection($emails));
    }

    /**
     * Update emails
     * @group Admin User > Giftcode > EmailContents
     * @param UpdateEmailContentRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(UpdateEmailContentRequest $request)
    {
        $email = EmailContent::query()->where('key',$request->get('key'))->first();
        $email->update($request->except(['key','id','type']));
        $emails = EmailContent::all()->toArray();
        cache(['wallet_email_contents' =>  $emails]);

        return api()->success(null, EmailContentResource::make($email->refresh()));
    }
}
