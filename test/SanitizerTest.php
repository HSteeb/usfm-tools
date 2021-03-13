<?php

namespace Test;

use HSteeb\UsfmTools\Sanitizer;

class SanitizerTest extends \PHPUnit\Framework\TestCase
{
    private $Sanitizer;
    private const USFM = <<<EO
\\ide utf-8
EO;

    public function setUp(): void
    {
        $this->Sanitizer = new Sanitizer();
    }

    public function testConstructor()
    {
        $this->assertNotNull($this->Sanitizer);
    }

    public function testSanitize()
    {
        $exp = <<<EOEXP
EOEXP;
        $this->assertEquals($exp, $this->Sanitizer->testSanitize(self::USFM));
    }

    public function testSanitizeWithConfigFalse()
    {
        $exp = <<<EOEXP
\\ide utf-8
EOEXP;
        $Config = [ "dropIdeUtf8" => false ]; # test just one config property
        $Sanitizer = new Sanitizer($Config);
        $this->assertEquals($exp, $Sanitizer->testSanitize(self::USFM));
    }

    public function testSanitizeWithConfigTrue()
    {
        $exp = <<<EOEXP
EOEXP;
        $Config = [ "dropIdeUtf8" => true ]; # test just one config property
        $Sanitizer = new Sanitizer($Config);
        $this->assertEquals($exp, $Sanitizer->testSanitize(self::USFM));
    }

}
