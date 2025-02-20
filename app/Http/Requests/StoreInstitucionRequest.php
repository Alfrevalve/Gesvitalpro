<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInstitucionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create instituciones');
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
            'code' => ['required', 'string', 'max:50', 'unique:instituciones,code'],
            'type' => ['required', 'string', 'in:hospital,clinic,center'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', 'unique:instituciones,email'],
            'website' => ['nullable', 'url', 'max:255'],
            'tax_id' => ['required', 'string', 'max:50', 'unique:instituciones,tax_id'],
            'license_number' => ['required', 'string', 'max:50', 'unique:instituciones,license_number'],
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
            'documents' => ['nullable', 'array'],
            'documents.*' => ['file', 'mimes:pdf,doc,docx', 'max:10240'], // 10MB max
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
            'documents' => 'documentos',
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
            'code.unique' => 'Este código ya está registrado.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'tax_id.unique' => 'Esta identificación fiscal ya está registrada.',
            'license_number.unique' => 'Este número de licencia ya está registrado.',
            'logo.max' => 'El logotipo no debe ser mayor a 2MB.',
            'documents.*.max' => 'Los documentos no deben ser mayores a 10MB.',
            'documents.*.mimes' => 'Los documentos deben ser archivos PDF o Word.',
            'operating_hours.*.required' => 'Debe especificar el horario para todos los días.',
            'specialties.*.exists' => 'Una de las especialidades seleccionadas no existe.',
        ];
    }
}
