<?php

use Surcouf\PhpArchive\Database\EQueryType;
use Surcouf\PhpArchive\Database\QueryBuilder;

$Controller->get(array(
  'pattern' => '/types',
  'fn' => 'ui_types_todo'
));

function ui_types_todo() {
  global $Controller, $OUT;

  $OUT['Page']['Breadcrumbs'][] = array(
    'text' => 'Dateien',
    'url' => $Controller->getLink('private:files'),
  );

  $OUT['Page']['Breadcrumbs'][] = array(
    'text' => 'Dokumentarten',
    'url' => $Controller->getLink('private:types'),
  );

  $OUT['Page']['Current'] = 'private:files';
  $OUT['Page']['CurrentSub'] = 'private:types';
  $OUT['Page']['Heading1'] = '<<Todo>>';
} // ui_types_todo()
