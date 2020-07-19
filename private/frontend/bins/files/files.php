<?php

use Surcouf\PhpArchive\Database\EQueryType;
use Surcouf\PhpArchive\Database\QueryBuilder;

$Controller->get(array(
  'pattern' => '/files',
  'fn' => 'ui_files'
));

function ui_files() {
  global $Controller, $OUT, $twig;

  $OUT['Page']['Breadcrumbs'][] = array(
    'text' => 'Dateien',
    'url' => $Controller->getLink('private:files'),
  );

  $query = new QueryBuilder(EQueryType::qtSELECT, 'folders', 'folder_id');
  $query
        ->join('mounts', ['mounts', 'mount_id', '=', 'folders', 'mount_id'])
        ->where('folders', 'parent_id', 'IS NULL')
        ->andWhere('mounts', 'tenant_id', '=', $Controller->User()->getCurrentTenantId());
  $rootfolder = $Controller->select($query)->fetch_assoc()['folder_id'];

  $folders = $Controller->User()->loadFolders($rootfolder);
  $files = $Controller->User()->loadFiles($rootfolder);

  $OUT['Folders'] = $folders;
  $OUT['Files'] = $files;

  $OUT['Page']['Current'] = 'private:files';
  $OUT['Page']['CurrentSub'] = 'private:files';
  $OUT['Page']['Heading1'] = 'Ihre Dateien';
  $OUT['Content'] = $twig->render('views/files/files.html.twig', $OUT);
} // ui_files()
