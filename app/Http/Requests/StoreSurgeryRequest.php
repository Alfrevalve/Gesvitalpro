<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSurgeryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'line_id' => 'required|exists:lines,id',
            'description' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'equipment_ids' => 'required|array|min:1',
            'equipment_ids.*' => 'exists:equipment,id',
            'staff_ids' => 'required|array|min:1',
            'staff_ids.*' => 'exists:users,id',
        ];
    }

    public function messages()
    {
        return [
            'line_id.required' => 'Debe seleccionar una línea.',
            'line_id.exists' => 'La línea seleccionada no existe.',
            'description.required' => 'La descripción es obligatoria.',
            'description.max' => 'La descripción no puede exceder los 500 caracteres.',
            'notes.max' => 'Las notas no pueden exceder los 1000 caracteres.',
            'equipment_ids.required' => 'Debe seleccionar al menos un equipo.',
            'equipment_ids.min' => 'Debe seleccionar al menos un equipo.',
            'equipment_ids.*.exists' => 'Uno de los equipos seleccionados no existe.',
            'staff_ids.required' => 'Debe seleccionar al menos un miembro del personal.',
            'staff_ids.min' => 'Debe seleccionar al menos un miembro del personal.',
            'staff_ids.*.exists' => 'Uno de los miembros del personal seleccionados no existe.',
        ];
    }
}
