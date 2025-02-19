<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEquipmentRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Cambiar según la lógica de autorización
    }

    public function rules()
    {
        return [
            'line_id' => 'required|exists:lines,id',
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'serial_number' => 'required|string|unique:equipment,serial_number',
        ];
    }

    public function messages()
    {
        return [
            'line_id.required' => 'El campo línea es obligatorio.',
            'name.required' => 'El campo nombre es obligatorio.',
            'type.required' => 'El campo tipo es obligatorio.',
            'serial_number.required' => 'El campo número de serie es obligatorio.',
        ];
    }
}
