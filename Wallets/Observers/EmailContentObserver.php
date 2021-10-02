<?php


namespace Wallets\Observers;

use Wallets\Models\EmailContent;

class EmailContentObserver
{
    public function updating(EmailContent $email)
    {
        $data = array_merge($email->getOriginal(),[
            'actor_id' => auth()->user()->id,
            'email_id' => $email->id,
        ]);
        unset($data['id']);
        $email->histories()->create($data);

    }
}
