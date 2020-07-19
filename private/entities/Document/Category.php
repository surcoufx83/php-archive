<?php

namespace Surcouf\PhpArchive\Document;

if (!defined('CORE2'))
  exit;

class Category {

  private $id, $name;
  private $parent;
  private $childs = array();

  function __construct($data) {
    global $DocumentCategories;
    $this->id = intval($data['category_id']);
    $this->name = $data['category_name'];
    if (!is_null($data['parent_id'])) {
      $this->parent =& $DocumentCategories[intval($data['parent_id'])];
      $this->parent->addChild($this);
    }
  }

  function addChild(Category &$child) {
    $this->childs[$child->getId()] = $child;
  }

  function getCategoryPath() {
    if (is_null($this->parent))
      return array($this->getLink());
    $ar = $this->parent->getCategoryPath();
    $ar[] = $this->getLink();
    return $ar;
  }

  function getChild($id) {
    return $this->childs[$id];
  }

  function getChilds() {
    return $this->childs;
  }

  function getDocumentsLink() {
    return '/documents/category/'.$this->id;
  }

  function getId() {
    return $this->id;
  }

  function getLink() {
    return '<a href="/category/'.$this->id.'">'.$this->name.'</a>';
  }

  function getName() {
    return $this->name;
  }

  function getParent() {
    return $this->parent;
  }

  function hasChilds() {
    return (count($this->childs) != 0);
  }

}
