<?php

namespace Surcouf\PhpArchive;

use Surcouf\PhpArchive\Document\Type;

if (!defined('CORE2'))
  exit;

class Document implements IDocument, IDbObject {

  private $id, $ownerid, $typeid, $name, $description;

  private $changes = array();

  public function __construct(array $record) {
    $this->id = intval($record['document_id']);
    $this->ownerid = intval($record['owner_id']);
    $this->typeid = (is_null($record['doctype_id']) ? null : intval($record['doctype_id']));
    $this->name = $record['document_name'];
    $this->description = $record['document_description'];
  }

  public function getDescription() : string {
    return $this->description;
  }

  public function getDbChanges() : array {
    return $this->changes;
  }

  public function getId() : int {
    return $this->id;
  }

  public function getName() : string {
    return $this->name;
  }

  public function getOwner() : User {
    global $Controller;
    return $Controller->getUser($this->ownerid);
  }

  public function getOwnerId() : int {
    return $this->ownerid;
  }

  public function getType() : ?Type {
    global $Controller;
    return $Controller->getType($this->typeid);
  }

  public function getTypeId() : int {
    return $this->typeid;
  }

}
