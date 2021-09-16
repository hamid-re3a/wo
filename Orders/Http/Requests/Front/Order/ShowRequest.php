<?php

namespace Orders\Http\Requests\Front\Order;

use Illuminate\Foundation\Http\FormRequest;

class ShowRequest extends FormRequest
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
        $id = auth()->user()->id ? auth()->user()->id : null;
        return [
            'id' => 'required|integer|exists:orders,id,user_id,' . $id
        ];
    }


}
