<?php


namespace Payments\Http\Requests\Invoice;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShowOrderTransactionsRequest extends FormRequest
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
            'transaction_id' => 'required_without:order_id|exists:invoices,transaction_id',
            'order_id' => 'required_without:transaction_id|exists:invoices,payable_id,payable_type,Order',
        ];
    }
}
