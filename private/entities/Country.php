<?php

namespace Surcouf\PhpArchive;

if (!defined('CORE2'))
  exit;

class Country implements ICountry {

  private $id, $code, $name, $namede, $inenvelope, $envelopename, $zippattern;

  function __construct(array $record) {
    $this->id = intval($record['country_id']);
    $this->code = $record['country_code'];
    $this->name = $record['country_name'];
    $this->namede = $record['country_name_de'];
    $this->inenvelope = getBool($record['country_envelope_show']);
    $this->envelopename = $record['country_envelope_name'];
    $this->zippattern = $record['country_zip_pattern'];
  }

  public function getCode() : string {
    return $this->code;
  }

  public function getEnvelopeName() : string {
    return $this->envelopename;
  }

  public function getId() : int {
    return $this->id;
  }

  public function getName() : string {
    return $this->namede;
  }

  public function getNameEn() : string {
    return $this->name;
  }

  public function getZipPattern() : string {
    return $this->zippattern;
  }

  public function showOnEnvelope() : bool {
    return $this->inenvelope;
  }

  public function validateZip(string $testZip) : bool {
    return preg_match('/^'.$this->zippattern.'$/', $testZip);
  }

}
