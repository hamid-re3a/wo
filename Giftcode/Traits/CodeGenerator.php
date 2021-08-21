<?php


namespace Giftcode\Traits;


use Giftcode\Models\Giftcode;
use Illuminate\Support\Str;

trait CodeGenerator
{

    private function generateCode()
    {
        $giftcodeModel = new Giftcode();
        $code = $this->makeCode();
        while($giftcodeModel->where('code',$code)->first())
            $code = $this->makeCode();

        return $code;
    }

    private function makeCode()
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
        if(giftcodeGetSetting('has_expiration_date') AND !empty(giftcodeGetSetting('giftcode_lifetime')))
            return now()->addDays(intval(giftcodeGetSetting('giftcode_lifetime')))->toDateTimeString();

        return null;
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
