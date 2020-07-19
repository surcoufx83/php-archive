<?php

$Controller->get(array(
  'pattern' => '/ajax/file/generateJpg/(?<id>\d+)',
  'out' => 'json',
  'fn' => 'ajax_file_gen_jpg'
));

$Controller->get(array(
  'pattern' => '/ajax/file/generateTif/(?<id>\d+)\+(?<index>\d+)',
  'out' => 'json',
  'fn' => 'ajax_file_gen_tif'
));

$Controller->get(array(
  'pattern' => '/ajax/file/generateOcr/(?<id>\d+)\+(?<index>\d+)',
  'out' => 'json',
  'fn' => 'ajax_file_gen_tsv'
));

$Controller->get(array(
  'pattern' => '/ajax/file/purgeImages/(?<id>\d+)',
  'out' => 'json',
  'fn' => 'ajax_file_purge_images'
));

$Controller->get(array(
  'pattern' => '/ajax/file/validate/(?<id>\d+)',
  'out' => 'json',
  'fn' => 'ajax_file_validate'
));

$Controller->get(array(
  'pattern' => '/ajax/file/validate/shortresponse/(?<id>\d+)',
  'out' => 'json',
  'fn' => 'ajax_file_validate_short'
));

function ajax_file_classify() {
  global $Files, $Router;

  $id = intval($Router->getMatches()['id']);
  load_file($id, false);
  $file =& $Files[$id];

  if ($file->isDir())
    return $Router->exitJson(getConfiguredResponseArray(602));

  $result = $file->classifyDocument();

  $Router->exitJson($result);
  exit;

} // ajax_file_classify()

function ajax_file_classifyas() {
  global $Files, $Router;

  $id = intval($Router->getMatches()['id']);
  load_file($id, false);
  $file =& $Files[$id];

  if ($file->isDir())
    return $Router->exitJson(getConfiguredResponseArray(602));

  $group = $Router->getMatches()['param'];
  $targetid = $Router->getMatches()['pid'];

  return $file->classifyDocumentAs($group, $targetid);

} // ajax_file_classifyas()

function ajax_file_gen_jpg() {
  global $OUT, $Files, $Router;

  $id = intval($Router->getMatches()['id']);
  load_file($id, false);
  $file =& $Files[$id];

  $result = $file->generateImage(Surcouf\PhpArchive\File\Ocr\EFileConvertFormat::JPG);
  $file->loadOcrImageCache();
  if ($result['Result']['Success']) {
    $result['File'] = array(
      'Id' => $file->getId(),
      'ImageCount' => $file->getOcrImageCache()[Surcouf\PhpArchive\File\Ocr\EFileConvertFormat::JPG]['filecount'],
      'ImageCountRel' => number_format($file->getOcrImageCache_CompleteState('jpg'), 0, ',', '.'),
      'ImageCountTarget' => $file->getPageCount(),
    );
    $result['Replacers'] = array(
      'bytecount' => $file->getOcrImageCache_ByteCount('jpg'),
      'filecount' => $file->getOcrImageCache_FileCount('jpg'),
      'relative' => $result['File']['ImageCountRel'],
      'files-ocr-'.$file->getId().'-completed' => $file->getOcrImageCache_CompleteState(),
    );
  }
  $Router->exitJson($result);

} // ajax_file_gen_jpg()

function ajax_file_gen_tif() {
  global $Files, $Router;

  $id = intval($Router->getMatches()['id']);
  load_file($id, false);
  $file =& $Files[$id];

  $page = intval($Router->getMatches()['index']);

  $result = $file->generateImage(Surcouf\PhpArchive\File\Ocr\EFileConvertFormat::TIF, $page);
  $file->loadOcrImageCache();
  if ($result['Result']['Success']) {
    $result['File'] = array(
      'Id' => $file->getId(),
      'ImageCount' => $file->getOcrImageCache()[Surcouf\PhpArchive\File\Ocr\EFileConvertFormat::TIF]['filecount'],
      'ImageCountRel' => number_format($file->getOcrImageCache_CompleteState('tif'), 0, ',', '.'),
      'ImageCountTarget' => $file->getPageCount(),
      'PageIndex' => $page,
    );
    $result['Replacers'] = array(
      'bytecount' => $file->getOcrImageCache_ByteCount('tif'),
      'filecount' => $file->getOcrImageCache_FileCount('tif'),
      'relative' => $result['File']['ImageCountRel'],
      'files-ocr-'.$file->getId().'-completed' => $file->getOcrImageCache_CompleteState(),
    );
  }
  $Router->exitJson($result);

} // ajax_file_gen_tif()

function ajax_file_gen_tsv() {
  global $Files, $Router;

  $id = intval($Router->getMatches()['id']);
  load_file($id, false);
  $file =& $Files[$id];

  $page = intval($Router->getMatches()['index']);

  $result = $file->doOcr($page);
  $file->loadOcrImageCache();
  $file->loadOcrStatsCache();

  if ($result['Result']['Success']) {
    $result['File'] = array(
      'Id' => $file->getId(),
      'ImageCount' => $file->getOcrImageCache()[Surcouf\PhpArchive\File\Ocr\EFileConvertFormat::TSV]['filecount'],
      'ImageCountRel' => number_format($file->getOcrImageCache_CompleteState('ocr'), 0, ',', '.'),
      'ImageCountTarget' => $file->getPageCount(),
      'PageIndex' => $page,
    );
    $result['Replacers'] = array(
      'bytecount' => $file->getOcrImageCache_ByteCount('ocr'),
      'filecount' => $file->getOcrImageCache_FileCount('ocr'),
      'relative' => $result['File']['ImageCountRel'],
      'page-'.$page.'-confidence' => $file->getConfidenceLevel($page),
      'page-'.$page.'-wordcount' => $file->getWordCount($page),
      'files-ocr-'.$file->getId().'-completed' => $file->getOcrImageCache_CompleteState(),
    );
  } else {
    $result['Result']['Error']['AdditionalMessage'] = $file->getLastError();
  }
  $Router->exitJson($result);

} // ajax_file_gen_tsv()

function ajax_file_purge_images() {
  global $Files, $Router;

  $id = intval($Router->getMatches()['id']);
  load_file($id, false);
  $file =& $Files[$id];

  $result = $file->purgeImages();
  $Router->exitJson($result);

} // ajax_file_purge_images()

function ajax_file_validate() {
  global $Files, $Router;

  $id = intval($Router->getMatches()['id']);
  load_file($id, false);
  $file =& $Files[$id];

  $result = $file->validateObject();
  $result['Result']['CanOcr'] = $file->canDoOcr();

  $Router->exitJson($result);
  exit;

} // ajax_file_validate()

function ajax_file_validate_short() {
  global $Files, $Mounts, $Router;

  $id = intval($Router->getMatches()['id']);
  load_file($id, false);
  $file =& $Files[$id];

  $result = $file->validateObject(true);
  $result['Result']['CanOcr'] = $file->canDoOcr();

  $Router->exitJson($result);
  exit;

} // ajax_file_validate_short()
