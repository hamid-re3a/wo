<?php


namespace Giftcode\Traits;


use Giftcode\Models\Giftcode;
use Illuminate\Support\Str;

trait CodeGenerator
{

    public function generateCode()
    {
        try {
            $giftcodeModel = new Giftcode();
            $code = $this->makeCode();

            while($giftcodeModel->where('code',$code)->first())
                $code = $this->makeCode();


            return [$code,$this->getExpirationDate()];
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }

    private function makeCode()
    {
        $characters = collect(str_split(giftcodeGetSetting('characters')));
        $length = giftcodeGetSetting('length');
        $mask = config('giftcode.mask');
        $code = $this->getPrefix();

        for($i = 0; $i < $length; $i++) {
            $mask =  Str::replaceFirst('*', $characters->random(1)->first(), $mask);
        }
        $code .= $mask;
        $code .= $this->getPostfix();
        return $code;

    }

    private function getExpirationDate()
    {
        if(giftcodeGetSetting('has_expiration_date') AND !empty(giftcodeGetSetting('giftcode_lifetime')))
            return now()->addDays(intval(giftcodeGetSetting('giftcode_lifetime')))->toDateTimeString();

        return null;
    }

    private function getSeparator()
    {
        if(!empty(giftcodeGetSetting('separator')))
            return giftcodeGetSetting('separator');

        return '-';
    }

    private function getPrefix()
    {
        $usePostFix = giftcodeGetSetting('use_prefix');
        $prefix = giftcodeGetSetting('prefix');
        if($usePostFix AND !empty($prefix))
            return $prefix . $this->getSeparator();
        return null;
    }

    private function getPostfix()
    {
        $usePrefix = giftcodeGetSetting('use_postfix');
        $postfix = giftcodeGetSetting('postfix');

        if($usePrefix AND !empty($postfix))
            return $this->getSeparator() . $postfix;

        return null;
    }
}
