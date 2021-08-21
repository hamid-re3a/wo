<?php
namespace Giftcode\database\seeders;

use Giftcode\Models\EmailContent;
use Giftcode\Models\Package;
use Giftcode\Models\Setting;
use Illuminate\Database\Seeder;

class EmailContentSeeder extends Seeder
{
    public function run()
    {
        if(defined('EMAIL_CONTENTS') AND is_array(EMAIL_CONTENTS)) {
            foreach(EMAIL_CONTENTS AS $key => $email) {
                EmailContent::create([
                    'key' => $key,
                    'is_active' => $email['is_active'],
                    'subject' => $email['subject'],
                    'from' => $email['from'],
                    'from_name' => $email['from_name'],
                    'body' => $email['body'],
                    'variables' => $email['variables'],
                    'variables_description' => $email['variables_description'],
                    'type' => $email['type']
                ]);
            }
        }
    }

}
