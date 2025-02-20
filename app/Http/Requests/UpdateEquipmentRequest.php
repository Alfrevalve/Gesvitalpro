<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEquipmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('edit equipment');
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
            'serial_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('equipment')->ignore($this->equipment),
            ],
            'manufacturer' => ['required', 'string', 'max:100'],
            'purchase_date' => ['required', 'date', 'before_or_equal:today'],
            'warranty_expiry' => ['required', 'date', 'after:purchase_date'],
            'status' => ['required', 'in:available,unavailable,maintenance'],
            'location' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'cost' => ['required', 'numeric', 'min:0'],
            'maintenance_interval' => ['required', 'integer', 'min:1'],
            'maintenance_interval_unit' => ['required', 'in:days,weeks,months,years'],
            'next_maintenance_date' => [
                'required',
                'date',
                'after:' . ($this->equipment->last_maintenance_date ?? 'today'),
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
            'specifications' => ['nullable', 'array'],
            'specifications.*' => ['string', 'max:255'],
            'documents' => ['nullable', 'array'],
            'documents.*' => ['file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
            'remove_documents' => ['nullable', 'array'],
            'remove_documents.*' => ['exists:equipment_documents,id'],
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
            'serial_number.unique' => 'Este número de serie ya está registrado en otro equipo.',
            'warranty_expiry.after' => 'La fecha de vencimiento de garantía debe ser posterior a la fecha de compra.',
            'next_maintenance_date.after' => 'La próxima fecha de mantenimiento debe ser posterior al último mantenimiento.',
            'documents.*.mimes' => 'Los documentos deben ser archivos PDF, Word o imágenes.',
            'documents.*.max' => 'Los documentos no deben superar los 10MB.',
            'remove_documents.*.exists' => 'Uno de los documentos seleccionados para eliminar no existe.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('specifications') && is_string($this->specifications)) {
            $this->merge([
                'specifications' => json_decode($this->specifications, true)
            ]);
        }
    }
}
