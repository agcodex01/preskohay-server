<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class ApplicationRequest extends FormRequest
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
        if ($req->action['as'] == 'drivers.motor') {
            return [
                'motor_img' => 'required'
            ];
        } else {
            return [
                'license_img' => 'required'
            ];
        }
    }
}
