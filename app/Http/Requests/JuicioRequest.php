<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JuicioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
  public function rules(): array {
    return [
        'nombre'               => ['required','string','max:255'],
        'tipo'                 => ['required','in:nulidad,revocacion'],
        'cliente_id'           => ['required','exists:clientes,id'],
        'autoridad_id'         => ['nullable','exists:autoridads,id'],
        'fecha_inicio'         => ['nullable','date'],
        'monto'                => ['nullable','numeric','min:0'],
        'observaciones_monto'  => ['nullable','string'],
        'resolucion_impugnada' => ['nullable','string','max:500'],
        'garantia'             => ['nullable','string','max:255'],
        'numero_juicio'        => ['nullable','string','max:255'],
        'numero_expediente'    => ['nullable','string','max:255'],
        'estatus'              => ['required','in:juicio,autorizado,en_proceso,concluido'],
        'abogado_id'           => ['nullable','exists:abogados,id'],
        'etiquetas'            => ['array'],
        'etiquetas.*'          => ['exists:etiquetas,id'],
    ];
}

}
