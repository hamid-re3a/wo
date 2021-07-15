<?php

namespace App\Http\Requests\Front\Order;

use App\Rules\PersianCharacterRule;
use Illuminate\Foundation\Http\FormRequest;

class OrderPriceRequest extends FormRequest
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
        return [
            'items_id' => 'required|array',
//            'items_id.*' => 'exists:products,id',
            'items_qty' => 'required|array',
        ];
    }
}
