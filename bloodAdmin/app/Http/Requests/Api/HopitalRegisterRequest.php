<?php
// app/Http/Requests/Api/HopitalRegisterRequest.php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class HopitalRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // À remplacer par middleware pour Admin seulement
    }

    public function rules(): array
    {
        return [
            'login' => 'required|string|unique:users,login',
            'password' => 'required|min:8|confirmed',
            'telephone' => 'nullable|string',
            'nom' => 'required|string|max:255',
            'ville' => 'required|string|max:255',
            'adresse' => 'nullable|string',
            'email' => 'nullable|email|unique:hopitals,email',
        ];
    }

    public function messages(): array
    {
        return [
            'login.unique' => 'Ce login est déjà utilisé.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'email.unique' => 'Cet email est déjà utilisé.',
        ];
    }
}