<?php

use Surcouf\PhpArchive\Database\EQueryType;
use Surcouf\PhpArchive\Database\QueryBuilder;

$Controller->get(array(
  'pattern' => '/categories',
  'fn' => 'ui_categories_todo'
));

function ui_categories_todo() {
  global $Controller, $OUT;

  $OUT['Page']['Breadcrumbs'][] = array(
    'text' => 'Dateien',
    'url' => $Controller->getLink('private:files'),
  );

  $OUT['Page']['Breadcrumbs'][] = array(
    'text' => 'Kategorien',
    'url' => $Controller->getLink('private:categories'),
  );

  $OUT['Page']['Current'] = 'private:files';
  $OUT['Page']['CurrentSub'] = 'private:categories';
  $OUT['Page']['Heading1'] = '<<Todo>>';
} // ui_categories_todo()
