<?php

include_once('kcsqs_client.php');

class sqslog {
  
  protected static $instance;

  public static function getInstance() {
    if (!self::$instance) {
      self::$instance = new kcsqs();
    }
    return self::$instance;
  }

  public static function log($name, $value) {
    return self::getInstance()->put($name, $value);
  }

}
