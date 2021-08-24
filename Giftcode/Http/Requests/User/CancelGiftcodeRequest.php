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
        $user_id = !empty(request()->user) ?  request()->user->id : null;
        return [
            'id' => 'required|uuid|exists:giftcodes,uuid,user_id,' . $user_id
        ];
    }

}