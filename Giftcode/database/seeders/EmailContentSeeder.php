<?php

namespace Giftcode\database\seeders;

use Giftcode\Models\EmailContent;
use Illuminate\Database\Seeder;

class EmailContentSeeder extends Seeder
{
    public function run()
    {
        if (defined('GIFTCODE_EMAIL_CONTENTS') AND is_array(GIFTCODE_EMAIL_CONTENTS)) {

            $now = now()->toDateTimeString();
            foreach (GIFTCODE_EMAIL_CONTENTS AS $key => $email) {
                if (filter_var(env('MAIL_FROM', $email['from']), FILTER_VALIDATE_EMAIL))
                    $from = env('MAIL_FROM', $email['from']);
                else
                    $from = $email['from'];
                EmailContent::query()->firstOrCreate(
                    ['key' => $key],
                    [
                        'is_active' => $email['is_active'],
                        'subject' => $email['subject'],
                        'from' => $from,
                        'from_name' => $email['from_name'],
                        'body' => $email['body'],
                        'variables' => $email['variables'],
                        'variables_description' => $email['variables_description'],
                        'type' => $email['type'],
                        'created_at' => $now,
                        'updated_at' => $now
                    ]);
            }
            cache(['giftcode_email_contents' => GIFTCODE_EMAIL_CONTENTS]);
        }
    }

}
