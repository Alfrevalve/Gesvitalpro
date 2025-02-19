<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVisitaRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Cambiar según la lógica de autorización
    }

    public function rules()
    {
        return [
            'fecha_hora' => 'required|date',
            'institucion_id' => 'required|exists:instituciones,id',
            'medico_id' => 'required|exists:medicos,id',
            'motivo' => 'required|string|max:255',
            'observaciones' => 'nullable|string',
            'estado' => 'required|in:programada,realizada,cancelada'
        ];
    }

    public function messages()
    {
        return [
            'fecha_hora.required' => 'El campo fecha y hora es obligatorio.',
            'institucion_id.required' => 'El campo institución es obligatorio.',
            'medico_id.required' => 'El campo médico es obligatorio.',
            'motivo.required' => 'El campo motivo es obligatorio.',
            'estado.required' => 'El campo estado es obligatorio.',
        ];
    }
}
