<?php
namespace Wallets\database\seeders;

use Wallets\Models\EmailContent;
use Illuminate\Database\Seeder;

class EmailContentSeeder extends Seeder
{
    public function run()
    {
        if(defined('WALLET_EMAIL_CONTENTS') AND is_array(WALLET_EMAIL_CONTENTS)) {
            $emails = [];
            $now = now()->toDateTimeString();
            foreach(WALLET_EMAIL_CONTENTS AS $key => $email)
                $emails[] = [
                    'key' => $key,
                    'is_active' => $email['is_active'],
                    'subject' => $email['subject'],
                    'from' => $email['from'],
                    'from_name' => $email['from_name'],
                    'body' => $email['body'],
                    'variables' => $email['variables'],
                    'variables_description' => $email['variables_description'],
                    'type' => $email['type'],
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            EmailContent::insert($emails);
            cache(['wallet_email_contents' => $emails]);
        }
    }

}
