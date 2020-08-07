<?php

namespace Surcouf\PhpArchive;

use \DateTime;
use Surcouf\PhpArchive\Document\Category;
use Surcouf\PhpArchive\File\Extension;
use Surcouf\PhpArchive\Helper\ConverterHelper;

if (!defined('CORE2'))
  exit;

class File implements IFile, IDbObject {

  private $id, $folderid, $documentid, $categoryid, $extensionid;
  private $name, $edittime, $size, $pages, $checksum;
  private $preocrcheck, $ocrpossible, $ocrpages, $ocrcompleted, $haspreview;

  private $changes = array();

  function __construct($record) {
    $this->id = intval($record['file_id']);
    $this->folderid = is_null($record['folder_id']) ? null : intval($record['folder_id']);
    $this->documentid = is_null($record['document_id']) ? null : intval($record['document_id']);
    $this->categoryid = is_null($record['category_id']) ? null : intval($record['category_id']);
    $this->extensionid = is_null($record['ext_id']) ? null : intval($record['ext_id']);
    $this->name = $record['file_name'];
    $this->edittime = is_null($record['file_edittime']) ? null : new \DateTime($record['file_edittime']);
    $this->size = is_null($record['file_size']) ? null : intval($record['file_size']);
    $this->pages = is_null($record['file_pages']) ? null : intval($record['file_pages']);
    $this->checksum = $record['file_checksum'];
    $this->preocrcheck = is_null($record['file_preocr_checked']) ? null : ConverterHelper::to_bool($record['file_preocr_checked']);
    $this->ocrpossible = is_null($record['file_ocr_possible']) ? null : ConverterHelper::to_bool($record['file_ocr_possible']);
    $this->ocrpages = is_null($record['file_ocr_pages']) ? null : intval($record['file_ocr_pages']);
    $this->ocrcompleted = is_null($record['file_ocr_completed']) ? null : new \DateTime($record['file_ocr_completed']);
    $this->haspreview = is_null($record['file_preview_generated']) ? null : ConverterHelper::to_bool($record['file_preview_generated']);
  }

  public function getDbChanges() : array {
    return $this->changes;
  }

  public function getCategory() : ?Category {
    global $Controller;
    return $Controller->getCategory($this->categoryid);
  }

  public function getCategoryId() : ?int {
    return $this->categoryid;
  }

  public function getChecksum() : string {
    return $this->checksum;
  }

  public function getDocument() : ?Document {
    global $Controller;
    return $Controller->getDocument($this->documentid);
  }

  public function getDocumentId() : ?int {
    return $this->documentid;
  }

  public function getEditTime() : ?\DateTime {
    return $this->edittime;
  }

  public function getExtension() : ?Extension {
    global $Controller;
    return $Controller->getExtension($this->extensionid);
  }

  public function getExtensionId() : ?int {
    return $this->extensionid;
  }

  public function getFolder() : ?Folder {
    global $Controller;
    return $Controller->getFolder($this->folderid);
  }

  public function getFolderId() : ?int {
    return $this->folderid;
  }

  public function getId() : int {
    return $this->id;
  }

  public function getMount() : ?Mount {
    return $this->getFolder()->getMount();
  }

  public function getName() : string {
    return $this->name;
  }

  public function getOcrPageCount() : ?int {
    return $this->ocrpages;
  }

  public function getPageCount() : int {
    return $this->pages;
  }

  public function getPreOcrCheckResult() : ?bool {
    if (is_null($this->preocrcheck))
      return null;
    return $this->preocrcheck;
  }

  public function getSize() : ?int {
    return $this->size;
  }

  public function hasPreview() : bool {
    if (is_null($this->haspreview))
      return false;
    return $this->haspreview;
  }

  public function hasPreviewGenerated() : bool {
    return !is_null($this->haspreview);
  }

  public function hasPreOcrCheckCompleted() : bool {
    return !is_null($this->preocrcheck);
  }

  public function isOcrCompleted() : bool {
    return !is_null($this->ocrcompleted);
  }

  public function isOcrPossible() : ?bool {
    if (is_null($this->ocrpossible))
      return null;
    return $this->ocrpossible;
  }

}
