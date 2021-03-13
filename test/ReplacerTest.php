<?php

namespace Test;

use HSteeb\UsfmTools\Replacer;

class ReplacerTest extends  \PHPUnit\Framework\TestCase
{
    private $Replacer;

    public function setUp(): void
    {
        $this->Replacer = new Replacer();
    }

    public function testConstructor()
    {
        $this->assertNotNull($this->Replacer);
    }

    public function testEscapeRefresher()
    {
        $X = ["a" => false];
        $this->assertEquals(false, isset($X["a"]) && $X["a"]);
        // === String basics ===
        // `\d`
        $this->assertEquals(2, strlen("\d"));
        $this->assertEquals('\d', '\\d');
        $this->assertEquals('\d', "\d");
        // `\n`
        $this->assertEquals('\n', '\\n');
        $this->assertEquals(2, strlen("\\n"));
        $this->assertEquals(2, strlen('\n'));
        $this->assertEquals(1, strlen("\n"));
        # therefore not $this->assertEquals('\n', "\n");
        // others
        $this->assertEquals(2, strlen("\a"));
        $this->assertEquals(2, strlen("\p"));
        $this->assertEquals(1, strlen("\r"));
        $this->assertEquals(1, strlen("\t"));
        $this->assertEquals(1, strlen("\v")); // ! not in PHP 5 book

        // === preg_replace basics ===
        // `\d` (regex meta character matches literal)
        $this->assertEquals("x", preg_replace("@\d@"    , "x", "5"  )); # pattern 2 chars '\d' matches 1 char '5'
        $this->assertEquals("x", preg_replace('@\d@'    , "x", "5"  )); # pattern 2 chars '\d' matches 1 char '5'
        $this->assertEquals("x", preg_replace('@\\d@'   , "x", "5"  )); # pattern 2 chars '\d' matches 1 char '5'
        $this->assertEquals("x", preg_replace('@\\\\d@' , "x", '\\d')); # pattern 3 chars '\\d' matches 2 chars '\d'

        // `\n` (regex meta character matches interpreted characters)
        $this->assertEquals("x", preg_replace("@\n@"    , "x", "\n" )); # string 1 char "\n" matches 1 char "\n" literally
        $this->assertEquals("x", preg_replace('@\n@'    , "x", "\n" )); # pattern 2 chars '\n' matches 1 char "\n"
        $this->assertEquals("x", preg_replace("@\\n@"   , "x", "\n" )); # pattern 2 chars '\n' matches 1 char "\n"
        $this->assertEquals("x", preg_replace('@\\\\n@' , "x", "\\n")); # pattern 3 chars '\\n' matches 2 char '\n' = '\\' + 'n'

        // other regex meta character to match literal
        $this->assertEquals("x", preg_replace('@\\\\a@' , "x", '\\a'));
        $this->assertEquals("x", preg_replace('@\\\\b@' , "x", '\\b'));
        $this->assertEquals("x", preg_replace('@\\\\c@' , "x", '\\c'));
        $this->assertEquals("x", preg_replace('@\\\\d@' , "x", '\\d'));
        $this->assertEquals("x", preg_replace('@\\\\e@' , "x", '\\e'));
        $this->assertEquals("x", preg_replace('@\\\\f@' , "x", '\\f'));
        $this->assertEquals("x", preg_replace('@\\\\g@' , "x", '\\g'));

        // multiline
        $this->assertEquals("x", preg_replace("@\nX$\n@mu", "x", "\nX\n"));
    }

    public function testEmptyS5NoP()
    {
        $text = <<<EOTEXT
\\s5
\\c 1
\\v 1 TEXT
EOTEXT;
        $exp = <<<EOEXP
\\c 1
\\p
\\v 1 TEXT
EOEXP;
       $this->assertEquals($exp, $this->Replacer->replaceEmptyS5($text));
    }

    public function testEmptyS5BeforeP()
    {
        $text = <<<EOTEXT
\\s5
\\c 1
\\p
\\v 1 TEXT
EOTEXT;
        $exp = <<<EOEXP
\\c 1
\\p
\\v 1 TEXT
EOEXP;
       $this->assertEquals($exp, $this->Replacer->replaceEmptyS5($text));
    }

    public function testDropIdeUtf8Lc()
    {
        $text = <<<EOTEXT
\\ide utf-8
EOTEXT;
        $exp = <<<EOEXP
EOEXP;
       $this->assertEquals($exp, $this->Replacer->dropIdeUtf8($text));
    }

    public function testDropIdeUtf8Uc()
    {
        $text = <<<EOTEXT
\\ide UTF-8
EOTEXT;
        $exp = <<<EOEXP
EOEXP;
       $this->assertEquals($exp, $this->Replacer->dropIdeUtf8($text));
    }

    public function testKeepIdeOther()
    {
        $text = <<<EOTEXT
\\ide x
EOTEXT;
       $this->assertEquals($text, $this->Replacer->dropIdeUtf8($text));
    }

    public function testReplaceArrayNull()
    {
       $this->assertEquals("usfm", $this->Replacer->replaceArray('usfm', null));
       $this->assertEquals("usfm", $this->Replacer->replaceArray('usfm', []));
    }

    public function testReplaceArrayNonArray()
    {
       $this->expectExceptionMessage('ReplaceArray must be');
       $this->Replacer->replaceArray('usfm', "null");
    }

    public function testReplaceArrayNotPaired()
    {
       $this->expectExceptionMessage('ReplaceArray must be');
       $this->Replacer->replaceArray('usfm', [["from"]]);
    }

    public function testReplaceArrayNonArrays()
    {
       $this->expectExceptionMessage('ReplaceArray must be');
       $this->Replacer->replaceArray('usfm', ["1", "2"]);
    }

    public function testReplaceArraySizeMismatch()
    {
       $this->expectExceptionMessage('ReplaceArray must be');
       $this->Replacer->replaceArray('usfm', [["1"], ["2", "3"]]);
    }

    public function testReplaceArray()
    {
       $this->assertEquals("a \u{ab}X>>", $this->Replacer->replaceArray('a <<@X>>', [ ["@", "<<"], ["", "\u{00ab}"] ]));
    }


}
