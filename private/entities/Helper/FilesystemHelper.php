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

  public function paths_combine(...$paths) {
    $fullpath = '';
    foreach($paths AS $path) {
      if ($fullpath == '')
        $fullpath = $path;
      else
        $fullpath = $fullpath.DIRECTORY_SEPARATOR.$path;
    }
    return $fullpath;
  }

}
