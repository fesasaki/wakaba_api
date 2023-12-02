<?php

namespace App\Http\Requests\Common;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'active'=> 'required|numeric',
            'user_type' => 'required|numeric',
            'name' => 'required|string',
            'phone' => 'required|string',
            'email' => ['required', 'string', Rule::unique('users')->ignore($this->id)],
            'username' => ['required', 'string', Rule::unique('users')->ignore($this->id)],
        ];
    }

    public function messages()
    {
        return [
            'user_type.required'  => 'O campo tipo é obrigatório.',
            'user_type.numeric'  => 'O campo tipo deve ser um número.',
            'active.required'  => 'O campo ativo é obrigatório.',
            'active.numeric'  => 'O campo ativo deve ser um número.',
            'name.required'  => 'O campo nome é obrigatório.',
            'email.required'  => 'O campo email é obrigatório.',
            'phone.required'  => 'O campo celular é obrigatório.',
            'email.unique'  => 'Este email já está sendo utilizado.',
            'username.required'  => 'Este usuário é obrigatório.',
            'username.unique'  => 'Este usuário já está sendo utilizado.',
        ];
    }
}
