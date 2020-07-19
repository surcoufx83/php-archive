<?php

use Surcouf\PhpArchive\Database\EQueryType;
use Surcouf\PhpArchive\Database\QueryBuilder;

if (!defined('CORE2'))
  exit;

$DocumentCategories = array();
$MainCategories = array();

function load_documentCategories() {
  global $Controller, $DocumentCategories, $MainCategories;
  if (count($DocumentCategories) != 0)
    return;
  $query = new QueryBuilder(EQueryType::qtSELECT, 'categories', DB_ANY);
  $query->orderBy(['parent_id', 'category_id']);
  if ($result = $Controller->select($query)) {
    while ($record = $result->fetch_assoc()) {
      $DocumentCategories[intval($record['category_id'])] = new Surcouf\PhpArchive\Document\Category($record);
      if (is_null($record['parent_id']))
        $MainCategories[intval($record['category_id'])] =& $DocumentCategories[intval($record['category_id'])];
    }
  }
  ksort($DocumentCategories);
  ksort($MainCategories);
}

$DocumentTypes = array();
$DocumentTypesByName = array();

function load_documentType($id) {
  global $Controller, $DocumentTypes, $DocumentTypesByName;
  if (count($DocumentTypes) != 0)
    return;
  $query = new QueryBuilder(EQueryType::qtSELECT, 'types', DB_ANY);
  $query->where('types', 'type_id', '=', intval($id));
  if ($result = $Controller->select($query)) {
    $result = $result->fetch_assoc();
    $DocumentTypes[intval($result['type_id'])] = new Surcouf\PhpArchive\Document\Type($result);
    $DocumentTypesByName[$DocumentTypes[intval($result['type_id'])]->getName()] =& $DocumentTypes[intval($result['type_id'])];
  }
  ksort($DocumentTypesByName);
}

function load_documentTypes() {
  global $Controller, $DocumentTypes, $DocumentTypesByName;
  if (count($DocumentTypes) != 0)
    return;
  $query = new QueryBuilder(EQueryType::qtSELECT, 'types', DB_ANY);
  if ($result = $Controller->select($query)) {
    while ($record = $result->fetch_assoc()) {
      $DocumentTypes[intval($record['type_id'])] = new Surcouf\PhpArchive\Document\Type($record);
      $DocumentTypesByName[$DocumentTypes[intval($record['type_id'])]->getName()] =& $DocumentTypes[intval($record['type_id'])];
    }
  }
  ksort($DocumentTypesByName);
}
