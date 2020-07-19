<?php

$Controller->get(array(
  'pattern' => '/ajax/document/classify/(?<id>\d+)',
  'out' => 'json',
  'fn' => 'ajax_document_classify'
));

$Controller->get(array(
  'pattern' => '/ajax/document/classify/(?<id>\d+)/as/(?<param>(type|category))/(?<pid>\d+)',
  'out' => 'json',
  'fn' => 'ajax_document_classifyas'
));

$Controller->post(array(
  'pattern' => '/ajax/document/classify/(?<id>\d+)/update-metadata',
  'out' => 'json',
  'fn' => 'ajax_document_updatemetadata'
));

function ajax_document_classify() {
  global $Files, $Router;

  $id = intval($Router->getMatches()['id']);
  load_file($id, false);
  $file =& $Files[$id];

  return $file->classifyDocument();

} // ajax_document_classify()

function ajax_document_classifyas() {
  global $Files, $Router;

  $id = intval($Router->getMatches()['id']);
  load_file($id, false);
  $file =& $Files[$id];

  $group = $Router->getMatches()['param'];
  $targetid = $Router->getMatches()['pid'];

  return $file->classifyDocumentAs($group, $targetid);

} // ajax_document_classifyas()

function ajax_document_updatemetadata() {
  global $Files, $Router;

  $id = intval($Router->getMatches()['id']);
  load_file($id, false);
  $file =& $Files[$id];

  if ($file->isDir())
    return getConfiguredResponseArray(602);

  $response = null;
  $file->getDocument(true)->updateMeta($Router->getPayload(), $response);

  return $response;

} // ajax_document_updatemetadata()
