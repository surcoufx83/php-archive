<?php

namespace Surcouf\PhpArchive\Mount;

if (!defined('CORE2'))
  exit;

class EMountType {
  const DataDir             =   1;
  const ScanInput           =   2;
  const EMailInput          =   3;
  const TemplateDir         =   4;
  const TrashDir            =   5;
}
