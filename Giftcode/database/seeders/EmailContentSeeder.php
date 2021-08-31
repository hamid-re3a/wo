<?php
namespace Giftcode\database\seeders;

use Giftcode\Models\EmailContent;
use Illuminate\Database\Seeder;

class EmailContentSeeder extends Seeder
{
    public function run()
    {
        if(defined('EMAIL_CONTENTS') AND is_array(EMAIL_CONTENTS)) {
            $emails = [];
            foreach(EMAIL_CONTENTS AS $key => $email)
                $emails[] = [
                    'key' => $key,
                    'is_active' => $email['is_active'],
                    'subject' => $email['subject'],
                    'from' => env('MAIL_USERNAME', $email['from']),
                    'from_name' => $email['from_name'],
                    'body' => $email['body'],
                    'variables' => $email['variables'],
                    'variables_description' => $email['variables_description'],
                    'type' => $email['type']
                ];
            EmailContent::insert($emails);
            cache(['giftcode_email_contents' => $emails]);
        }
    }

}
