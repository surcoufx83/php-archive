<?php

namespace Surcouf\PhpArchive\File\Ocr;

if (!defined('CORE2'))
  exit;

class EFileConvertFormat {
  const JPG = 0;
  const TIF = 1;
  const PDF = 2;
  const OCR = 3;
  const HOCR = 4;
  const TSV = 5;
  const TXT = 6;
}
