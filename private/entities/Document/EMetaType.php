<?php

namespace Surcouf\PhpArchive\Document;

if (!defined('CORE2'))
  exit;

class EMetaType {

  const None = 0;

  const mtSTRING = 1;

  const mtDATE = 2;

  const mtINT = 4;

  const mtFLOAT = 8;

  const mtBOOL = 16;

}
