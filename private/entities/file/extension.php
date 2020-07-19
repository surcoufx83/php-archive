<?php

namespace Surcouf\PhpArchive\File;

use Surcouf\PhpArchive\IDbObject;
use Surcouf\PhpArchive\Config\Icon;

if (!defined('CORE2'))
  exit;

class Extension implements IExtension, IDbObject {

  private $id, $ext, $mime, $name, $icon;
  private $inlineviewer, $nomail, $canocr;

  private $changes = array();

  function __construct($data) {
    global $Controller;
    $this->id = intval($data['ext_id']);
    $this->icon = is_null($data['ext_icon']) ? $Controller->Config()->Icons->File : new Icon(json_decode($data['ext_icon'], true)['icon']);
    $this->ext = $data['ext_key'];
    $this->mime = $data['ext_mimetype'];
    $this->name = $data['ext_name'];
    $this->nomail = getBool($data['ext_noemail']);
    $this->canocr = getBool($data['ext_canocr']);
    $this->inlineviewer = getBool($data['ext_inlineviewer']);
  }

  public function canOcr() : bool {
    return $this->canocr;
  }

  public function disallowSendmail() : bool {
    return $this->nomail;
  }

  public function getDbChanges() : array {
    return $this->changes;
  }

  public function getExt() : string {
    return $this->ext;
  }

  public function getIcon(?string $cssClass=null, ?string $customStyle=null, ?string $id=null) : string {
    return $this->icon->getIcon($cssClass, $customStyle, $id);
  }

  public function getIconObj() : ?Icon {
    return $this->icon;
  }

  public function getId() : int {
    return $this->id;
  }

  public function getMimeType() : string {
    return $this->mime;
  }

  public function getName() : string {
    return $this->name;
  }

  public function useInlineViewer() : bool {
    return $this->inlineviewer;
  }

}
