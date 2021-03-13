<?php


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
<?php


class Sanitizer
{
  private const DEFAULTCONFIG = [
    // see README
    "replaceEmptyS5" => false
  , "replace" => null
  , "dropIdeUtf8"    => false
  ];

  private $Replacer;
  private $Config = null; // only set if passed to c'tor; @see optionTruthy().

  /**
   * @param {Array} $Config options: see README.
   */
  function __construct($Config = null)
  {
    if ($Config) {
      $this->Config = array_merge(self::DEFAULTCONFIG, $Config);
    }
    $this->Replacer = new Replacer();
  }

  /**
   * @param {String} $infile path to source file
   * @param {String} $outfile path to result file
   */
  function run($infile, $outfile)
  {
    try {
      $usfm = file_get_contents($infile);
      echo "Loaded $infile\n";

      $usfm = $this->sanitize($usfm);
      echo "Sanitized.\n";

      $bytesWritten = file_put_contents($outfile, $usfm);
      if ($bytesWritten === false) {
        throw new Exception("Failed to write " . $outfile);
      }
      echo "Saved $outfile.\n";

    }
    catch (Exception $E) {
      echo "run: " . $E->getMessage();
    }
  }

  /**
   * For unit test
   * @param {String} $usm the USFM Bible book text to sanitize
   * @return {String} the resulting USFM text
   */
  function testSanitize($usfm)
  {
    return $this->sanitize($usfm);
  }

  private function sanitize($usfm)
  {
    if ($this->optionTruthy("replaceEmptyS5")) {
      $usfm = $this->Replacer->replaceEmptyS5($usfm);
    }
    if (isset($this->Config["replace"])) {
      $usfm = $this->Replacer->replaceArray($usfm, $this->Config["replace"]);
    }

    if ($this->optionTruthy("dropIdeUtf8")) {
      $usfm = $this->Replacer->dropIdeUtf8($usfm);
    }

    return $usfm;
  }

  /**
   * @param {String} $option key in $this->Config
   * @return true if $this->Config not set (= default: run all options), or $this->Config[$options] is truthy.
   */
  private function optionTruthy($option)
  {
    return !$this->Config || (isset($this->Config[$option]) && $this->Config[$option]);
  }

}
?>
<?php



class Replacer
{
  private const LINEREST = "[^\n]*";

  /**
   * @param {String} $usfm the USFM Bible text
   * For `\s5` without section text:
   *  - if followed by `\c` and `\v`: replaces it by `\p`,
   *  - otherwise drops it.
   */
  function replaceEmptyS5($text)
  {
    $reEscS = "\\\\s";
    $reEscC = "\\\\c";
    $reEscV = "\\\\v";

    $text = preg_replace("@^$reEscS\d?\b[^\n]*\n($reEscC\b" . self::LINEREST . "\n)($reEscV\b)@mu", "$1\p\n$2", $text);
    $text = preg_replace("@^$reEscS\d?\b[^\n]*\n($reEscC\b" . self::LINEREST . "\n\\\\p\n)@mu", "$1", $text);
    #$text = preg_replace("@^\\s\d?\b[^\n]*\n@mu", "", $text);

    return $text;
  }

  /**
   * @param {String} $usfm the USFM Bible text
   * Drops `\ide utf-8` (case-insensitive), usfm2osis.py fails on it.
   */
  function dropIdeUtf8($text)
  {
    $text = preg_replace("@^\\\\ide\s+utf-8\s*@miu", "", $text);
    return $text;
  }

  /**
   * @param {String} $text the USFM text.
   * @param {Array|null} $ReplaceArray array of two arrays of equal size, to be replaced element-wise (PHP: str_replace)
   * e.g. [ ["@", "<<", ">>"], ["", "\u{00ab}", "\u{00bb}"] ]
   * replaces "@" by empty string, angle brackets "<<"/">>" by typographic guillemets (Unicode U+00AB/U+00BB)
   * @see osis2html::Replacer::replaceArray()
   */
  function replaceArray($text, $ReplaceArray)
  {
    if ($ReplaceArray) {
      if (!is_array($ReplaceArray) || count($ReplaceArray) != 2
          || !is_array($ReplaceArray[0]) || !is_array($ReplaceArray[1])
          || count($ReplaceArray[0]) != count($ReplaceArray[1])
          ) {
        throw new \Exception("ReplaceArray must be null or array containing two arrays of equal size");
      }
      $text = str_replace($ReplaceArray[0], $ReplaceArray[1], $text);
    }
    return $text;
  }

}
?>
