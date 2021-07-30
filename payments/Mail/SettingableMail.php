<?php


namespace Payments\Mail;

use Payments\Models\EmailContentSetting;

interface SettingableMail
{
    public function getSetting(): array ;
}
