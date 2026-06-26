<?php

// app/Http/Requests/Api/HopitalLoginRequest.php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class HopitalLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public
    }

    public function rules(): array
    {
        return [
            'hopital_code' => 'required|string|exists:hopitals,code_hopital',
            'password' => 'required|min:6',
        ];
    }

    //message d'errreur de validation
    public function messages(): array
    {
        return [
            'hopital_code.required' => 'Le code hôpital est requis.',
            'hopital_code.exists' => 'Cet hôpital n\'existe pas.',
            'password.required' => 'Le mot de passe est requis.',
        ];
    }
}