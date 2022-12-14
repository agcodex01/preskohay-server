<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // $user = \Auth::user();

        // return $user ? true : false;

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name'              => 'required|unique:products',
            'unit'              => 'required',
            'stocks'            => 'required',
            'category'          => 'required',
            'description'       => 'required',
            'price_per_unit'    => 'required'
        ];
    }
}
