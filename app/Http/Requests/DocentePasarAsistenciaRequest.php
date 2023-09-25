<?php

namespace App\Http\Requests;

use App\Constants;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class DocentePasarAsistenciaRequest extends FormRequest
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
      'fecha' => 'required|date',
      'licenciatura' => 'required',
      'semestre' => 'required',
      'grupo' => 'required',
      'alumnos' => 'required',
      'periodo' => 'required',
    ];
  }
  public function messages()
	{
		return [
			'fecha.required' => 'La fecha para pasar asostencia es obligatoria.',
			'licenciatura.required' => 'Licenciatura es obligatoria.',
			'semestre.required' => 'Semestre es obligatorio.',
			'grupo.required' => 'Grupo es obligatorio.',
			'alumnos.required' => 'Alumnos son obligatorios.',
			'periodo.required' => 'Periodo es obligatorio.',
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
