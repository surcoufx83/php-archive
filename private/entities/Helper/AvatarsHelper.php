<?php

namespace Surcouf\PhpArchive\Helper;

use Surcouf\PhpArchive\Controller;

if (!defined('CORE2'))
  exit;

final class AvatarsHelper implements IAvatarsHelper {

  public static function createAvatar(string $payload, string $appendix) : string {
    $data = HashHelper::hash($payload);
    $filename = $data.$appendix.'.png';
    $filepath = combinePaths(DIR_PUBLIC_IMAGES, 'avatars', $filename);
    $identicon = new \Identicon\Identicon();
    $imageData = $identicon->getImageData($payload);
    file_put_contents($filepath, $imageData);
    return ($filename);
  }

  public static function exists(string $filename) : bool {
    $filepath = combinePaths(DIR_PUBLIC_IMAGES, 'avatars', $filename);
    return file_exists($filepath);
  }

}
