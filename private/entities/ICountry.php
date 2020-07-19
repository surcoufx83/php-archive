<?php

namespace Surcouf\PhpArchive;

if (!defined('CORE2'))
  exit;

interface ICountry {

  public function getCode() : string;
  public function getEnvelopeName() : string;
  public function getId() : int;
  public function getName() : string;
  public function getNameEn() : string;
  public function getZipPattern() : string;
  public function showOnEnvelope() : bool;
  public function validateZip(string $testZip) : bool;

}
