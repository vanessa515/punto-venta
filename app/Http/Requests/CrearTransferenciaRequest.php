<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrearTransferenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // exists: se activará cuando los compañeros suban el módulo de sucursales
            'fk_sucursal_origen'       => ['required', 'integer'],
            'fk_sucursal_destino'      => ['required', 'integer'],
            'notas'                    => ['nullable', 'string', 'max:1000'],

            // Lista de productos — mínimo 1
            'detalles'                 => ['required', 'array', 'min:1'],
            'detalles.*.fk_producto'   => ['required', 'integer'],
            'detalles.*.cantidad'      => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'fk_sucursal_origen.required'     => 'La sucursal de origen es obligatoria.',
            'fk_sucursal_destino.required'    => 'La sucursal de destino es obligatoria.',
            'detalles.required'               => 'Debes agregar al menos un producto al traspaso.',
            'detalles.min'                    => 'Debes agregar al menos un producto al traspaso.',
            'detalles.*.fk_producto.required' => 'Cada línea del traspaso debe tener un producto.',
            'detalles.*.cantidad.required'    => 'Cada línea del traspaso debe tener una cantidad.',
            'detalles.*.cantidad.min'         => 'La cantidad de cada producto debe ser mayor a 0.',
        ];
    }
}
