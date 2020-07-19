<?php

namespace Surcouf\PhpArchive;

if (!defined('CORE2'))
  exit;

interface IAddress {

  public function getCity() : string;
  public function getCountry() : ?Country;
  public function getCountryId() : string;
  public function getId() : int;
  public function getLine1() : string;
  public function getLine2() : string;
  public function getLine3() : string;
  public function getLine4() : string;
  public function getName() : string;
  public function getZip() : string;

}
