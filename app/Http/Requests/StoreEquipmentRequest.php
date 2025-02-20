<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEquipmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create equipment');
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
            'description' => ['required', 'string', 'max:1000'],
            'model' => ['required', 'string', 'max:100'],
            'serial_number' => ['required', 'string', 'max:100', 'unique:equipment,serial_number'],
            'manufacturer' => ['required', 'string', 'max:100'],
            'purchase_date' => ['required', 'date', 'before_or_equal:today'],
            'warranty_expiry' => ['required', 'date', 'after:purchase_date'],
            'status' => ['required', 'in:available,unavailable,maintenance'],
            'location' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'cost' => ['required', 'numeric', 'min:0'],
            'maintenance_interval' => ['required', 'integer', 'min:1'],
            'maintenance_interval_unit' => ['required', 'in:days,weeks,months,years'],
            'next_maintenance_date' => ['required', 'date', 'after:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'specifications' => ['nullable', 'array'],
            'specifications.*' => ['string', 'max:255'],
            'documents' => ['nullable', 'array'],
            'documents.*' => ['file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
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
            'description' => 'descripción',
            'model' => 'modelo',
            'serial_number' => 'número de serie',
            'manufacturer' => 'fabricante',
            'purchase_date' => 'fecha de compra',
            'warranty_expiry' => 'vencimiento de garantía',
            'status' => 'estado',
            'location' => 'ubicación',
            'category' => 'categoría',
            'cost' => 'costo',
            'maintenance_interval' => 'intervalo de mantenimiento',
            'maintenance_interval_unit' => 'unidad de intervalo',
            'next_maintenance_date' => 'próxima fecha de mantenimiento',
            'notes' => 'notas',
            'specifications' => 'especificaciones',
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
            'serial_number.unique' => 'Este número de serie ya está registrado.',
            'warranty_expiry.after' => 'La fecha de vencimiento de garantía debe ser posterior a la fecha de compra.',
            'next_maintenance_date.after' => 'La próxima fecha de mantenimiento debe ser posterior a hoy.',
            'documents.*.mimes' => 'Los documentos deben ser archivos PDF, Word o imágenes.',
            'documents.*.max' => 'Los documentos no deben superar los 10MB.',
        ];
    }
}
