<?php

$Controller->get(array(
  'pattern' => '/ajax/mount/scan/(?<id>\d+)',
  'fn' => 'ajax_mount_scan'
));

function ajax_mount_scan() {
  global $OUT, $Config, $Files, $Mounts, $Router, $twig;

  $id = intval($Router->getMatches()['id']);
  if (!array_key_exists($id, $Mounts))
    $Router->exitJson(getConfiguredResponseArray(80));
  $mount =& $Mounts[$id];

  $result = array();
  $mount->rescan($result);
  $Router->exitJson($result);

} // ajax_mount_scan()
