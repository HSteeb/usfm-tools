<?php

namespace HSteeb\UsfmTools;


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
