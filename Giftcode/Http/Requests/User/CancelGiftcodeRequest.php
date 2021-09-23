<?php

namespace Giftcode\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class CancelGiftcodeRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user_id = !empty(request()->header('X-user-id')) ?  request()->header('X-user-id') : null;
        return [
            'id' => 'required|integer|exists:giftcodes,uuid,user_id,' . $user_id
        ];
    }

}
