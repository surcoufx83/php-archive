<?php

namespace Surcouf\PhpArchive;

use Surcouf\PhpArchive\User\Session;

if (!defined('CORE2'))
  exit;

interface IUser {

  public function createNewSession($keepSession) : bool;
  public function getFirstname() : string;
  public function getId() : int;
  public function getInitials() : string;
  public function getLastname() : string;
  public function getLastActivityTime() : ?\DateTime;
  public function getMail() : string;
  public function getName() : string;
  public function getSession() : ?Session;
  public function getUsername() : string;
  public function loadFiles(int $folder, $tenant = null) : array;
  public function loadFolders(int $parent, $tenant = null) : array;
  public function verify($password) : bool;
  public function verifySession(string $session_token, string $session_password) : bool;

}
