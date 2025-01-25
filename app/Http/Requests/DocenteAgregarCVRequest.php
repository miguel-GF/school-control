<?php

namespace App\Http\Requests;

use App\Constants;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class DocenteAgregarCVRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
   */
  public function rules(): array
  {
    return [
      'nombre' => 'required',
      'fechaNacimiento' => 'required|date_format:Y-m-d|before:today',
      'ciudad' => 'required',
      'estado' => 'required',
      'pais' => 'required',
      'estadoCivil' => 'required',
      'genero' => 'required',
      'correo' => 'nullable|email',
      'correoInstitucional' => 'required|email',
      'celular' => 'required',
      'domicilioCalle' => 'required',
      'domicilioNoExterior' => 'required',
      'domicilioNoInterior' => 'nullable',
      'domicilioColonia' => 'required',
      'domicilioCiudad' => 'required',
      'domicilioEstado' => 'required',
      'domicilioCodigoPostal' => 'required|size:5',
    ];
  }
  public function messages()
	{
		return [
			'nombre.required' => 'El nombre completo es requerido.',
      'fechaNacimiento.required' => 'La fecha de nacimiento es requerida.',
      'fechaNacimiento.date_format' => 'El formato de la fecha de nacimiento debe ser DD/MM/YYYY.',
      'fechaNacimiento.before' => 'La fecha de nacimiento es inválida, favor de verificar.',
      'ciudad.required' => 'Ciudad de nacimiento es requerido.',
      'estado.required' => 'Estado de nacimiento es requerido.',
      'pais.required' => 'País de nacimiento es requerido.',
      'estadoCivil.required' => 'Estado civil es requerido.',
      'genero.required' => 'Género es requerido.',
      // 'correo.required' => 'Correo electrónico es requerido.',
      'correo.email' => 'Correo electrónico es inválido, favor de verificar.',
      'correoInstitucional.required' => 'Correo insitucional es requerido.',
      'correoInstitucional.email' => 'Correo institucional es inválido, favor de verificar.',
      'celular.required' => 'No de celular es requerido.',
      'domicilioCalle.required' => 'Calle es requerida.',
      'domicilioNoExterior.required' => 'No. exterior es requerido.',
      'domicilioColonia.required' => 'Colonia es requerida.',
      'domicilioCiudad.required' => 'Ciudad es requerida.',
      'domicilioEstado.required' => 'Estado es requerido.',
      'domicilioCodigoPostal.required' => 'Código postal es requerido.',
      'domicilioCodigoPostal.size' => 'Código postal debe ser de 5 dígitos.',
		];
	}

  protected function failedValidation(Validator $validator)
	{
		$response = response([
      'mensaje' => $validator->errors()->first(),
      'status' => Constants::ERROR_DATOS_INVALIDOS,
    ]);
		throw new HttpResponseException($response);
	}
}
