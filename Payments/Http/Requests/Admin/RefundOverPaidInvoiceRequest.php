<?php

namespace Payments\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

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
            'transaction_id' => 'required|exists:invoices,transaction_id,additional_status,PaidOver,payable_type,Order,is_refund_at,null',
        ];
    }

    public function messages()
    {
        return [
            'transaction_id.exists' => 'selected invoice is not valid'
        ];
    }


}
