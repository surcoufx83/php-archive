<?php


if (!defined('CORE2'))
  exit;

function formatFloat(float $value, int $precission = -1) {
  global $Config;
  $decplcs = $Config->Defaults->NumberFormat->Decimals->getInt();
  $decsep = $Config->Defaults->NumberFormat->DecimalsSeparator->getString();
  $thsdsep = $Config->Defaults->NumberFormat->ThousandsSeparator->getString();
  return number_format($value, $precission > -1 ? $precission : $decplcs, $decsep, $thsdsep);
}

function formatInt(int $value) {
  global $Config;
  $thsdsep = $Config->Defaults->NumberFormat->ThousandsSeparator->getString();
  return number_format($value, 0, '', $thsdsep);
}

function generateRandomToken($length = 32){
    if(!isset($length) || intval($length) <= 8 ){
      $length = 32;
    }
    if (function_exists('random_bytes')) {
        return bin2hex(random_bytes($length));
    }
    if (function_exists('mcrypt_create_iv')) {
        return bin2hex(mcrypt_create_iv($length, MCRYPT_DEV_URANDOM));
    }
    if (function_exists('openssl_random_pseudo_bytes')) {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }
    return null;
}

$_rdblBytes = array('B', 'KB', 'MB', 'GB', 'TB');
function getReadableSize($bytes, $prec = -1) {
  global $_rdblBytes;
  if ($prec == -1) {
    if ($bytes >= 1048576)
      $prec = 2;
    else if ($bytes >= 1024)
      $prec = 1;
    else
      $prec = 0;
  }
  $i = 0;
  while ($bytes >= 1024) {
    $i++;
    $bytes /= 1024;
  }
  if ($prec == 0)
    return formatInt($bytes).' '.$_rdblBytes[$i];
  else
    return formatFloat($bytes, $prec).' '.$_rdblBytes[$i];
}

function getSnPl($value, $sn, $pl, $valueAsPrefix=false, $valueAsAppendix=false, $separator=' ') {
  return ($valueAsPrefix ? $value.$separator : '').($value == 1 ? $sn : $pl).($valueAsAppendix ? $separator.$value : '');
}

function getSymbolByQuantity($bytes) {
  $symbols = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
  $exp = $bytes ? floor(log($bytes) / log(1024)) : 0;
  return sprintf(($symbols[$exp] == 'B' ? '%.0f ' : '%.2f ').$symbols[$exp], ($bytes/pow(1024, floor($exp))));
}

function hasFlag(int $needle, int $haystack) : bool {
  return ($haystack & $needle);
}
