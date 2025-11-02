<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Cambiar a lógica de autorización si es necesario
    }

    public function rules()
    {
        return [
            'bike_id' => 'required|exists:bikes,id',
            'componentes' => 'required|array|min:1',
            'componentes.*' => 'exists:components,id',
            'prioridad' => 'required|in:normal,urgente,premium',
            'descripcion_problema' => 'required|string',
            'estimacion_reparacion' => 'nullable|string',
            'tiempo_estimado' => 'required|integer|min:1',
        ];
    }
    
    public function messages()
    {
        return [
            'componentes.required' => 'Debe seleccionar al menos un componente.',
            'componentes.*.exists' => 'Uno de los componentes seleccionados no es válido.',
        ];
    }
    
}
