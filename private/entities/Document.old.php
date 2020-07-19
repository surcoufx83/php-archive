<?php

namespace Surcouf\PhpArchive;

use Surcouf\PhpArchive\File\Ocr\EFileConvertFormat;
use Surcouf\PhpArchive\Permission\EDocumentPermission;

if (!defined('CORE2'))
  exit;

class DocumentOld {

  private $id, $file, $type, $category, $contract, $user;
  private $title, $description;
  private $changes = array();

  function __construct(&$fileobj, $data=null) {
    $this->file =& $fileobj;
    if (!is_null($data)) {
      global $DocumentCategories, $DocumentTypes;
      $this->id = $this->file->getId();
      $this->title = $data['document_title'];
      $this->description = $data['document_description'];
      if (array_key_exists(intval($data['type_id']), $DocumentTypes))
        $this->type =& $DocumentTypes[intval($data['type_id'])];
      if (array_key_exists(intval($data['category_id']), $DocumentCategories))
        $this->category =& $DocumentCategories[intval($data['category_id'])];
    }
  }

  function classify(&$response) {
    global $db, $DocumentCategories, $DocumentTypes;

    load_documentCategories();
    load_documentTypes();

    if (!$this->classify__initCaches($response))
      return $response;

    $sumwords = 0;
    $diswords = count($this->fulltextwords);
    foreach ($this->fulltextwords as $key => $value) {
      $sumwords += $value['count_word'];
    }

    $response = getConfiguredResponseArray(1);
    $response['Classification'] = array();

    if ($this->classifyDocumentsCategory($diswords, $sumwords, $response)) {
      if ($response['Classification']['Category']['TopDifference'] > 40) {
        if ($this->setCategory($response['Classification']['Category']['ClassifiedAs']['Id']))
          $response['Classification']['Category']['ClassifiedAs']['State'] = 'Changed';
        else
          $response['Classification']['Category']['ClassifiedAs']['State'] = 'Untouched';
      } else
        $response['Classification']['Category']['ClassifiedAs']['State'] = 'Rejected';
    }

    if ($this->classifyDocumentsType($diswords, $sumwords, $response)) {
      if ($response['Classification']['Type']['TopDifference'] > 40) {
        if ($this->setType($response['Classification']['Type']['ClassifiedAs']['Id']))
          $response['Classification']['Type']['ClassifiedAs']['State'] = 'Changed';
        else
          $response['Classification']['Type']['ClassifiedAs']['State'] = 'Untouched';
      } else
        $response['Classification']['Type']['ClassifiedAs']['State'] = 'Rejected';
    }

    if (!$this->pushToDb($dbresponse)) {
      if ($dbresponse['Result']['Error']['Code'] != 2)
        $response = $dbresponse;
      return false;
    }

    return true;

  }

  function classify__initCaches(&$response) {

    $this->ocrStats = $this->file->getOcrStatsCache();
    $this->file->loadOcr(false, false, false, 3, -1, 3, $this->fulltext, $this->fulltextwords);
    if (count($this->fulltextwords) == 0) {
      $response = getConfiguredResponseArray(809);
      return false;
    }

    return true;

  }

  function classifyAs($group, $id, &$response) {

    $isReClassified = false;

    if ($group == 'category')
      $isReClassified = $this->setCategory(intval($id), $response);
    else if ($group == 'type')
      $isReClassified = $this->setType(intval($id), $response);
    else
      $response = getConfiguredResponseArray(80);

    if ($isReClassified) {
      if (!$this->pushToDb($dbresponse)) {
        if ($dbresponse['Result']['Error']['Code'] != 2)
          $response = $dbresponse;
        return false;
      }
      return true;
    }

    return false;

  }

  function classifyDocumentsCategory($diswords, $sumwords, &$result) {
    global $db, $DocumentCategories;
    $query = 'SELECT `d`.`category_id`, `d`.`fs_id`, `fs`.`fs_pages`, COUNT(DISTINCT(`ifi`.`ft_id`)) AS `word_count_distinct`, COUNT(`ifi`.`ft_id`) AS `word_count`
              FROM `document_types` `dt`
              JOIN `documents` `d` ON `d`.`type_id` = `dt`.`type_id` AND `d`.`fs_id` != '.$this->file->getId().'
              JOIN `filesystem` `fs` ON `fs`.`fs_id` = `d`.`fs_id`
              JOIN `file_images` `fi` ON `fi`.`fs_id` = `d`.`fs_id` AND `fi`.`image_type`='.EFileConvertFormat::TSV.'
              JOIN `image_fulltext_indexes` `ifi` ON `ifi`.`image_id` = `fi`.`image_id` AND `ifi`.`ft_id` IN ('.implode(', ', array_keys($this->fulltextwords)).')
              GROUP BY `d`.`category_id`, `d`.`fs_id`, `fs`.`fs_pages`';
    $candidates = array();
    $res = $db->query($query);
    while($record = $res->fetch_assoc()) {
      $a = min($record['word_count_distinct'], $diswords) / max($record['word_count_distinct'], $diswords);
      $b = min($record['word_count'], $sumwords) / max($record['word_count'], $sumwords);
      $c = 0.0;

      $cat = intval($record['category_id']);
      $pages = intval($record['fs_pages']);

      if ($pages == $this->file->getPageCount())
        $c = 1.0;
      else if ($pages < $this->file->getPageCount())
        $c = $pages / $this->file->getPageCount();
      else
        $c = $this->file->getPageCount() / $pages;

      $x = ($a + $b + $c) / 3;

      if (!array_key_exists($cat, $candidates)) {
        $candidates[$cat] = array(
          'word_count_distinct' => intval($record['word_count_distinct']),
          'word_count' => intval($record['word_count']),
          'a' => $a,
          'b' => $b,
          'c' => $c,
          'x' => $x,
        );
      } else if ($x > $candidates[$cat]['x']) {
        $candidates[$cat] = array(
          'word_count_distinct' => intval($record['word_count_distinct']),
          'word_count' => intval($record['word_count']),
          'a' => $a,
          'b' => $b,
          'c' => $c,
          'x' => $x,
        );
      }

    }

    $xcandidate = array();
    foreach ($candidates as $key => $arr) {
      $x2 = intval(round($arr['x'] * 100));
      if (!array_key_exists($x2, $xcandidate))
        $xcandidate[$x2] = array();
      $xcandidate[$x2][] = $key;
    }

    krsort($xcandidate);

    $result['Classification']['Category'] = array(
      'ClassifiedAs' => false,
      'Candidates' => array(),
    );

    $i = 1;
    foreach ($xcandidate as $x => $arr) {
      if ($i == 1 && count($arr) == 1 && $x > 74) {
        $result['Classification']['Category']['ClassifiedAs'] = array(
          'Id' => $DocumentCategories[$arr[0]]->getId(),
          'Name' => $DocumentCategories[$arr[0]]->getName(),
          'Statistics' => array(
            'WordMatch' => $candidates[$arr[0]]['a'],
            'WordCountMatch' => $candidates[$arr[0]]['b'],
            'PageMatch' => $candidates[$arr[0]]['c'],
            'Points' => $candidates[$arr[0]]['x'],
          ),
        );
      }
      for ($c = 0; $c < count($arr); $c++) {
        $result['Classification']['Category']['Candidates']['c'.$i] = array(
          'Id' => $DocumentCategories[$arr[$c]]->getId(),
          'Name' => $DocumentCategories[$arr[$c]]->getName(),
          'Statistics' => array(
            'WordMatch' => $candidates[$arr[$c]]['a'],
            'WordCountMatch' => $candidates[$arr[$c]]['b'],
            'PageMatch' => $candidates[$arr[$c]]['c'],
            'Points' => $candidates[$arr[$c]]['x'],
          ),
        );
        $i++;
      }
      if ($i > 3)
        break;
    }

    if (count($result['Classification']['Category']['Candidates']) > 1) {
      $result['Classification']['Category']['TopDifference'] =
        floor($result['Classification']['Category']['Candidates']['c1']['Statistics']['Points'] * 100 -
        $result['Classification']['Category']['Candidates']['c2']['Statistics']['Points'] * 100);
    } else
      $result['Classification']['Category']['TopDifference'] = 100;

    return is_array($result['Classification']['Category']['ClassifiedAs']);

  }

  function classifyDocumentsType($diswords, $sumwords, &$result) {
    global $db, $DocumentTypes;
    $query = 'SELECT `d`.`type_id`, `d`.`fs_id`, `fs`.`fs_pages`, COUNT(DISTINCT(`ifi`.`ft_id`)) AS `word_count_distinct`, COUNT(`ifi`.`ft_id`) AS `word_count`
              FROM `document_types` `dt`
              JOIN `documents` `d` ON `d`.`type_id` = `dt`.`type_id` AND `d`.`fs_id` != '.$this->file->getId().'
              JOIN `filesystem` `fs` ON `fs`.`fs_id` = `d`.`fs_id`
              JOIN `file_images` `fi` ON `fi`.`fs_id` = `d`.`fs_id` AND `fi`.`image_type`='.EFileConvertFormat::TSV.'
              JOIN `image_fulltext_indexes` `ifi` ON `ifi`.`image_id` = `fi`.`image_id` AND `ifi`.`ft_id` IN ('.implode(', ', array_keys($this->fulltextwords)).')
              GROUP BY `d`.`type_id`, `d`.`fs_id`, `fs`.`fs_pages`';
    $candidates = array();
    $res = $db->query($query);
    while($record = $res->fetch_assoc()) {
      $a = min($record['word_count_distinct'], $diswords) / max($record['word_count_distinct'], $diswords);
      $b = min($record['word_count'], $sumwords) / max($record['word_count'], $sumwords);
      $c = 0.0;

      $type = intval($record['type_id']);
      $pages = intval($record['fs_pages']);

      if ($pages == $this->file->getPageCount())
        $c = 1.0;
      else if ($pages < $this->file->getPageCount())
        $c = $pages / $this->file->getPageCount();
      else
        $c = $this->file->getPageCount() / $pages;

      $x = ($a + $b + $c) / 3;

      if (!array_key_exists($type, $candidates)) {
        $candidates[$type] = array(
          'word_count_distinct' => intval($record['word_count_distinct']),
          'word_count' => intval($record['word_count']),
          'a' => $a,
          'b' => $b,
          'c' => $c,
          'x' => $x,
        );
      } else if ($x > $candidates[$type]['x']) {
        $candidates[$type] = array(
          'word_count_distinct' => intval($record['word_count_distinct']),
          'word_count' => intval($record['word_count']),
          'a' => $a,
          'b' => $b,
          'c' => $c,
          'x' => $x,
        );
      }

    }

    $xcandidate = array();
    foreach ($candidates as $key => $arr) {
      $x2 = intval(round($arr['x'] * 100));
      if (!array_key_exists($x2, $xcandidate))
        $xcandidate[$x2] = array();
      $xcandidate[$x2][] = $key;
    }
    krsort($xcandidate);

    $result['Classification']['Type'] = array(
      'ClassifiedAs' => false,
      'Candidates' => array(
        1 => array(),
        2 => array(),
        3 => array(),
      ),
    );

    $i = 1;
    foreach ($xcandidate as $x => $arr) {
      if ($i == 1 && count($arr) == 1 && $x > 74) {
        $result['Classification']['Type']['ClassifiedAs'] = array(
          'Id' => $DocumentTypes[$arr[0]]->getId(),
          'Name' => $DocumentTypes[$arr[0]]->getName(),
          'Statistics' => array(
            'WordMatch' => $candidates[$arr[0]]['a'],
            'WordCountMatch' => $candidates[$arr[0]]['b'],
            'PageMatch' => $candidates[$arr[0]]['c'],
            'Points' => $candidates[$arr[0]]['x'],
          ),
        );
      }
      for ($c = 0; $c < count($arr); $c++) {
        $result['Classification']['Type']['Candidates']['c'.$i] = array(
          'Id' => $DocumentTypes[$arr[$c]]->getId(),
          'Name' => $DocumentTypes[$arr[$c]]->getName(),
          'Statistics' => array(
            'WordMatch' => $candidates[$arr[$c]]['a'],
            'WordCountMatch' => $candidates[$arr[$c]]['b'],
            'PageMatch' => $candidates[$arr[$c]]['c'],
            'Points' => $candidates[$arr[$c]]['x'],
          ),
        );
        $i++;
      }
      if ($i > 3)
        break;
    }

    if (count($result['Classification']['Type']['Candidates']) > 1) {
      $result['Classification']['Type']['TopDifference'] =
        floor($result['Classification']['Type']['Candidates']['c1']['Statistics']['Points'] * 100 -
        $result['Classification']['Type']['Candidates']['c2']['Statistics']['Points'] * 100);
    } else
      $result['Classification']['Type']['TopDifference'] = 100;

    return is_array($result['Classification']['Type']['ClassifiedAs']);

  }

  function getCategory() {
    return $this->category;
  }

  function getContract() {
    return $this->contract;
  }

  function getDescription() {
    return $this->description;
  }

  function getFile() {
    return $this->file;
  }

  function getLink() {
    return '<a href="/document/'.$this->id.'">'.$this->title.'</a>';
  }

  function getTitle() {
    return $this->title;
  }

  function getType() {
    return $this->type;
  }

  function getUser() {
    return $this->user;
  }

  function pushToDb(&$result=null) {
    global $db;
    if (is_null($this->id)) {
      $query = 'INSERT INTO `documents`(`fs_id`, `type_id`, `category_id`) VALUES ';
      $query .= '(' .intval($this->file->getId());
      $query .= ', '.(is_null($this->type) ? 'NULL' : intval($this->type->getId()));
      $query .= ', '.(is_null($this->category) ? 'NULL' : intval($this->category->getId())).')';
      if ($db->query($query)) {
        $this->id = $this->file->getId();
        $result = getConfiguredResponseArray(1);
        return true;
      }
      $result = getConfiguredResponseArray(202);
      return false;
    }
    if (count($this->changes) != 0) {
      $query = 'UPDATE `documents` SET '.implode(', ', $this->changes).' WHERE `fs_id`='.$this->id;
      if ($db->query($query)) {
        $result = getConfiguredResponseArray(1);
        return true;
      }
      $result = getConfiguredResponseArray(203);
      return false;
    }
    $result = getConfiguredResponseArray(2);
    return false;
  }

  function setCategory($id, &$result=null) {
    global $DocumentCategories, $Router;
    if (!$Controller->User()->may(DOCUMENTS, EDocumentPermission::AssignCategory)) {
      $result = getConfiguredResponseArray(120);
      return false;
    }

    load_documentCategories();
    if (is_null($this->category) || $id != $this->category->getId()) {
      if (array_key_exists($id, $DocumentCategories)) {
        $this->changes[] = '`category_id`=\''.intval($id).'\'';
        $this->category =& $DocumentCategories[$id];
        $result = getConfiguredResponseArray(1);
        return true;
      } else {
        $result = getConfiguredResponseArray(80);
        return false;
      }
    }
    $result = getConfiguredResponseArray(2);
    return false;
  }

  function setType($id, &$result=null) {
    global $DocumentTypes, $Router;
    if (!$Controller->User()->may(DOCUMENTS, EDocumentPermission::AssignType)) {
      $result = getConfiguredResponseArray(120);
      return false;
    }

    load_documentTypes();
    if (is_null($this->type) || $id != $this->type->getId()) {
      if (array_key_exists($id, $DocumentTypes)) {
        $this->changes[] = '`type_id`=\''.intval($id).'\'';
        $this->type =& $DocumentTypes[$id];
        $result = getConfiguredResponseArray(1);
        return true;
      } else {
        $result = getConfiguredResponseArray(80);
        return false;
      }
    }
    $result = getConfiguredResponseArray(2);
    return false;
  }

  function updateMeta($payload, &$result=null) {
    global $db, $Router;
    if (!$Controller->User()->may(DOCUMENTS, EDocumentPermission::EditMetadata)) {
      $result = getConfiguredResponseArray(120);
      return false;
    }
    if (!array_key_exists('document', $payload)) {
      $result = getConfiguredResponseArray(80);
      return false;
    }
    if (array_key_exists('title', $payload['document'])) {
      $this->changes[] = '`document_title`=\''.$db->real_escape_string($payload['document']['title']).'\'';
      $this->title = $payload['document']['title'];
    }

    return $this->pushToDb($result);
  }

}
