<?php

namespace Surcouf\PhpArchive;

use Surcouf\PhpArchive;
use Surcouf\PhpArchive\Controller;
use Surcouf\PhpArchive\IController;
use Surcouf\PhpArchive\IUser;
use Surcouf\PhpArchive\Database\EQueryType;
use Surcouf\PhpArchive\Database\QueryBuilder;
use Surcouf\PhpArchive\User\Session;
use \DateTime;

if (!defined('CORE2'))
  exit;

class User implements IUser, IDbObject {

  private $controller = null;
  private $id, $firstname, $lastname, $name, $initials, $loginname, $passwordhash, $mailadress;
  private $mailvalidationcode, $mailvalidated, $lastactivity;

  private $changes = array();

  public function __construct(IController &$Controller, $dr) {
    $this->controller = $Controller;
    $this->id = intval($dr['user_id']);
    $this->firstname = $dr['user_firstname'];
    $this->lastname = $dr['user_lastname'];
    $this->name = $dr['user_fullname'];
    $this->initials = strtoupper(substr($this->firstname, 0, 1).substr($this->lastname, 0, 1));
    $this->loginname = $dr['user_name'];
    $this->passwordhash = $dr['user_password'];
    $this->mailadress = $dr['user_email'];
    $this->mailvalidationsent = (!is_null($dr['user_email_validation']) ? $dr['user_email_validation'] : '');
    $this->mailvalidated = (!is_null($dr['user_email_validated']) ? new DateTime($dr['user_email_validated']) : '');
    $this->lastactivity = (!is_null($dr['user_last_activity']) ? new DateTime($dr['user_last_activity']) : '');
  }

  public function createNewSession($keepSession) : bool {

    $session_token = generateRandomToken(16);
    $session_password = generateRandomToken(24);

    $session_password4hash = hash('crc32b', substr($session_token, 0, 16));
    $session_password4hash .= $session_password;
    $session_password4hash .= hash('crc32b', substr($session_token, 16));

    $hash_token = password_hash($session_token, PASSWORD_ARGON2I, ['threads' => 12]);
    $hash_password = password_hash($session_password4hash, PASSWORD_ARGON2I, ['threads' => 12]);

    if ($this->controller->setSessionCookies($this->loginname, $session_token, $session_password, $keepSession)) {
      $query = new QueryBuilder(EQueryType::qtINSERT, 'user_logins');
      $query->columns(['user_id', 'login_type', 'login_token', 'login_password', 'login_keep'])
            ->values([$this->id, 1, $hash_token, $hash_password, $keepSession]);
      if ($this->controller->insert($query)) {
        $this->session = new Session($this, array(
          'login_id' => 0,
          'user_id' => $this->id,
          'login_time' => (new \DateTime())->format('Y-m-d H:i:s'),
          'login_keep' => $keepSession,
        ));
        return true;
      }
    }
    return false;
  }

  public function getDbChanges() : array {
    return $this->changes;
  }

  public function getFirstname() : string {
    return $this->firstname;
  }

  public function getId() : int {
    return $this->id;
  }

  public function getInitials() : string {
    return $this->initials;
  }

  public function getLastname() : string {
    return $this->lastname;
  }

  public function getLastActivityTime() : ?\DateTime {
    return $this->lastactivity;
  }

  public function getMail() : string {
    return $this->mailadress;
  }

  public function getName() : string {
    return $this->name;
  }

  public function getProfileLink() : string {
    return '/user/'.$this->id;
  }

  public function getSession() : ?Session {
    return $this->session;
  }

  public function getUsername() : string {
    return $this->loginname;
  }

  public function loadFiles(int $folder, $tenant = null) : array {
    $query = new QueryBuilder(EQueryType::qtSELECT, 'files', DB_ANY);
    $query
          ->select('categories', DB_ANY)
          ->select('files', DB_ANY)
          ->select('documents', DB_ANY)
          ->select('types', DB_ANY)
          ->select('folders', DB_ANY)
          ->select('mounts', DB_ANY)
          ->join('categories', ['categories', 'category_id', '=', 'files', 'category_id'])
          ->join('documents', ['documents', 'document_id', '=', 'files', 'document_id'])
          ->join('types', ['types', 'type_id', '=', 'documents', 'doctype_id'])
          ->join('folders', ['folders', 'folder_id', '=', 'files', 'folder_id'])
          ->join('mounts', ['mounts', 'mount_id', '=', 'folders', 'mount_id'])
          ->where('files', 'folder_id', '=', $folder)
          ->andWhere('mounts', 'tenant_id', '=', $this->getCurrentTenantId())
          ->orderBy('files', 'file_name');
    $result = $this->controller->select($query);
    $files = array();
    while ($record = $result->fetch_assoc()) {
      $files[] = $this->controller->getFile($record)->getId();
    }
    return $files;
  }

  public function loadFolders(int $parent, $tenant = null) : array {
    $query = new QueryBuilder(EQueryType::qtSELECT, 'folders', DB_ANY);
    $query
          ->select('mounts', DB_ANY)
          ->join('mounts', ['mounts', 'mount_id', '=', 'folders', 'mount_id'])
          ->where('folders', 'parent_id', '=', $parent)
          ->andWhere('mounts', 'tenant_id', '=', $this->getCurrentTenantId())
          ->orderBy('folders', 'folder_name');
    $result = $this->controller->select($query);
    $folders = array();
    while ($record = $result->fetch_assoc()) {
      $folders[] = $this->controller->getFolder($record)->getId();
    }
    return $folders;
  }

  public function verify($password) : bool {
    $start = microtime(true);
    if (password_verify($password, $this->passwordhash)) {
      if (password_needs_rehash($this->passwordhash, PASSWORD_ARGON2I, ['threads' => 12])) {
        $this->passwordhash = password_hash($password, PASSWORD_ARGON2I, ['threads' => 12]);
        $this->changes['user_password'] = $this->passwordhash;
        $this->controller->updateDbObject($this);
      }
      return true;
    }
    return false;
  }

  public function verifySession(string $session_token, string $session_password) : bool {
    $query = new QueryBuilder(EQueryType::qtSELECT, 'user_logins', DB_ANY);
    $query->where('user_logins', 'user_id', '=', $this->id)
          ->orderBy([['login_time', 'DESC']]);
    if ($result = $this->controller->select($query)) {
      while ($record = $result->fetch_assoc()) {
        if (password_verify($session_token, $record['login_token'])) {
          $pwdhash = hash('crc32b', substr($session_token, 0, 16));
          $pwdhash .= $session_password;
          $pwdhash .= hash('crc32b', substr($session_token, 16));
          if (password_verify($pwdhash, $record['login_password'])) {
            $uptime = new DateTime();

            $query = new QueryBuilder(EQueryType::qtUPDATE, 'user_logins');
            $query->update(['login_time' => $uptime->format('Y-m-d H:i:s')]);
            $query->where('user_logins', 'login_id', '=', intval($record['login_id']));
            $this->controller->update($query);

            $record['login_time'] = $uptime->format('Y-m-d H:i:s');
            $this->session = new Session($this, $record);
            return true;
          }
        }
      }
    }
    return false;
  }

}
