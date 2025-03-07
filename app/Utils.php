<?php

namespace App;

class Utils
{
  public static function getUser()
  {
    $session = Session();
    $user = $session->get('user', null);
    return $user;
  }

  public static function obtenerTamanioLegibleArchivo($bytes)
  {
    if ($bytes >= 1073741824) {
      $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
      $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
      $bytes = number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
      $bytes = $bytes . ' bytes';
    } elseif ($bytes == 1) {
      $bytes = $bytes . ' byte';
    } else {
      $bytes = '0 bytes';
    }

    return $bytes;
  }

  public static function limpiarCaracteresEspeciales($cadena) {
    $cadena = iconv('UTF-8', 'ASCII//TRANSLIT', $cadena);
    $cadena = preg_replace('/[^a-zA-Z0-9\s]/', '', $cadena);
    $cadena = trim(preg_replace('/\s+/', ' ', $cadena));
    return $cadena;
}
}
