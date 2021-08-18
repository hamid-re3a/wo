<?php


namespace Giftcode\Traits;


use Illuminate\Support\Str;

trait CodeGenerator
{

    public function generateCode()
    {
        $characters = collect(str_split(giftcodeGetSetting('characters')));
        $length = giftcodeGetSetting('length');
        $mask = giftcodeGetSetting('mask');
        $code = $this->getPrefix();

        for($i = 0; $i < $length; $i++) {
            $mask =  Str::replaceFirst('*', $characters->random(1)->first(), $mask);
        }

        $code .= $mask;
        $code .= $this->getPostfix();
        return [$code,$this->getExpirationDate()];

    }

    private function getExpirationDate()
    {
        if(giftcodeGetSetting('has_expiration_date'))
            return now()->addDays(intval(giftcodeGetSetting('giftcode_lifetime')))->toDateTimeString();
    }

    private function getSeparator()
    {
        return giftcodeGetSetting('separator');
    }

    private function getPrefix()
    {
        $usePostFix = giftcodeGetSetting('use_prefix');
        $prefix = giftcodeGetSetting('prefix');
        if($usePostFix)
            return $prefix . $this->getSeparator();

        return null;
    }

    private function getPostfix()
    {
        $usePrefix = giftcodeGetSetting('use_postfix');
        $postfix = giftcodeGetSetting('postfix');

        if($usePrefix)
            return $this->getSeparator() . $postfix;

        return null;
    }
}
