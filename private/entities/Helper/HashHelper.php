<?php

namespace Surcouf\PhpArchive\Helper;

use Surcouf\PhpArchive\Controller;

if (!defined('CORE2'))
  exit;

final class HashHelper implements IHashHelper {

  public static function getHashAlgo() : string {
    global $Controller;
    return $Controller->Config()->Checksum->Algorithm->getString();
  }

  public static function hash(string $input, ?string $algorithm = null) : string {
    if (!is_null($algorithm))
      return hash($algorithm, $input);
    return hash(HashHelper::getHashAlgo(), $input);
  }

}
