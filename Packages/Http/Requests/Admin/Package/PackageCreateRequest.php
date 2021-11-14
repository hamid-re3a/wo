<?php

namespace Packages\Http\Requests\Admin\Package;

use Illuminate\Foundation\Http\FormRequest;

class PackageCreateRequest extends FormRequest
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
            'package_exactly' => 'sometimes|boolean',
            'name' => 'required|string',
            'short_name' => 'required|string',
            'validity_in_days' => 'required|numeric',
            'price' => 'required|numeric',
            'roi_percentage' => 'required|numeric|min:0|max:100',
            'direct_percentage' => 'required|numeric|min:0|max:100',
            'binary_percentage' => 'required|numeric|min:0|max:100',
            'category_id' => 'required|numeric|exists:categories,id',
        ];
    }
}
