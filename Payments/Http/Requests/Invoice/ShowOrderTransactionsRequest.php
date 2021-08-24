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
            'order_id' => 'required|integer|exists:invoices,order_id',
        ];
    }
}
