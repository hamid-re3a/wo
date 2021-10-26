<?php

namespace Payments\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RefundOverPaidInvoiceRequest extends FormRequest
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
            'id' => [
                'required',
                Rule::exists('invoices','transaction_id')
                    ->where('additional_status','PaidOver')
                    ->where('payable_type','Order')
                    ->whereNull('is_refund_at')
            ]
        ];
    }

    public function messages()
    {
        return [
            'transaction_id.exists' => 'selected invoice is not valid'
        ];
    }


}
