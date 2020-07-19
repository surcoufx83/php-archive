<?php

namespace Surcouf\PhpArchive\Document;

if (!defined('CORE2'))
  exit;

class Type {

  private $id, $name;

  function __construct($data) {
    $this->id = intval($data['type_id']);
    $this->name = $data['type_name'];
  }

  function getId() {
    return $this->id;
  }

  function getLink() {
    return '<a href="/type/'.$this->id.'">'.$this->name.'</a>';
  }

  function getName() {
    return $this->name;
  }

}
