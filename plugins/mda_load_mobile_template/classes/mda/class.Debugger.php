<?php
class Debugger
{
  public static function debug($var_to_debug)
  {
    ob_start();
    print_r($var_to_debug);
    $out1 = ob_get_contents();
    ob_clean();
    
    return $out1;
  }
}