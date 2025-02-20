<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInstitucionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('edit instituciones');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('instituciones')->ignore($this->institucion),
            ],
            'type' => ['required', 'string', 'in:hospital,clinic,center'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('instituciones')->ignore($this->institucion),
            ],
            'website' => ['nullable', 'url', 'max:255'],
            'tax_id' => [
                'required',
                'string',
                'max:50',
                Rule::unique('instituciones')->ignore($this->institucion),
            ],
            'license_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('instituciones')->ignore($this->institucion),
            ],
            'is_active' => ['boolean'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:50'],
            'contact_email' => ['required', 'email', 'max:255'],
            'emergency_phone' => ['required', 'string', 'max:50'],
            'operating_hours' => ['required', 'array'],
            'operating_hours.*' => ['required', 'string'],
            'services' => ['nullable', 'array'],
            'services.*' => ['string', 'max:255'],
            'specialties' => ['nullable', 'array'],
            'specialties.*' => ['exists:specialties,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'max:2048'], // 2MB max
            'remove_logo' => ['nullable', 'boolean'],
            'documents' => ['nullable', 'array'],
            'documents.*' => ['file', 'mimes:pdf,doc,docx', 'max:10240'], // 10MB max
            'remove_documents' => ['nullable', 'array'],
            'remove_documents.*' => ['exists:institucion_documents,id'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'code' => 'código',
            'type' => 'tipo',
            'address' => 'dirección',
            'city' => 'ciudad',
            'state' => 'estado/provincia',
            'postal_code' => 'código postal',
            'country' => 'país',
            'phone' => 'teléfono',
            'email' => 'correo electrónico',
            'website' => 'sitio web',
            'tax_id' => 'identificación fiscal',
            'license_number' => 'número de licencia',
            'is_active' => 'activo',
            'latitude' => 'latitud',
            'longitude' => 'longitud',
            'contact_name' => 'nombre de contacto',
            'contact_phone' => 'teléfono de contacto',
            'contact_email' => 'correo de contacto',
            'emergency_phone' => 'teléfono de emergencia',
            'operating_hours' => 'horario de atención',
            'services' => 'servicios',
            'specialties' => 'especialidades',
            'notes' => 'notas',
            'logo' => 'logotipo',
            'remove_logo' => 'eliminar logotipo',
            'documents' => 'documentos',
            'remove_documents' => 'documentos a eliminar',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.unique' => 'Este código ya está registrado en otra institución.',
            'email.unique' => 'Este correo electrónico ya está registrado en otra institución.',
            'tax_id.unique' => 'Esta identificación fiscal ya está registrada en otra institución.',
            'license_number.unique' => 'Este número de licencia ya está registrado en otra institución.',
            'logo.max' => 'El logotipo no debe ser mayor a 2MB.',
            'documents.*.max' => 'Los documentos no deben ser mayores a 10MB.',
            'documents.*.mimes' => 'Los documentos deben ser archivos PDF o Word.',
            'operating_hours.*.required' => 'Debe especificar el horario para todos los días.',
            'specialties.*.exists' => 'Una de las especialidades seleccionadas no existe.',
            'remove_documents.*.exists' => 'Uno de los documentos seleccionados para eliminar no existe.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('services') && is_string($this->services)) {
            $this->merge([
                'services' => json_decode($this->services, true)
            ]);
        }

        if ($this->has('operating_hours') && is_string($this->operating_hours)) {
            $this->merge([
                'operating_hours' => json_decode($this->operating_hours, true)
            ]);
        }
    }
}
