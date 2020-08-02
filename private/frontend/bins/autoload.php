<?php

if (!defined('CORE2'))
  exit;

// Frontend binaries
require_once __DIR__.'/document/ajax-document.php';
require_once __DIR__.'/document/document.php';
require_once __DIR__.'/file/ajax-file.php';
require_once __DIR__.'/file/file.php';
require_once __DIR__.'/files/categories.php';
require_once __DIR__.'/files/files.php';
require_once __DIR__.'/files/types.php';
require_once __DIR__.'/files/workflow/inbox.php';
require_once __DIR__.'/mount/ajax-mount.php';
require_once __DIR__.'/home.php';
require_once __DIR__.'/user/user.php';
require_once __DIR__.'/user/dropzone.php';
require_once __DIR__.'/user/search.php';

require_once __DIR__.'/admin/autoload.php';
