<?php

$Controller->get(array(
  'pattern' => '/file/(?<id>\d+)',
  'fn' => 'ui_file'
));

$Controller->get(array(
  'pattern' => '/file/edit/(?<id>\d+)',
  'fn' => 'ui_file_edit'
));

$Controller->get(array(
  'pattern' => '/file/ocr/(?<id>\d+)',
  'fn' => 'ui_file_ocr'
));

$Controller->get(array(
  'pattern' => '/file/send/(?<id>\d+)',
  'fn' => 'ui_file_send'
));

$Controller->post(array(
  'pattern' => '/file/submit-mail/(?<id>\d+)',
  'fn' => 'ajax_submit_file_send'
));

$Controller->get(array(
  'pattern' => '/file/validation/(?<id>\d+)',
  'fn' => 'ui_file_validation'
));

$Controller->get(array(
  'pattern' => '/file/view/(?<id>\d+)',
  'fn' => 'ui_file_view'
));

function ui_file() {
  global $OUT, $Controller, $twig;

  $id = intval($Controller->Dispatcher()->getMatches()['id']);
  $file = $Controller->getFile($id);

  /*if ($file->needsValidation()
    && !$file->isDir())
    $OUT['Page']['Breadcrumbs'][] = array('Überprüfung erforderlich!', $file->getValidationLink(), $Config->Exclamation, false, 'text-danger');
*/
  //$OUT['Page']['Breadcrumbs'][] = array('Ordner '.$file->getMount()->getName(), $file->getMount()->getRoot()->getLink(), $Config->Files);

  //if ($file->getMount()->getRoot()->getId() != $file->getParent()->getId())
  //  $OUT['Page']['Breadcrumbs'][] = array('Ordner '.$file->getParent()->getName(), $file->getParent()->getLink(), $Config->Files);

    //$OUT['Page']['Breadcrumbs'][] = array('Bearbeiten', $file->getEditLink(), $Config->Edit);

  //if ($file->getExtObject()->allowMailsend())
  //  $OUT['Page']['Breadcrumbs'][] = array('Per E-Mail verschicken', $file->getMailLink(), $Config->SendMail);

  //if ($file->canDoOcr() === true && !$file->needsValidation())
  //  $OUT['Page']['Breadcrumbs'][] = array('OCR', $file->getOcrLink(), $Config->Ocr);

  //  $OUT['Page']['Breadcrumbs'][] = array('Klassifizierung', $file->getClassifyLink(), $Config->Classify);

  $OUT['Page']['File'] = $file;
  $OUT['Page']['Heading1'] = $file->getName();
  $OUT['Page']['Mode'] = 'readonly';
  $OUT['Page']['ShowFile'] = true; //$file->showInline();
  $OUT['Content'] = $twig->render('views/fileview/file-view.html.twig', $OUT);
} // ui_file()

function ui_file_edit() {
  global $OUT, $Config, $Files, $Router, $twig;

  load_documentTypes();

  $id = intval($Router->getMatches()['id']);
  load_file($id, true);
  $file =& $Files[$id];
  $file->loadDocument();

  if ($file->needsValidation() && !$file->isDir())
    $OUT['Page']['Breadcrumbs'][] = array('Überprüfung erforderlich!', $file->getValidationLink(), $Config->Exclamation, false, 'text-danger');

  $OUT['Page']['Breadcrumbs'][] = array('Ordner '.$file->getMount()->getName(), $file->getMount()->getRoot()->getLink(), $Config->Files);

  if ($file->getMount()->getRoot()->getId() != $file->getParent()->getId())
    $OUT['Page']['Breadcrumbs'][] = array('Ordner '.$file->getParent()->getName(), $file->getParent()->getLink(), $Config->Files);

  $OUT['Page']['Breadcrumbs'][] = array('Zurück', $file->getLink(), $Config->Back);

  if ($file->getExtObject()->allowMailsend())
    $OUT['Page']['Breadcrumbs'][] = array('Per E-Mail verschicken', $file->getMailLink(), $Config->SendMail);

    $OUT['Page']['Breadcrumbs'][] = array('OCR', $file->getOcrLink(), $Config->Ocr);

  $OUT['Page']['File'] = $file;
  $OUT['Page']['Heading1'] = $file->getName();
  $OUT['Page']['Mode'] = 'edit';
  $OUT['Page']['Scripts']['FormValidator'] = true;
  $OUT['Page']['Scripts']['Custom'][] = 'file-edit-meta';
  $OUT['Page']['ShowFile'] = $file->showInline();
  $OUT['Content'] = $twig->render('views/fileview/file-view.html.twig', $OUT);
} // ui_file_edit()

function ui_file_ocr() {
  global $OUT, $Config, $Files, $Router, $twig;

  $id = intval($Router->getMatches()['id']);
  $filter = 'WHERE `fs_id`='.$id;
  load_files($filter);
  $file =& $Files[$id];
  $file->loadOcrImageCache();
  $file->loadOcrStatsCache();

  if ($file->needsValidation() || !$file->canDoOcr())
    $Router->forward($file->getLink());

  $OUT['Page']['Breadcrumbs'][] = array('Ordner '.$file->getMount()->getName(), $file->getMount()->getRoot()->getLink(), $Config->Files);

  if ($file->getMount()->getRoot()->getId() != $file->getParent()->getId())
    $OUT['Page']['Breadcrumbs'][] = array('Ordner '.$file->getParent()->getName(), $file->getParent()->getLink(), $Config->Files);

    $OUT['Page']['Breadcrumbs'][] = array('Bearbeiten', $file->getEditLink(), $Config->Edit);

  $OUT['Page']['Breadcrumbs'][] = array('Zurück', $file->getLink(), $Config->Back);

  $OUT['Page']['File'] = $file;
  $OUT['Page']['Heading1'] = $file->getName();
  $OUT['Page']['ShowFile'] = $file->showInline();
  $OUT['Page']['Scripts']['Custom'][] = 'file-ocr';
  $OUT['Content'] = $twig->render('views/fileview/file-view-ocr.html.twig', $OUT);
} // ui_file_ocr()

function ui_file_send() {
  global $OUT, $Config, $Files, $Router, $twig;

  $id = intval($Router->getMatches()['id']);
  $filter = 'WHERE `fs_id`='.$id;
  load_files($filter);
  $file =& $Files[$id];

  if (!$file->getExtObject()->allowMailsend())
    $Router->forward($file->getLink());

  $OUT['Page']['Breadcrumbs'][] = array('Ordner '.$file->getMount()->getName(), $file->getMount()->getRoot()->getLink(), $Config->Files);

  if ($file->getMount()->getRoot()->getId() != $file->getParent()->getId())
    $OUT['Page']['Breadcrumbs'][] = array('Ordner '.$file->getParent()->getName(), $file->getParent()->getLink(), $Config->Files);

    $OUT['Page']['Breadcrumbs'][] = array('Bearbeiten', $file->getEditLink(), $Config->Edit);

  $OUT['Page']['Breadcrumbs'][] = array('Zurück', $file->getLink(), $Config->Back);

  $OUT['Page']['File'] = $file;
  $OUT['Page']['Heading1'] = $file->getName();
  $OUT['Page']['Mode'] = 'mail';
  $OUT['Page']['ShowFile'] = $file->showInline();
  $OUT['Page']['Scripts']['FormValidator'] = true;
  $OUT['Page']['Scripts']['Custom'][] = 'file-view-mail';
  $OUT['Content'] = $twig->render('views/fileview/file-view.html.twig', $OUT);
} // ui_file_send()

function ajax_submit_file_send() {
  global $Config, $Files, $Router;

  $id = intval($Router->getMatches()['id']);
  $filter = 'WHERE `fs_id`='.$id;
  load_files($filter);
  $file =& $Files[$id];

  $mail = $Config->MailProvider->Archive->getMailAccount();
  $data = $Router->getPayload();

  if (!$file->getExtObject()->allowMailsend())
    $Router->exitJson(getConfiguredResponseArray(706));

  if ($mail->sendMailWithAttachment($data['mailto'], $data['mailcc'], $data['attfilename'], $data['mailsubj'], $data['mailtext'], $file)) {
    $Router->exitJson(getResponseArray());
  } else {
    $Router->exitJson(getResponseArray(false, $mail->getLastErrorcode(), $mail->getLastErrormessage()));
  }

} // ajax_submit_file_send()

function ui_file_validation() {
  global $OUT, $Config, $Files, $Router, $twig;

  $id = intval($Router->getMatches()['id']);
  $filter = 'WHERE `fs_id`='.$id;
  load_files($filter);
  $file =& $Files[$id];
  $file->loadOcrImageCache();
  $file->loadOcrStatsCache();

  if ($file->isDir())
    $Router->forward($file->getLink());

  $OUT['Page']['Breadcrumbs'][] = array('Zurück', $file->getLink(), $Config->Back);
  $OUT['Page']['Breadcrumbs'][] = array('Ausführen', '#', $Config->Start, 'file-validate-start');

  $OUT['Page']['File'] = $file;
  $OUT['Page']['Heading1'] = $file->getName();
  $OUT['Page']['Mode'] = 'validation';
  $OUT['Page']['ShowFile'] = $file->showInline();
  $OUT['Page']['Scripts']['Custom'][] = 'file-validate';
  $OUT['Content'] = $twig->render('views/fileview/file-view.html.twig', $OUT);
} // ui_file_validation()

function ui_file_view() {
  global $Files, $Router;

  $id = intval($Router->getMatches()['id']);
  $filter = 'WHERE `fs_id`='.$id;
  load_files($filter);
  $file =& $Files[$id];

  if (!$file->showInline()) {
    if (DEBUG === true)
      die('File can\'t be returned.');
    exit;
  }

  $content = file_get_contents($file->getPath());

  header('Content-Type: '.$file->getMimeType());
  header('Content-Length: ' . strlen($content));
  header('Content-Disposition: inline; filename="'.$file->getName().'"');
  header('Cache-Control: private, max-age=3600, must-revalidate');
  header('Last-Modified: '.$file->getChangeTime()->format('D, d M Y H:i:s'));
  header('Pragma: public');
  header('X-Frame-Options: sameorigin');
  ini_set('zlib.output_compression','0');

  die($content);

} // ui_file_view
