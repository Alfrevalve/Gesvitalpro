<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class CustomRequest
 * Handles validation requests for the application.
 */
class CustomRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust as necessary for authorization
    }

    public function rules()
    {
        return [
            // Define your validation rules here
        ];
    }

    public function messages()
    {
        return [
            // Define custom validation messages here
        ];
    }
}
