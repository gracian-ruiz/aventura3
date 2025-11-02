<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Cambia si necesitas lógica de autorización
    }

    public function rules()
    {
        return [
            'bike_id' => 'required|exists:bikes,id',
            'componente_id' => 'nullable|exists:components,id',
            'prioridad' => 'required|in:normal,urgente,premium',
            'tiempo_estimado' => 'nullable|integer|min:1',
        ];
    }

    public function messages()
    {
        return [
            'bike_id.required' => 'Debe seleccionar una bicicleta.',
            'bike_id.exists' => 'La bicicleta seleccionada no es válida.',
            'componente_id.exists' => 'El componente seleccionado no es válido.',
            'prioridad.required' => 'Debe elegir una prioridad.',
            'prioridad.in' => 'La prioridad debe ser normal o urgente.',
            'tiempo_estimado.integer' => 'El tiempo estimado debe ser un número entero.',
            'tiempo_estimado.min' => 'El tiempo estimado debe ser al menos 1 minuto.',
        ];
    }
}
