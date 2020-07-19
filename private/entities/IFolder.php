<?php

namespace Surcouf\PhpArchive;

if (!defined('CORE2'))
  exit;

interface IFolder {

  public function getId() : int;
  public function getMount() : ?Mount;
  public function getMountId() : ?int;
  public function getName() : string;
  public function getParent() : ?Folder;
  public function getParentId() : ?int;

}
