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
        $req = Request::route();
        switch ($req->action['as']) {
            case 'orders.store':
                return [
                    'data' => 'required'
                ];
                break;

            case 'orders.update':
                return [
                    'quantity' => 'required'
                ];
                break;
        }

    }
}
