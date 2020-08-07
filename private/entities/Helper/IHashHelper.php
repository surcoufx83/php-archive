<?php

namespace Surcouf\PhpArchive\Helper;

if (!defined('CORE2'))
  exit;

interface IHashHelper {

  public static function generate_token($length = 32) : ?string;
  public static function getHashAlgo() : string;
  public static function hash(string $input, ?string $algorithm = null) : string;

}