<?php

namespace App\Http\Requests;

use App\Constants;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DocenteGuardarCVRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Reglas para guardar avance. Solo el celular es obligatorio porque el
   * docente puede guardar su expediente por partes y continuar despues.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
   */
  public function rules(): array
  {
    return [
      'celular' => 'required|string|max:20',
      'nombre' => 'nullable|string|max:220',
      'fechaNacimiento' => 'nullable|date_format:Y-m-d|before:today',
      'ciudad' => 'nullable|string|max:30',
      'estado' => 'nullable|string|max:30',
      'pais' => 'nullable|string|max:30',
      'estadoCivil' => 'nullable|string|max:15',
      'genero' => 'nullable|string|max:20',
      'correo' => 'nullable|email|max:150',
      'correoInstitucional' => 'nullable|email|max:150',
      'facebook' => 'nullable|string',
      'telefono' => 'nullable|string|max:20',
      'domicilioCalle' => 'nullable|string',
      'domicilioNoExterior' => 'nullable|string|max:10',
      'domicilioNoInterior' => 'nullable|string|max:10',
      'domicilioColonia' => 'nullable|string|max:40',
      'domicilioCiudad' => 'nullable|string|max:40',
      'domicilioEstado' => 'nullable|string|max:40',
      'domicilioCodigoPostal' => 'nullable|string|size:5',
      'rfc' => 'nullable|string|max:15',
      'curp' => 'nullable|string|max:25',
      'clabe' => 'nullable|string|max:50',
      'banco' => 'nullable|string|max:50',
      'fiscalCalle' => 'nullable|string',
      'fiscalNoExterior' => 'nullable|string|max:10',
      'fiscalNoInterior' => 'nullable|string|max:10',
      'fiscalColonia' => 'nullable|string|max:40',
      'fiscalCiudad' => 'nullable|string|max:40',
      'fiscalEstado' => 'nullable|string|max:40',
      'fiscalCodigoPostal' => 'nullable|string|size:5',
      'porcentajeActividadProf' => 'nullable|numeric|min:0|max:100',
      'porcentajeAsalariado' => 'nullable|numeric|min:0|max:100',
      'porcentajePensionado' => 'nullable|numeric|min:0|max:100',
      'porcentajeDocencia' => 'nullable|numeric|min:0|max:100',
      'enfermedades' => 'nullable|string',
      'alergias' => 'nullable|string',
      'tipoSangre' => 'nullable|string|max:4',
      'informacionAdicional' => 'nullable|string',
      'descripcionCurriculum' => 'nullable|string',
      'descripcionPlaneacionAcademica' => 'nullable|string',
      'descripcionIne' => 'nullable|string',
      'descripcionCurp' => 'nullable|string',
      'descripcionDomicilio' => 'nullable|string',
      'descripcionSituacionFiscal' => 'nullable|string',
      'descripcionActaNacimiento' => 'nullable|string',
      'descripcionCedula' => 'nullable|string',
      'archivoCurriculum' => 'nullable|file|mimes:pdf|max:10240',
      'archivoPlaneacionAcademica' => 'nullable|file|mimes:pdf|max:10240',
      'archivoIne' => 'nullable|file|mimes:pdf|max:10240',
      'archivoCurp' => 'nullable|file|mimes:pdf|max:10240',
      'archivoDomicilio' => 'nullable|file|mimes:pdf|max:10240',
      'archivoSituacionFiscal' => 'nullable|file|mimes:pdf|max:10240',
      'archivoActaNacimiento' => 'nullable|file|mimes:pdf|max:10240',
      'archivoCedula' => 'nullable|file|mimes:pdf|max:10240',
      'archivoCuentaBancaria' => 'nullable|file|mimes:jpg,jpeg,png|max:10240',
    ];
  }

  public function messages()
  {
    return [
      'celular.required' => 'El número de celular es obligatorio.',
      'fechaNacimiento.date_format' => 'La fecha de nacimiento debe tener el formato AAAA-MM-DD.',
      'fechaNacimiento.before' => 'La fecha de nacimiento es inválida, favor de verificar.',
      'correo.email' => 'El correo electrónico es inválido, favor de verificar.',
      'correoInstitucional.email' => 'El correo institucional es inválido, favor de verificar.',
      'domicilioCodigoPostal.size' => 'El código postal debe ser de 5 dígitos.',
      'fiscalCodigoPostal.size' => 'El código postal fiscal debe ser de 5 dígitos.',
      'archivoCurriculum.mimes' => 'El currículum debe ser un archivo PDF.',
      'archivoPlaneacionAcademica.mimes' => 'La planeación académica debe ser un archivo PDF.',
      'archivoIne.mimes' => 'La INE debe ser un archivo PDF.',
      'archivoCurp.mimes' => 'La CURP debe ser un archivo PDF.',
      'archivoDomicilio.mimes' => 'El comprobante de domicilio debe ser un archivo PDF.',
      'archivoSituacionFiscal.mimes' => 'El comprobante de situación fiscal debe ser un archivo PDF.',
      'archivoActaNacimiento.mimes' => 'El acta de nacimiento debe ser un archivo PDF.',
      'archivoCedula.mimes' => 'La cédula profesional debe ser un archivo PDF.',
      'archivoCuentaBancaria.mimes' => 'La fotografía de la cuenta bancaria debe ser JPG o PNG.',
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
