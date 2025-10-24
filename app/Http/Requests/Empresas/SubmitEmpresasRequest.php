<?php

namespace App\Http\Requests\Empresas;

use App\enums\Response\ResponseStatusEnum;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SubmitEmpresasRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "documento" => "required|string|size:11",
            "razon_social" => "required|string",
            "nombre_comercial" => "required|string",
            "direccion" => "require|string",
            "usuario_facturacion" => "required|string",
            "contrasena_facturacion" => "required|string"
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $message = $validator->errors()->all();
        throw new HttpResponseException(response()->json(['status' => ResponseStatusEnum::ERROR, 'message' => 'Fallo en la validación de datos', 'errors' => $message]));
    }
}
