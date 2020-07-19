<?php

namespace Surcouf\PhpArchive;

use Surcouf\PhpArchive\Document\Category;
use Surcouf\PhpArchive\File\Extension;

if (!defined('CORE2'))
  exit;

interface IFile {

  public function getCategory() : ?Category;
  public function getCategoryId() : ?int;
  public function getChecksum() : string;
  public function getDocument() : ?Document;
  public function getDocumentId() : ?int;
  public function getEditTime() : ?\DateTime;
  public function getExtension() : ?Extension;
  public function getExtensionId() : ?int;
  public function getFolder() : ?Folder;
  public function getFolderId() : ?int;
  public function getId() : int;
  public function getName() : string;
  public function getOcrPageCount() : ?int;
  public function getPageCount() : int;
  public function getPreOcrCheckResult() : ?bool;
  public function getSize() : ?int;
  public function hasPreOcrCheckCompleted() : bool;
  public function isOcrCompleted() : bool;
  public function isOcrPossible() : ?bool;

}
