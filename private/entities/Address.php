<?php

namespace Surcouf\PhpArchive;

if (!defined('CORE2'))
  exit;

class Address implements IAddress {

  private $id, $title;
  private $line1, $line2, $line3, $line4;
  private $zip, $city, $countryid;

  public function __construct($record) {
    $this->id = intval($record['address_id']);
    $this->title = $record['address_title'];
    $this->line1 = $record['address_line1'];
    $this->line2 = $record['address_line2'];
    $this->line3 = $record['address_line3'];
    $this->line4 = $record['address_line4'];
    $this->zip = $record['address_zip'];
    $this->city = $record['address_city'];
    $this->countryid = intval($record['country_id']);
  }

  public function getCity() : string {
    return $this->city;
  }

  public function getCountry() : ?Country {
    global $Controller;
    return $Controller->getCountry($this->countryid);
  }

  public function getCountryId() : string {
    return $this->countryid;
  }

  public function getId() : int {
    return $this->id;
  }

  public function getLine1() : string {
    return $this->line1;
  }

  public function getLine2() : string {
    return $this->line2;
  }

  public function getLine3() : string {
    return $this->line3;
  }

  public function getLine4() : string {
    return $this->line4;
  }

  public function getName() : string {
    return $this->title;
  }

  public function getZip() : string {
    return $this->zip;
  }

}
