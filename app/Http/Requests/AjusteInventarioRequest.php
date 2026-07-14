<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AjusteInventarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // exists: se activará cuando los compañeros suban sucursal y producto
            'fk_sucursal'     => ['required', 'integer'],
            'fk_producto'     => ['required', 'integer'],
            'cantidad'        => ['required', 'numeric', 'min:0.01'],
            'tipo_movimiento' => ['required', 'string', 'in:entrada,salida,ajuste'],

            // Solo tiene sentido en entradas (nueva compra con costo conocido)
            'costo_unitario'  => ['nullable', 'numeric', 'min:0'],

            'referencia'      => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'fk_sucursal.required'     => 'La sucursal es obligatoria.',
            'fk_producto.required'     => 'El producto es obligatorio.',
            'cantidad.required'        => 'La cantidad es obligatoria.',
            'cantidad.numeric'         => 'La cantidad debe ser un valor numérico.',
            'cantidad.min'             => 'La cantidad debe ser mayor a 0.',
            'tipo_movimiento.required' => 'El tipo de movimiento es obligatorio.',
            'tipo_movimiento.in'       => 'El tipo solo puede ser: entrada, salida o ajuste.',
            'costo_unitario.numeric'   => 'El costo unitario debe ser un valor numérico.',
            'costo_unitario.min'       => 'El costo unitario no puede ser negativo.',
        ];
    }
}
