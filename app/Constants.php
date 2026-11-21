<?php

namespace App;

class Constants
{
  const ACTIVO_STATUS = "ACTIVO";
  const BAJA_STATUS = "BAJA";
  const ERROR_DATOS_INVALIDOS = 422;
  // Estados del expediente de curriculum del docente
  const CV_STATUS_EN_CAPTURA = 'Activo';
  const CV_STATUS_COMPLETADO = 'Completado';
  const CV_STATUS_BAJA = 'Baja';
  const TIPO_ARCHIVO_CURRICULUM = 'cv';
  const TIPO_ARCHIVO_INE = 'ine';
  const TIPO_ARCHIVO_CURP = 'curp';
  const TIPO_ARCHIVO_COMPROBANTE_DOMICILIO = 'domicilio';
  const TIPO_ARCHIVO_COMPROBANTE_SITUACION_FISCAL = 'situacion_fiscal';
  const TIPO_ARCHIVO_ACTA_NACIMIENTO = 'acta_nacimiento';
  const TIPO_ARCHIVO_CEDULA_PROFESIONAL = 'cedula_profesional';
  const TIPO_ARCHIVO_CUENTA_BANCARIA = 'cuenta_bancaria';
  const TIPO_ARCHIVO_PLANEACION_ACADEMICA = 'planeacion_academica';
}
