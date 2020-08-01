<?php


if (!defined('CORE2'))
  exit;

function bool2str($b) {
  return $b === true ? 'true' : 'false';
}

function dateInterval2IsoFormat(DateInterval $interval) {
  // https://stackoverflow.com/questions/33787039/format-dateinterval-as-iso8601
  list($date,$time) = explode("T",$interval->format("P%yY%mM%dDT%hH%iM%sS"));
  // now, we need to remove anything that is a zero, but make sure to not remove
  // something like 10D or 20D
  $res =
      str_replace([ 'M0D', 'Y0M', 'P0Y' ], [ 'M', 'Y', 'P' ], $date) .
      rtrim(str_replace([ 'M0S', 'H0M', 'T0H'], [ 'M', 'H', 'T' ], "T$time"),"T");
  if ($res == 'P') // edge case - if we remove everything, DateInterval will hate us later
      return 'PT0S';
  return $res;
}

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

function getBool($v) {
  return ($v === 1 || $v === '1' || $v === 'true' || $v === true);
}

function getOldDate(DateInterval $i) {
  $nowdt = new DateTime();
  $dt = $nowdt->sub($i);
  return $dt;
}

function getOldTimestamp(DateInterval $i) {
  $nowdt = new DateTime();
  $dt = $nowdt->sub($i);
  return $dt->getTimestamp();
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

function getShieldTimespan($stamp, $span) {
  $ispan = $span->s + $span->i * 60 + $span->h * 3600 + $span->d * 86400;
  if ($ispan <= 3600) {
    $cjtime = $span->i.' min ago';
    $cjcol = 'success';
  } else if ($ispan <= 14400) {
    $cjtime = $span->h.' hrs ago';
    $cjcol = 'important';
  } else {
    $cjtime = $stamp->format('d.m.Y H:i');
    $cjcol = 'critical';
  }
  return $cjtime.'-'.$cjcol;
}

function getSnPl($value, $sn, $pl, $valueAsPrefix=false, $valueAsAppendix=false, $separator=' ') {
  return ($valueAsPrefix ? $value.$separator : '').($value == 1 ? $sn : $pl).($valueAsAppendix ? $separator.$value : '');
}

function getSymbolByQuantity($bytes) {
  $symbols = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
  $exp = $bytes ? floor(log($bytes) / log(1024)) : 0;
  return sprintf(($symbols[$exp] == 'B' ? '%.0f ' : '%.2f ').$symbols[$exp], ($bytes/pow(1024, floor($exp))));
}

function getTimespanFromNow($dt) {
  $ispan = abs((new DateTime())->getTimestamp() - $dt->getTimestamp());
  $result = '';
  if ($ispan <= 3600) {
    $i = ceil($ispan / 60);
    if ($i > 10)
      $i = round($i / 10) * 10;
    $result = $i.' minute'.($i == 1 ? '' : 's').' ago';
  } else if ($ispan <= 86400) {
    $h = round($ispan / 3600);
    $result = $h.' hour'.($h == 1 ? '' : 's').' ago';
  } else if ($ispan <= 2592000) {
    $d = round($ispan / 86400);
    $result = $d.' day'.($d == 1 ? '' : 's').' ago';
  } else if ($ispan <= 15552000) {
    $m = round($ispan / 2592000);
    $result = $m.' month'.($m == 1 ? '' : 's').' ago';
  }
  else
    $result = 'I don\'t know...';
  return $result;
}

function getTimespanFromStamps($stamp1, $stamp2) {
  $ispan = abs($stamp2 - $stamp1);
  if ($ispan <= 3600) {
    $i = ceil($ispan / 60);
    if ($i > 10)
      $i = round($i / 10) * 10;
    $result = $i.' minute'.($i == 1 ? '' : 's').' ago';
  } else if ($ispan <= 86400) {
    $h = round($ispan / 3600);
    $result = $h.' hour'.($h == 1 ? '' : 's').' ago';
  } else if ($ispan <= 2592000) {
    $d = round($ispan / 86400);
    $result = $d.' day'.($d == 1 ? '' : 's').' ago';
  } else if ($ispan <= 15552000) {
    $m = round($ispan / 2592000);
    $result = $m.' month'.($m == 1 ? '' : 's').' ago';
  }
  else
    $result = 'I don\'t know...';
  return $result;
}

function getTodayTimestamp() {
  $nowdt = new DateTime();
  $nowdt->setTime(0, 0, 0);
  return $nowdt->getTimestamp();
}

function hasFlag(int $needle, int $haystack) : bool {
  return ($haystack & $needle);
}

function hashValue($value) {
  if(is_array($value))
    return hashValue(implode('*', $value));
  return strtoupper(hash('crc32b', $value));
}

function validateFileHash($path, $hash) {
  global $Config;
  if (!file_exists($path))
    return false;
  $cs = hash_file($Config->Checksum->Algorithm->getString(), $path);
  if ($hash != $cs)
    return $cs;
  return true;
}
