<?php

namespace Surcouf\PhpArchive;

use \DirectoryIterator;
use \SplFileInfo;
use Surcouf\PhpArchive\Config\Icon;
use Surcouf\PhpArchive\Mount\EMountType;

if (!defined('CORE2'))
  exit;

class Mount {

  private $id, $icon, $name, $path, $type;
  private $rootfile;
  private $childs = array(), $stats = array();

  function __construct($data) {
    global $IconUnknown;
    $this->id = intval($data['mount_id']);
    $this->icon = $data['mount_icon'] == null ? $IconUnknown : new Icon(json_decode($data['mount_icon'], true)['icon']);
    $this->name = $data['mount_title'];
    $this->path = $data['mount_path'];
    $this->type = intval($data['mount_type']);
    if (array_key_exists('fs_id', $data)) {
      if (!is_null($data['fs_id'])) {
        global $Files;
        register_file($data);
        $this->rootfile =& $Files[intval($data['fs_id'])];
      }
    }
  }

  function addChild($childId) {
    global $Files;
    $this->childs[$Files[$childId]->getName()] =& $Files[$childId];
  }

  function getDirCount($applyFormat = false) {
    if (!array_key_exists(1, $this->stats)) {
      if ($applyFormat)
        return formatInt(0);
      return 0;
    }
    if ($applyFormat)
      return formatInt($this->stats[1]['entries']-1);
    return $this->stats[1]['entries']-1;
  }

  function getDirCountStr() {
    return $this->getDirCount(true).' '.getSnPl($this->getDirCount(), 'Verzeichnis', 'Verzeichnisse');
  }

  function getDirStatistics() {
    return $this->stats[1];
  }

  function getFileCount($applyFormat = false) {
    if (!array_key_exists(0, $this->stats)) {
      if ($applyFormat)
        return formatInt(0);
      return 0;
    }
    if ($applyFormat)
      return formatInt($this->stats[0]['entries']);
    return $this->stats[0]['entries'];
  }

  function getFileCountStr() {
    return $this->getFileCount(true).' '.getSnPl($this->getFileCount(), 'Datei', 'Dateien');
  }

  function getFileSize($applyFormat = false) {
    if (!array_key_exists(0, $this->stats)) {
      if ($applyFormat)
        return formatInt(0);
      return 0;
    }
    if ($applyFormat)
      return formatInt($this->stats[0]['size']);
    return $this->stats[0]['size'];
  }

  function getFileSizeStr() {
    return getReadableSize($this->getFileSize());
  }

  function getFileStatistics() {
    return $this->stats[0];
  }

  public function getIcon($p) {
    return $this->icon->getIcon($p);
  }

  public function getIconObject() {
    return $this->icon;
  }

  function getId() {
    return $this->id;
  }

  function getLink() {
    if (!$this->hasRoot())
      return null;
    return '/files/'.$this->getRoot()->getId();
  }

  function getName() {
    return $this->name;
  }

  function getPath() {
    return $this->path;
  }

  function getRoot() {
    return $this->rootfile;
  }

  function getStatistics() {
    return $this->stats;
  }

  function getType() {
    return $this->type;
  }

  function hasRoot() {
    return !is_null($this->rootfile);
  }

  function isInbox() {
    return ($this->type == EMountType::ScanInput || $this->type == EMountType::EMailInput);
  }

  function isTrash() {
    return ($this->type == EMountType::TrashDir);
  }

  function removeChild($childId) {
    global $Files;
    unset($this->childs[$Files[$childId]->getName()]);
  }

  function rescan(&$response) {
    global $Files, $Router;

    if (!is_dir($this->path)) {
      $response = getConfiguredResponseArray(603);
      return false;
    }

    if (!is_readable($this->path)) {
      $response = getConfiguredResponseArray(608);
      return false;
    }

    load_mountFiles($this->id);

    $stats = array(
      'Dirs' => array(
        'Checked' => 0,
        'Added' => 0,
        'Removed' => 0,
      ),
      'Files' => array(
        'Checked' => 0,
        'Added' => 0,
        'Removed' => 0,
      )
    );

    $this->rescan__removeOldEntries($stats);

    if (!$this->rescan__scanInboxes_iterateDirectory($stats)) {
      $response = getConfiguredResponseArray(609);
      return false;
    }

    $response = getConfiguredResponseArray(1);
    if (!ISCONSOLE) {
      load_mountStatistics();
      $response['Response']['Actions'] = $stats;
      $response['Replacers'] = array(
        'mount-'.$this->id.'-bytecount' => $this->getFileSizeStr(),
        'mount-'.$this->id.'-filecount' => $this->getFileCountStr(),
      );
    }
    return true;

  }

  private function rescan__removeOldEntries(&$stats) {
    global $Files;
    $remfiles = array();
    foreach ($Files AS $key => $obj) {
      if ($obj->getMountId() == $this->id) {
        if ($obj->isFile())
          $stats['Files']['Checked']++;
        else
          $stats['Dirs']['Checked']++;
        if (!$obj->stillExists()) {
          if ($obj->isFile())
            $stats['Files']['Removed']++;
          else
            $stats['Dirs']['Removed']++;
          $remfiles[] = $key;
          $obj->purge();
        }
      }
    }
    for ($i=0; $i<count($remfiles); $i++) {
      unset($Files[$i]);
    }
  }

  private function rescan__scanInboxes_iterateDirectory(&$stats, $subPath = '', $parent = null) {
    global $Files, $FilePaths;
    $curpath = FilesystemHelper::paths_combine($this->getPath(), $subPath);
    $dirObject = new File(new SplFileInfo($curpath), $this, $parent);
    if (!array_key_exists($dirObject->getPath(), $FilePaths)) {
      $dirObject->updateDatabase();
      $stats['Dirs']['Added']++;
      $Files[$dirObject->getId()] = $dirObject;
      $FilePaths[$dirObject->getPath()] =& $Files[$dirObject->getId()];
    } else {
      $dirObject =& $FilePaths[$dirObject->getPath()];
    }

    $dir = new DirectoryIterator($curpath);
    foreach ($dir AS $fi) {
      if ($fi->isDot())
        continue;
      $fileObject = new File($fi, $this, $dirObject);
      if (!array_key_exists($fileObject->getPath(), $FilePaths)) {
        $fileObject->updateDatabase();
        if ($fileObject->isDir())
          $stats['Dirs']['Added']++;
        else
          $stats['Files']['Added']++;
        $Files[$fileObject->getId()] = $fileObject;
        $FilePaths[$fileObject->getPath()] =& $Files[$fileObject->getId()];
        $fileObject =& $Files[$fileObject->getId()];
        if ($fileObject->getParent() != null)
          $fileObject->getParent()->addChild($fileObject->getId());
        if ($fileObject->getMount() != null)
          $fileObject->getMount()->addChild($fileObject->getId());
      } else {
        $fileObject =& $FilePaths[$fileObject->getPath()];
        if ($fileObject->isChanged())
          $fileObject->updateDatabase();
      }
      if ($fi->isDir()) {
        $this->rescan__scanInboxes_iterateDirectory($stats, FilesystemHelper::paths_combine($subPath, $fi->getFilename()), $dirObject);
      }
    }
    return true;
  }

  function setStatistics(array $newstats) {
    $this->stats = $newstats;
  }

}
