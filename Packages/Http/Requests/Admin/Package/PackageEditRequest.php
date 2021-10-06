<?php

namespace Packages\Http\Requests\Admin\Package;

use Illuminate\Foundation\Http\FormRequest;

class PackageEditRequest extends FormRequest
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
            'package_id' => 'required|exists:packages,id',
            'name' => 'required|string',
            'short_name' => 'required|string',
            'validity_in_days' => 'required|numeric',
            'price' => 'required|string',
            'roi_percentage' => 'required|numeric',
            'direct_percentage' => 'required|numeric',
            'binary_percentage' => 'required|numeric',
            'category_id' => 'required|numeric',
        ];
    }
}
