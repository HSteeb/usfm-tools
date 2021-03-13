<?php
namespace HSteeb\UsfmTools;

use HSteeb\UsfmTools\Replacer;

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
