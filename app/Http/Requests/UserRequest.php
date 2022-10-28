<?php

namespace App\Http\Requests;

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
        $userId = $this->user ? $this->user->id : '';
        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|unique:users,email,'. $userId,
            'age' => 'required',
            'birthdate' => 'required',
            'password' => 'required|min:8',
            'address' => 'required',
            'user_role' => 'required',
            'contact_number' => 'required'
        ];

        if ($userId) {
            unset($rules['password']);
        }

        return $rules;
    }
}
