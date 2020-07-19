<?php

namespace Surcouf\PhpArchive;

use Surcouf\PhpArchive\Config;
use Surcouf\PhpArchive\Database\DbConf;
use Surcouf\PhpArchive\Database\EAggregationType;
use Surcouf\PhpArchive\Database\EQueryType;
use Surcouf\PhpArchive\Database\QueryBuilder;
use Surcouf\PhpArchive\Document\Category;
use Surcouf\PhpArchive\Document\Type;
use Surcouf\PhpArchive\File\Extension;

if (!defined('CORE2'))
  exit;

class Controller implements IController {

  private $database, $currentUser;
  private $config, $dispatcher;

  private $addresses = array();
  private $categories = array(), $categoriesByName = array();
  private $countries = array();
  private $documents = array();
  private $extensions = array(), $extensionsByName = array();
  private $files = array(), $filesByName = array();
  private $folders = array(), $foldersByName = array();
  private $types = array();
  private $users = array();

  private $changedObjects = array();

  public function Config() : Config\Configuration {
    return $this->config;
  }

  public function Dispatcher() : Dispatcher {
    return $this->dispatcher;
  }

  public function User() : ?User {
    return $this->currentUser;
  }

  public function dbescape($value, bool $includeQuotes = true) : string {
    $value = $this->database->real_escape_string($value);
    if ($includeQuotes && !is_integer($value))
      $value = '\''.$value.'\'';
    return $value;
  }

  public function delete(QueryBuilder &$qbuilder) : bool {
    $query = $qbuilder->buildQuery();
    $result = $this->database->query($query);
    return $result;
  }

  public function get(array $params) : void {
    $this->dispatcher->get($params);
  }

  public function getAddress($filter) : ?Address {
    if (is_integer($filter)) {
      if (!array_key_exists($filter, $this->addresses))
        return $this->loadAddress($filter);
      else
        return $this->addresses[$filter];
    }
    return null;
  }

  public function getCategory($filter) : ?Category {
    if (is_integer($filter)) {
      if (!array_key_exists($filter, $this->categories))
        return $this->loadCategory($filter);
      else
        return $this->categories[$filter];
    }
    if (is_array($filter))
      return $this->registerCategory(null, $filter);
    return null;
  }

  public function getCountry($filter) : ?Country {
    if (is_integer($filter)) {
      if (!array_key_exists($filter, $this->countries))
        return $this->loadCountry($filter);
      else
        return $this->countries[$filter];
    }
    return null;
  }

  public function getDocument($filter) : ?Document {
    if (is_integer($filter)) {
      if (!array_key_exists($filter, $this->documents))
        return $this->loadDocument($filter);
      else
        return $this->documents[$filter];
    }
    if (is_array($filter))
      return $this->registerDocument(null, $filter);
    return null;
  }

  public function getExtension($filter) : ?Extension {
    if (is_integer($filter)) {
      if (!array_key_exists($filter, $this->extensions))
        return $this->loadExtension($filter);
      else
        return $this->extensions[$filter];
    }
    if (is_array($filter))
      return $this->registerExtension(null, $filter);
    return null;
  }

  public function getFile($filter) : ?File {
    if (is_integer($filter)) {
      if (!array_key_exists($filter, $this->files))
        return $this->loadFile($filter);
      else
        return $this->files[$filter];
    }
    if (is_array($filter))
      return $this->registerFile(null, $filter);
    return null;
  }

  public function getFolder($filter) : ?Folder {
    if (is_integer($filter)) {
      if (!array_key_exists($filter, $this->folders))
        return $this->loadFolder($filter);
      else
        return $this->folders[$filter];
    }
    if (is_array($filter))
      return $this->registerFolder(null, $filter);
    return null;
  }

  public function getInsertId() : ?int {
    return $this->database->insert_id;
  }

  public function getLink($filter) : ?string {

    $items = explode(':', $filter);

    switch($items[0]) {
      case 'admin':
        return $this->getLink_Admin($items);
      case 'ajax':
        return $this->getLink_Ajax($items);
      case 'dropzone':
        return $this->getLink_Dropzone($items);
      case 'maintenance':
        return '/maintenance';
      case 'private':
        return $this->getLink_Private($items);
      case 'workflow':
        return $this->getLink_Workflow($items);

    }

    return null;
  }

  private function getLink_Ajax(array $params) : ?string {
    switch($params[1]) {
      case 'admin':
      return $this->getLink_AjaxAdmin($params);
      break;
    }
    return null;
  }

  private function getLink_AjaxAdmin(array $params) : ?string {
    switch($params[2]) {
      case 'address':
        switch($params[3]) {
          case 'create':
            return '/ajax/admin/address/create';
          case 'search':
            return '/ajax/admin/search/address';
        }
        return null;
    }
    return null;
  }

  private function getLink_Admin(array $params) : ?string {
    switch($params[1]) {
      case 'address':
        return '/admin/address/'.$params[2];
      case 'addresses':
        return '/admin/addresses';
      case 'cronjobs':
        return '/admin/cronjobs';
      case 'logs':
        return '/admin/logs';
      case 'main':
        return '/admin';
      case 'settings':
        return '/admin/settings';
      case 'storage':
        return '/admin/storage';
      case 'user':
        return '/admin/user/'.$params[2];
      case 'users':
        return '/admin/users';
    }
    return null;
  }

  private function getLink_Dropzone(array $params) : ?string {
    switch($params[1]) {
      case 'main':
        return '/dropzone';
    }
    return null;
  }

  private function getLink_Private(array $params) : ?string {
    switch($params[1]) {
      case 'avatar':
        return '/pictures/avatars/'.$params[2];
      case 'categories':
        return '/categories'.(array_key_exists(2, $params) ? '/'.$params[2] : '');
      case 'category':
        return '/category/'.$params[2];
      case 'dashboard':
        return '/dashboard';
      case 'file':
        return '/file/'.$params[2];
      case 'files':
        return '/files'.(array_key_exists(2, $params) ? '/'.$params[2] : '');
      case 'login':
        return '/login';
      case 'logout':
        return '/logout';
      case 'home':
        return '/';
      case 'search':
        return '/search';
      case 'settings':
        return '/settings';
      case 'types':
        return '/types'.(array_key_exists(2, $params) ? '/'.$params[2] : '');
      case 'type':
        return '/type/'.$params[2];
    }
    return null;
  }

  private function getLink_Workflow(array $params) : ?string {
    switch($params[1]) {
      case 'main':
        return '/workflow';
      case 'inbox':
        return '/workflow/inbox';
    }
    return null;
  }

  public function getType($filter) : ?Type {
    if (is_integer($filter)) {
      if (!array_key_exists($filter, $this->types))
        return $this->loadExtension($filter);
      else
        return $this->types[$filter];
    }
    if (is_array($filter))
      return $this->registerType(null, $filter);
    return null;
  }

  public function getUser($filter=null) : ?User {
    if (is_integer($filter)) {
      if (!array_key_exists($filter, $this->users))
        return $this->loadUser($filter);
      else
        return $this->users[$filter];
    }
    if (is_string($filter)) {
      return $this->loadUsername($filter);
    }
    var_dump($filter);
    //debug_print_backtrace();
    exit;
    // tbd
    return null;
  }

  public function init() : void {
    if (!$this->init_Database())
      exit;
    if (!$this->init_Config())
      exit;
    if (!$this->init_Dispatcher())
      exit;
  }

  private function init_Config() : bool {
    $this->config = new Config\Configuration();
    $result = null;
    $query = new QueryBuilder(EQueryType::qtSELECT, 'config', DB_ANY);
    $query->orderBy(['parent_id', 'config_id']);
    $result = $this->select($query);
    if (!$result)
      return false;
    while ($record = $result->fetch_assoc()) {
      $this->config->addChild($record);
    }
    define('MAINTENANCE', $this->config->Maintenance->Enabled->getBool());
    return true;
  }

  private function init_Database() : bool {
    try {
      $this->database = new \Mysqli(DbConf::DB_HOST, DbConf::DB_USER, DbConf::DB_PASSWORD, DbConf::DB_DATABASE);
      if ($this->database->connect_errno != 0) {
        exit('Error connecting to the database.');
      }
      $this->database->set_charset('utf8mb4');
      return true;
    }
    catch (\Exception $e) {
      exit('Error connecting to the database.');
    }
  }

  private function init_Dispatcher() : bool {
    $this->dispatcher = new Dispatcher($this);
    $this->login();
    return true;
  }

  public function insert(QueryBuilder &$qbuilder) : bool {
    $query = $qbuilder->buildQuery();
    $result = $this->database->query($query);
    return $result;
  }

  public function insertSimple(string $table, array $columns, array $data) : int {
    $query = new QueryBuilder(EQueryType::qtINSERT, $table);
    $query->columns($columns)
          ->values($data);
    if ($this->insert($query)) {
      return $this->getInsertId();
    }
    return -1;
  }

  public function isAuthenticated() : bool {
    return !is_null($this->currentUser);
  }

  private function loadAddress(int $id) : ?Address {
    $query = new QueryBuilder(EQueryType::qtSELECT, 'addresses', DB_ANY);
    $query->where('addresses', 'address_id', '=', $id);
    $result = $this->select($query);
    if ($record = $result->fetch_assoc()) {
      return $this->registerAddress(intval($record['address_id']), $record);
    }
    return $this->registerAddress($id);
  }

  private function loadCategory(int $id) : ?Category {
    $query = new QueryBuilder(EQueryType::qtSELECT, 'categories', DB_ANY);
    $query->where('categories', 'category_id', '=', $id);
    $result = $this->select($query);
    if ($record = $result->fetch_assoc()) {
      return $this->registerCategory(intval($record['category_id']), $record);
    }
    return $this->registerCategory($id);
  }

  private function loadCountry(int $id) : ?Country {
    $query = new QueryBuilder(EQueryType::qtSELECT, 'countries', DB_ANY);
    $query->where('countries', 'country_id', '=', $id);
    $result = $this->select($query);
    if ($record = $result->fetch_assoc()) {
      return $this->registerCountry(intval($record['country_id']), $record);
    }
    return $this->registerCountry($id);
  }

  private function loadDocument(int $id) : ?Document {
    $query = new QueryBuilder(EQueryType::qtSELECT, 'documents', DB_ANY);
    $query->where('documents', 'document_id', '=', $id);
    $result = $this->select($query);
    if ($record = $result->fetch_assoc()) {
      return $this->registerDocument(intval($record['document_id']), $record);
    }
    return $this->registerDocument($id);
  }

  private function loadExtension(int $id) : ?Extension {
    $query = new QueryBuilder(EQueryType::qtSELECT, 'file_extensions', DB_ANY);
    $query->where('file_extensions', 'ext_id', '=', $id);
    $result = $this->select($query);
    if ($record = $result->fetch_assoc()) {
      return $this->registerExtension(intval($record['ext_id']), $record);
    }
    return $this->registerExtension($id);
  }

  private function loadFile(int $id) : ?File {
    $query = new QueryBuilder(EQueryType::qtSELECT, 'files', DB_ANY);
    $query->where('files', 'file_id', '=', $id);
    $result = $this->select($query);
    if ($record = $result->fetch_assoc()) {
      return $this->registerFile(intval($record['file_id']), $record);
    }
    return $this->registerFile($id);
  }

  private function loadFolder(int $id) : ?Folder {
    $query = new QueryBuilder(EQueryType::qtSELECT, 'folders', DB_ANY);
    $query->where('folders', 'folder_id', '=', $id);
    $result = $this->select($query);
    if ($record = $result->fetch_assoc()) {
      return $this->registerFolder(intval($record['folder_id']), $record);
    }
    return $this->registerFolder($id);
  }

  private function loadType(int $id) : ?Type {
    $query = new QueryBuilder(EQueryType::qtSELECT, 'types', DB_ANY);
    $query->where('types', 'type_id', '=', $id);
    $result = $this->select($query);
    if ($record = $result->fetch_assoc()) {
      return $this->registerType(intval($record['type_id']), $record);
    }
    return $this->registerType($id);
  }

  private function loadUser(int $id) : ?User {
    $query = new QueryBuilder(EQueryType::qtSELECT, 'users', DB_ANY);
    $query->where('users', 'user_id', '=', $id);
    $result = $this->select($query);
    if ($record = $result->fetch_assoc()) {
      return $this->registerUser(intval($record['user_id']), $record);
    }
    return $this->registerUser($id);
  }

  private function loadUsername(string $name) : ?User {
    $query = new QueryBuilder(EQueryType::qtSELECT, 'users', DB_ANY);
    if (strpos($name, '@') > 0)
      $query->where('users', 'user_email', 'LIKE', $name);
    else
      $query->where('users', 'user_name', 'LIKE', $name);
    $result = $this->select($query);
    if ($record = $result->fetch_assoc()) {
      return $this->registerUser(intval($record['user_id']), $record);
    }
    return $this->registerUser($id);
  }

  private function login() : bool {
    if (ISWEB)
      return $this->loginWithCookies();
    else
      return $this->loginCli();
  }

  private function loginCli() : bool {
    $this->currentUser = $this->loadUser(1);
    return !is_null($this->currentUser);
  }

  private function loginWithCookies() : bool {
    if (count($_COOKIE) == 0)
      return false;
    if (!array_key_exists($this->config->Cookies->User->getString(), $_COOKIE) ||
        !array_key_exists($this->config->Cookies->Session->getString(), $_COOKIE) ||
        !array_key_exists($this->config->Cookies->Password->getString(), $_COOKIE)) {
      return false;
    }
    $user = $this->loadUsername($_COOKIE[$this->config->Cookies->User->getString()]);
    if (is_null($user) || !$user->verifySession($_COOKIE[$this->config->Cookies->Session->getString()], $_COOKIE[$this->config->Cookies->Password->getString()])) {
      $this->removeCookies();
      return false;
    }
    $this->currentUser = $user;
    return true;
  }

  public function loginWithPassword(string $username, string $password, bool $keepSession, bool $agreedStatement, Array &$response = null) : bool {
    if ($password == '' || $username == '' || !$agreedStatement) {
      $response = $this->config->getResponseArray(30);
      return false;
    }
    $user = $this->loadUsername($username);
    if (is_null($user) || !$user->verify($password)) {
      $response = $this->config->getResponseArray(30);
      return false;
    }
    $this->currentUser =& $user;
    $this->currentUser->createNewSession($keepSession);
    $response = $this->config->getResponseArray(31);
    return true;
  }

  public function logout() : void {
    if ($this->isAuthenticated()) {
      $this->isAuthenticated = false;
      $this->currentUser->getSession()->destroy();
      $this->currentUser = null;
    }
    $this->removeCookies();
  }

  public function on(string $method, array $params) : void {
    $this->dispatcher->on($method, $params);
  }

  public function post(array $params) : void {
    $this->dispatcher->post($params);
  }

  public function put(array $params) : void {
    $this->dispatcher->put($params);
  }

  private function registerAddress(?int $id, array $record=null) : ?Address {
    if (is_null($id) && is_array($record))
      $id = intval($record['address_id']);
    if (array_key_exists($id, $this->addresses))
      return $this->addresses[$id];
    if (is_null($record)) {
      $this->addresses[$id] = null;
      return null;
    }
    $this->addresses[$id] = new Address($record);
    return $this->addresses[$id];
  }

  private function registerCategory(?int $id, array $record=null) : ?Category {
    if (is_null($id) && is_array($record))
      $id = intval($record['category_id']);
    if (array_key_exists($id, $this->categories))
      return $this->categories[$id];
    if (is_null($record)) {
      $this->categories[$id] = null;
      return null;
    }
    $this->categories[$id] = new Category($record);
    return $this->categories[$id];
  }

  private function registerCountry(?int $id, array $record=null) : ?Country {
    if (is_null($id) && is_array($record))
      $id = intval($record['country_id']);
    if (array_key_exists($id, $this->countries))
      return $this->countries[$id];
    if (is_null($record)) {
      $this->countries[$id] = null;
      return null;
    }
    $this->countries[$id] = new Country($record);
    return $this->countries[$id];
  }

  private function registerDocument(?int $id, array $record=null) : ?Document {
    if (is_null($id) && is_array($record))
      $id = intval($record['document_id']);
    if (array_key_exists($id, $this->documents))
      return $this->documents[$id];
    if (is_null($record)) {
      $this->documents[$id] = null;
      return null;
    }
    $this->documents[$id] = new Document($record);
    return $this->documents[$id];
  }

  private function registerExtension(?int $id, array $record=null) : ?Extension {
    if (is_null($id) && is_array($record))
      $id = intval($record['ext_id']);
    if (array_key_exists($id, $this->extensions))
      return $this->extensions[$id];
    if (is_null($record)) {
      $this->extensions[$id] = null;
      return null;
    }
    $this->extensions[$id] = new Extension($record);
    $this->extensionsByName[$this->extensions[$id]->getExt()] =& $this->extensions[$id];
    return $this->extensions[$id];
  }

  private function registerFile(?int $id, array $record=null) : ?File {
    if (is_null($id) && is_array($record))
      $id = intval($record['file_id']);
    if (array_key_exists($id, $this->files))
      return $this->files[$id];
    if (is_null($record)) {
      $this->files[$id] = null;
      return null;
    }
    $this->files[$id] = new File($record);
    $this->filesByName[$this->files[$id]->getName()] =& $this->files[$id];
    if (array_key_exists('category_name', $record))
      $this->registerCategory(null, $record);
    if (array_key_exists('document_name', $record))
      $this->registerDocument(null, $record);
    if (array_key_exists('ext_key', $record))
      $this->registerExtension(null, $record);
    if (array_key_exists('folder_name', $record))
      $this->registerFolder(null, $record);
    if (array_key_exists('type_name', $record))
      $this->registerType(null, $record);
    return $this->files[$id];
  }

  private function registerFolder(?int $id, array $record=null) : ?Folder {
    if (is_null($id) && is_array($record))
      $id = intval($record['folder_id']);
    if (array_key_exists($id, $this->folders))
      return $this->folders[$id];
    if (is_null($record)) {
      $this->folders[$id] = null;
      return null;
    }
    $this->folders[$id] = new Folder($record);
    $this->foldersByName[$this->folders[$id]->getName()] =& $this->folders[$id];
    return $this->folders[$id];
  }

  private function registerType(?int $id, array $record=null) : ?Type {
    if (is_null($id) && is_array($record))
      $id = intval($record['type_id']);
    if (array_key_exists($id, $this->types))
      return $this->types[$id];
    if (is_null($record)) {
      $this->types[$id] = null;
      return null;
    }
    $this->types[$id] = new Type($record);
    return $this->types[$id];
  }

  private function registerUser(?int $id, array $record=null) : ?User {
    if (is_null($id) && is_array($record))
      $id = intval($record['user_id']);
    if (array_key_exists($id, $this->users))
      return $this->users[$id];
    if (is_null($record)) {
      $this->users[$id] = null;
      return null;
    }
    $this->users[$id] = new User($this, $record);
    return $this->users[$id];
  }

  private function removeCookies() : void {
    $keys = array_keys($_COOKIE);
    for($i=0; $i<count($keys); $i++) {
      setcookie($keys[$i], null, -1);
      unset($_COOKIE[$keys[$i]]);
    }
  }

  public function select(QueryBuilder &$qbuilder) : ?\mysqli_result {
    $query = $qbuilder->buildQuery();
    $result = $this->database->query($query);
    if (!is_a($result, 'mysqli_result'))
      return null;
    return $result;
  }

  public function selectCountSimple(string $table, string $filterColumn=null, string $filterValue=null) : int {
    $query = new QueryBuilder(EQueryType::qtSELECT, $table);
    $query->select([['*', EAggregationType::atCOUNT, 'count']]);
    if (!is_null($filterColumn))
      $query->where($table, $filterColumn, '=', $filterValue);
    return $this->select($query)->fetch_assoc()['count'];
  }

  private function setCookie(string $name, string $value, int $expiration) : bool {
    return setcookie($name, $value, $expiration, '/');
  }

  public function setSessionCookies(string $userCookie, string $tokenCookie, string $passwordCookie, bool $longDuration) : bool {
    $expires = 0;
    if ($longDuration) {
      global $NOW;
      $expdatetime = $NOW->add($this->config->Cookies->ExpirationLong->getTimespan());
      $expires = $expdatetime->getTimestamp();
    }
    return ($this->setCookie($this->config->Cookies->User->getString(), $userCookie, $expires)
      && $this->setCookie($this->config->Cookies->Session->getString(), $tokenCookie, $expires)
      && $this->setCookie($this->config->Cookies->Password->getString(), $passwordCookie, $expires));
  }

  public function tearDown() : void {
    foreach ($this->changedObjects as $key => $object) {

      switch(get_class($object)) {

        case 'Surcouf\PhpArchive\User':
          if (count($object->getDbChanges()) == 0)
            break;
          $query = new QueryBuilder(EQueryType::qtUPDATE, 'users');
          $query->update($object->getDbChanges());
          $query->where('users', 'user_id', '=', $object->getId());
          $this->update($query);
          break;

      }


    }

  }

  public function update(QueryBuilder &$qbuilder) : bool {
    $query = $qbuilder->buildQuery();
    $result = $this->database->query($query);
    return $result;
  }

  public function updateDbObject(IDbObject &$object) : void {
    $key = get_class($object).$object->getId();
    if (!array_key_exists($key, $this->changedObjects))
      $this->changedObjects[$key] = $object;
  }

}
