<?php

use Surcouf\PhpArchive\Database\EQueryType;
use Surcouf\PhpArchive\Database\QueryBuilder;

$Controller->get(array(
  'pattern' => '/workflow/inbox',
  'fn' => 'ui_wf_inbox_todo'
));

function ui_wf_inbox_todo() {
  global $Controller, $OUT;

  $OUT['Page']['Breadcrumbs'][] = array(
    'text' => 'Dateien',
    'url' => $Controller->getLink('private:files'),
  );

  $OUT['Page']['Breadcrumbs'][] = array(
    'text' => 'Aufgaben',
    'url' => $Controller->getLink('workflow:inbox'),
  );

  $OUT['Page']['Current'] = 'private:files';
  $OUT['Page']['CurrentSub'] = 'workflow:inbox';
  $OUT['Page']['Heading1'] = '<<Todo>>';
} // ui_wf_inbox_todo()
