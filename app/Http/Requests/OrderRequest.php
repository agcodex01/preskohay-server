<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
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
        switch ($req->action['as']) {
            case 'order.product':
                return [
                    'farmer_id' => 'required',
                    'drop_off' => 'required',
                    'products'            => 'required|array',
                    'products.*.id'       => 'required|exists:products,id',
                    'products.*.quantity' => 'required|numeric',
                    'products.*.sub_total' => 'required|numeric'
                ];
                break;

            case 'order.product.update':
                return [
                    'shipping_fee' => 'required|numeric'
                ];
                break;
            case 'order.user.update':
                return [
                    'status' => 'required'
                ];
                break;
        }

    }
}
