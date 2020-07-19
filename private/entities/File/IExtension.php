<?php

namespace Surcouf\PhpArchive\File;

use Surcouf\PhpArchive\Config\Icon;

if (!defined('CORE2'))
  exit;

interface IExtension {

  public function canOcr() : bool;
  public function disallowSendmail() : bool;
  public function getExt() : string;
  public function getIcon(?string $cssClass=null, ?string $customStyle=null, ?string $id=null) : string;
  public function getIconObj() : ?Icon;
  public function getId() : int;
  public function getMimeType() : string;
  public function getName() : string;
  public function useInlineViewer() : bool;

}
