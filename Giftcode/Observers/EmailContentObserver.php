<?php


namespace Giftcode\Observers;

use Giftcode\Models\EmailContent;

class EmailContentObserver
{
    public function updating(EmailContent $email)
    {
        $data = array_merge($email->getOriginal(),[
            'actor_id' => request()->user->id,
            'email_id' => $email->id,
        ]);
        unset($data['id']);
        $email->histories()->create($data);

    }
}
