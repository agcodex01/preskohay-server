<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
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
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $req = Request::route();
        $response = [
            'name'              => 'required',
            'unit'              => 'required',
            'image'             => 'required',
            'stocks'            => 'required|numeric',
            'category'          => 'required',
            'description'       => 'required',
            'price_per_unit'    => 'required|numeric',
            'estimated_harvest_date' => 'required',
        ];
        if ($req->action['as'] == 'products.store') {
            $response['name'] = 'required|unique:products';
        }

        return $response;

    }
}
