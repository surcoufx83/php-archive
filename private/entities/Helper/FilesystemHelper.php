<?php

namespace Surcouf\PhpArchive\Helper;

use Surcouf\PhpArchive\Controller;

if (!defined('CORE2'))
  exit;

class FilesystemHelper {

  public function file_exists(string $filename) : bool {
    return file_exists($filename);
  }

  public function file_put_contents(string $filename, $data, ?int $flags = 0) {
    return file_put_contents($filename, $data, $flags);
  }

}
