<?php

class UserListCommand extends Ahc\Cli\Input\Command
{
  public function __construct()
  {
    parent::__construct('user', 'Lists all registered users.');
  }

  public function execute()
  {
    global $writer, $UsersByName;
    $table = array();
    foreach ($UsersByName as $key => $obj) {
      $table[] = array(
        'Id' => $obj->getId(),
        'Username' => $obj->getKey(),
        'Name' => $obj->getName(),
        'E-Mail' => $obj->getMail(),
        'Admin' => bool2str($obj->isAdmin()),
        'Guest' => bool2str($obj->isGuest()),
        'Group' => $obj->getGroup()->getId().': '.$obj->getGroup()->getName(),
      );
    }
    $writer->table($table);
    return 1;
  }
}

$app->add(new UserListCommand, 'u');
