<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class ValidationService
{
    /**
     * Validate data based on the given type
     */
    public function validate(array $data, string $type)
    {
        $rules = $this->getRules($type);
        $messages = $this->getMessages();
        
        return Validator::make($data, $rules, $messages);
    }

    /**
     * Get validation rules based on type
     */
    protected function getRules(string $type): array
    {
        return match($type) {
            'login' => [
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ],
            'user' => [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'confirmed', Password::defaults()],
                'date_of_birth' => ['nullable', 'date', 'before:today'],
                'gender' => ['nullable', 'string', 'in:M,F,O'],
                'contact_info' => ['nullable', 'string', 'max:255'],
            ],
            default => throw new \InvalidArgumentException("Unknown validation type: {$type}")
        };
    }

    /**
     * Get custom validation messages
     */
    protected function getMessages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser texto.',
            'email' => 'El campo :attribute debe ser un correo electrónico válido.',
            'max' => 'El campo :attribute no debe exceder :max caracteres.',
            'unique' => 'El :attribute ya está registrado.',
            'confirmed' => 'La confirmación de :attribute no coincide.',
            'date' => 'El campo :attribute debe ser una fecha válida.',
            'before' => 'El campo :attribute debe ser una fecha anterior a :date.',
            'in' => 'El valor seleccionado para :attribute no es válido.',
            'password' => [
                'min' => 'La contraseña debe tener al menos :min caracteres.',
                'mixed' => 'La contraseña debe contener al menos una letra mayúscula y una minúscula.',
                'numbers' => 'La contraseña debe contener al menos un número.',
                'symbols' => 'La contraseña debe contener al menos un símbolo.',
                'uncompromised' => 'La contraseña proporcionada ha aparecido en una filtración de datos. Por favor elija una contraseña diferente.',
            ],
        ];
    }
}
