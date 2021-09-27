<?php

namespace Packages\Http\Requests\Admin\Package;

use Illuminate\Foundation\Http\FormRequest;

class CategoryCommissionEditRequest extends FormRequest
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
            'key' => 'required|string|exists:categories,key',
            'level' => 'required|numeric',
            'percentage' => 'required|numeric',
        ];
    }
}
