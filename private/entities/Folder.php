<?php

namespace Surcouf\PhpArchive;

if (!defined('CORE2'))
  exit;

class Folder implements IFolder, IDbObject {

  private $id, $mountid, $parentid, $name;

  private $changes = array();

  function __construct($data) {
    $this->id = intval($data['folder_id']);
    $this->mountid = intval($data['mount_id']);
    $this->parentid = (is_null($data['parent_id']) ? null : intval($data['parent_id']));
    $this->name = $data['folder_name'];
  }

  public function getDbChanges() : array {
    return $this->changes;
  }

  function getId() : int {
    return $this->id;
  }

  public function getMount() : ?Mount {
    global $Controller;
    return $Controller->getMount($this->mountid);
  }

  public function getMountId() : ?int {
    return $this->mountid;
  }

  function getName() : string {
    return $this->name;
  }

  public function getParent() : ?Folder {
    if (is_null($this->parentid))
      return null;
    global $Controller;
    return $Controller->getFolder($this->parentid);
  }

  public function getParentId() : ?int {
    return $this->parentid;
  }

}
