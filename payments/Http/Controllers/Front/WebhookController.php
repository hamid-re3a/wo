<?php

namespace Payments\Http\Controllers\Front;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Payments\Jobs\WebhookJob;
use Payments\Models\PaymentCurrency;

class WebhookController extends Controller
{
    /**
     * Webhook Route
     * @group
     * Webhook
     *
     * ignore
     */
    public function index(Request $request)
    {
        WebhookJob::dispatch($request->getContent());
        return api()->success();
    }
}
