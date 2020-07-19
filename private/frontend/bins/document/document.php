<?php

$Controller->get(array(
  'pattern' => '/documents',
  'fn' => 'ui_documents'
));

$Controller->get(array(
  'pattern' => '/document/classify/(?<id>\d+)',
  'fn' => 'ui_document_classify'
));

function ui_documents() {
  global $db, $MainCategories, $OUT, $twig;

  load_documentCategories();
  load_documentTypes();

  $OUT['Categories'] = $MainCategories;
  $OUT['Page']['Heading1'] = 'Dokumente';
  $OUT['Content'] = $twig->render('views/documents/dashboard.html.twig', $OUT);
} // ui_documents()

function ui_document_classify() {
  global $db, $OUT, $Config, $MainCategories, $DocumentTypesByName, $Files, $Router, $twig;

  load_documentCategories();
  load_documentTypes();

  $id = intval($Router->getMatches()['id']);
  load_file($id);
  $file =& $Files[$id];
  $file->loadDocument();

  if ($file->isDir())
    $Router->forward($file->getLink());

  $OUT['Page']['Breadcrumbs'][] = array('Zurück', $file->getLink(), $Config->Icons->Back);
  $OUT['Page']['Breadcrumbs'][] = array('Ausführen', '#', $Config->Icons->Start, 'file-classify-start');
  $OUT['Page']['Breadcrumbs'][] = array('Vorschlag übernehmen', '#', $Config->Icons->Save, 'file-classify-save-rejection', '', 'd-none');

  for ($i=1; $i<10; $i++) {
    $nid = $id + $i;
    load_file($nid);
    $test =& $Files[$nid];
    if ($test->isFile()) {
      if (!$test->needsValidation()) {
        $OUT['Page']['Breadcrumbs'][] = array('Next: '.$test->getName(), $test->getClassifyLink(), $Config->Icons->Classify);
        break;
      }
    }
  }

  $res = $db->query('SELECT `filesystem`.`fs_id`
      FROM `filesystem`
      LEFT JOIN `documents` ON `documents`.`fs_id` = `filesystem`.`fs_id`
      WHERE `fs_isdir`=0 AND `fs_pages`=`fs_ocr_pages` AND `fs_pages`>0 AND `documents`.`fs_id` IS NULL
      ORDER BY RAND() LIMIT 1');
  if ($res->num_rows != 0) {
    $res = $res->fetch_assoc();
    load_file($res['fs_id']);
    $OUT['Page']['Breadcrumbs'][] = array('Random: '.$Files[intval($res['fs_id'])]->getName(), $Files[intval($res['fs_id'])]->getClassifyLink(), $Config->Icons->Classify);
  }

  $OUT['Page']['File'] = $file;
  $OUT['Page']['Heading1'] = $file->getName();
  $OUT['DocCategories'] = $MainCategories;
  $OUT['DocTypes'] = $DocumentTypesByName;
  $OUT['Page']['Mode'] = 'classify';
  $OUT['Page']['Scripts']['Custom'][] = 'file-classify';
  $OUT['Page']['ShowFile'] = $file->showInline();
  $OUT['Content'] = $twig->render('views/fileview/file-view.html.twig', $OUT);
} // ui_document_classify()
