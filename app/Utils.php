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
}
