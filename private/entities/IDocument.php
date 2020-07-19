<?php

namespace Surcouf\PhpArchive;

use Surcouf\PhpArchive\Document\Type;

if (!defined('CORE2'))
  exit;

interface IDocument {

  public function getDescription() : string;
  public function getId() : int;
  public function getName() : string;
  public function getOwner() : User;
  public function getOwnerId() : int;
  public function getType() : ?Type;
  public function getTypeId() : int;

}
