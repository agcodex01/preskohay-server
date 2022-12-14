<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
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
            case 'posts.store':
            case 'posts.update':
                return [
                    'title'         => 'required',
                    'description'   => 'required',
                    'image'         => 'required'
                ];
                break;

            case 'product.post':
                return [
                    'products' => 'required|array',
                    'products.*.id' => 'required|exists:products,id',
                ];
                break;
        }


    }
}
