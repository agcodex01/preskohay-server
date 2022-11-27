<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
        $userId = $this->user ? $this->user->id : '';
        if ($req->action['as'] == 'organizations.update') {
            $userId = $this->organization ? $this->organization->id : '';
        }
        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|unique:users,email,' . $userId,
            'age' => 'sometimes',
            'birthdate' => 'sometimes',
            'password' => 'required|min:8',
            'address' => 'sometimes',
            'profile_image' => 'sometimes',
            'user_role' => 'required|in:admin,user,farmer,driver',
            'contact_number' => 'required|regex:/(09)[0-9]{9}/|max:11',
        ];

        if ($userId) {
            unset($rules['password']);
        }

        return $rules;
    }
}
