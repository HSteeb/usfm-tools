<?php

use HSteeb\UsfmTools\Sanitizer;
require 'vendor/autoload.php';

/**
 * Sanitize USFM file
 */
if ($argc < 3) {
  echo <<<EOUSAGE
Usage:
  php sanitize.php infile outfile [config.json]

EOUSAGE;
  exit;
}
$infile  = $argv[1];
if (!file_exists($infile)) {
  echo "File $infile not found.\n";
  exit;
}
$outfile = $argv[2];

$Config  = [];
if ($argc == 4) {
  $jsonfile = $argv[3];
  if (!file_exists($jsonfile)) {
    echo "File $jsonfile not found.\n";
    exit;
  }
  $s = file_get_contents($jsonfile);
  $Config = json_decode($s, /* assoc */ true);
  if ($Config === null) {
    echo "Invalid JSON: " . json_last_error_msg() . "\n";
    exit;
  }
  echo "Loaded JSON $jsonfile.\n";
}

$Sanitizer = new Sanitizer($Config);
$Sanitizer->run($infile, $outfile);

?>
