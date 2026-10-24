<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId    = $this->route('user') ? $this->route('user')->id : null;
        $isUpdate  = $this->isMethod('PUT') || $this->isMethod('PATCH');

        $rules = [
            'name'  => 'required|string|max:255',
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'role'  => 'required|in:admin,owner,staff',
        ];

        if (! $isUpdate) {
            $rules['password'] = ['required', 'confirmed', Password::min(8)];
        } else {
            $rules['password'] = ['nullable', 'confirmed', Password::min(8)];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Nama harus diisi.',
            'email.required'    => 'Email harus diisi.',
            'email.unique'      => 'Email sudah digunakan.',
            'role.required'     => 'Role harus dipilih.',
            'role.in'           => 'Role tidak valid.',
            'password.required' => 'Password harus diisi.',
            'password.confirmed'=> 'Konfirmasi password tidak cocok.',
        ];
    }
}
