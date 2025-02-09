<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ValidationService
{
    public function getErrorMessages()
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser una cadena de texto.',
            'date' => 'El campo :attribute debe ser una fecha válida.',
            'email' => 'El campo :attribute debe ser una dirección de correo electrónico válida.',
            'max' => 'El campo :attribute no puede tener más de :max caracteres.',
            'unique' => 'El valor del campo :attribute ya está en uso.',
            'confirmed' => 'La confirmación de la contraseña no coincide.',
            'in' => 'El valor del campo :attribute debe estar en :values.',
            // Add more custom messages as needed
        ];
    }

    private function userValidationRules($data)
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($data['id'] ?? null)
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'
            ],
            'role' => [
                'required',
                Rule::exists('roles', 'id')
            ]
        ];
    }

    private function loginValidationRules($data)
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
                'exists:users,email'
            ],
            'password' => 'required|string'
        ];
    }

    public function validate($data, $type)
    {
        $rules = [];

        switch ($type) {
            case 'visita':
                $rules = [
                    'patient_id' => [
                        'required',
                        'exists:pacientes,id'
                    ],
                    'date' => [
                        'required',
                        'date',
                        'after_or_equal:today'
                    ],
                    'motivo' => 'required|string|max:500',
                    'estado' => [
                        'required',
                        Rule::in(['programada', 'completada', 'cancelada'])
                    ]
                ];
                break;

            case 'inventario':
                $rules = [
                    'nombre' => 'required|string|max:255',
                    'cantidad' => 'required|integer|min:0',
                    'precio_unitario' => 'required|numeric|min:0',
                    'categoria' => 'required|string|max:100',
                    'proveedor' => 'nullable|string|max:255',
                    'fecha_vencimiento' => 'nullable|date|after:today',
                    'ubicacion' => 'required|string|max:255',
                    'stock_minimo' => 'required|integer|min:0'
                ];
                break;

            case 'cirugia':
                $rules = [
                    'fecha_cirugia' => [
                        'required',
                        'date',
                        'after:today'
                    ],
                    'instrumentista' => [
                        'required',
                        'string',
                        'max:255',
                        'exists:personal,id'
                    ],
                    'equipo_asignado' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::exists('inventarios', 'id')
                    ],
                    'instituciones_hospitalarias' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::exists('instituciones', 'id')
                    ],
                    'cirujano' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::exists('personal', 'id')
                    ],
                    'paciente' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::exists('pacientes', 'id')
                    ],
                    'especialidad' => [
                        'required',
                        'string',
                        'max:255'
                    ],
                    'estado_cirugia' => [
                        'required',
                        'string',
                        Rule::in(['programada', 'en_proceso', 'completada', 'cancelada'])
                    ],
                    'notas_adicionales' => 'nullable|string|max:1000',
                    'duracion_estimada' => 'required|integer|min:1',
                    'tipo_anestesia' => 'required|string|max:100',
                    'requisitos_especiales' => 'nullable|string|max:500'
                ];
                break;

            case 'paciente':
                $rules = [
                    'name' => 'required|string|max:255',
                    'email' => [
                        'required',
                        'string',
                        'email',
                        'max:255',
                        Rule::unique('pacientes')->ignore($data['id'] ?? null)
                    ],
                    'telefono' => 'required|string|max:20',
                    'fecha_nacimiento' => 'required|date|before:today',
                    'genero' => [
                        'required',
                        Rule::in(['M', 'F', 'O'])
                    ],
                    'direccion' => 'required|string|max:500',
                    'tipo_sangre' => 'nullable|string|max:5',
                    'alergias' => 'nullable|string|max:500',
                    'antecedentes_medicos' => 'nullable|string|max:1000'
                ];
                break;

            case 'user':
                $rules = [
                    'name' => 'required|string|max:255',
                    'email' => [
                        'required',
                        'string',
                        'email',
                        'max:255',
                        Rule::unique('users')->ignore($data['id'] ?? null)
                    ],
                    'password' => [
                        'required',
                        'string',
                        'min:8',
                        'confirmed',
                        'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'
                    ],
                    'role' => [
                        'required',
                        Rule::exists('roles', 'id')
                    ]
                ];
                break;

            case 'login':
                $rules = [
                    'email' => [
                        'required',
                        'string',
                        'email',
                        'exists:users,email'
                    ],
                    'password' => 'required|string'
                ];
                break;

            case 'personal':
                $rules = [
                    'nombre' => 'required|string|max:255',
                    'apellido' => 'required|string|max:255',
                    'email' => [
                        'required',
                        'email',
                        Rule::unique('personal')->ignore($data['id'] ?? null)
                    ],
                    'telefono' => 'required|string|max:20',
                    'especialidad' => 'required|string|max:255',
                    'numero_colegiado' => [
                        'required',
                        'string',
                        'max:50',
                        Rule::unique('personal')->ignore($data['id'] ?? null)
                    ],
                    'institucion' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::exists('instituciones', 'id')
                    ],
                    'disponibilidad' => 'required|json',
                    'estado' => [
                        'required',
                        Rule::in(['activo', 'inactivo', 'vacaciones'])
                    ]
                ];
                break;
        }

        return Validator::make($data, $rules);
    }
}
