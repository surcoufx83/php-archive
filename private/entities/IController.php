<?php

namespace Surcouf\PhpArchive;

use Surcouf\PhpArchive\Database\QueryBuilder;
use Surcouf\PhpArchive\Document\Category;
use Surcouf\PhpArchive\Document\Type;
use Surcouf\PhpArchive\File\Extension;

if (!defined('CORE2'))
  exit;

interface IController {

  public function Config() : Config\Configuration;
  public function Dispatcher() : Dispatcher;
  public function User() : ?User;

  public function dbescape($value, bool $includeQuotes = true) : string;
  public function delete(QueryBuilder &$qbuilder) : bool;
  public function get(array $params) : void;
  public function getAddress($filter) : ?Address;
  public function getCategory($filter) : ?Category;
  public function getCountry($filter) : ?Country;
  public function getDocument($filter) : ?Document;
  public function getExtension($filter) : ?Extension;
  public function getFile($filter) : ?File;
  public function getFolder($filter) : ?Folder;
  public function getInsertId() : ?int;
  public function getLink($filter) : ?string;
  public function getType($filter) : ?Type;
  public function getUser($filter) : ?User;
  public function init() : void;
  public function insert(QueryBuilder &$qbuilder) : bool;
  public function insertSimple(string $table, array $columns, array $data) : int;
  public function isAuthenticated() : bool;
  public function loginWithPassword(string $username, string $password, bool $keepSession, bool $agreedStatement, Array &$response = null) : bool;
  public function logout() : void;
  public function on(string $method, array $params) : void;
  public function post(array $params) : void;
  public function put(array $params) : void;
  public function select(QueryBuilder &$qbuilder) : ?\mysqli_result;
  public function selectCountSimple(string $table, string $filterColumn=null, string $filterValue=null) : int;
  public function setSessionCookies(string $userCookie, string $tokenCookie, string $passwordCookie, bool $longDuration) : bool;
  public function tearDown() : void;
  public function update(QueryBuilder &$qbuilder) : bool;
  public function updateDbObject(IDbObject &$object) : void;

}
