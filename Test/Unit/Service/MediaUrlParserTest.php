<?php

namespace MageSuite\Media\Test\Unit\Service;

class MediaUrlParserTest extends \PHPUnit\Framework\TestCase
{
    protected \MageSuite\Media\Service\MediaUrlParser $mediaUrlParser;

    public function setUp(): void
    {
        $this->mediaUrlParser = new \MageSuite\Media\Service\MediaUrlParser();
    }

    public function testItParsesMediaDirective()
    {
        $this->assertSame('wysiwyg/test.png', $this->mediaUrlParser->parse('{{media url="wysiwyg/test.png"}}'));
    }

    public function testItReturnsEmptyStringWhenDirectiveHasNoUrl()
    {
        $this->assertSame('', $this->mediaUrlParser->parse('{{media}}'));
        $this->assertSame('', $this->mediaUrlParser->parse('wysiwyg/test.png'));
    }

    public function testItStripsMediaPrefixFromAbsoluteUrl()
    {
        $this->assertSame('wysiwyg/test.png', $this->mediaUrlParser->parse('http://localhost/media/wysiwyg/test.png'));
    }

    public function testItStripsPubMediaPrefixFromAbsoluteUrl()
    {
        $this->assertSame('wysiwyg/test.png', $this->mediaUrlParser->parse('http://localhost/pub/media/wysiwyg/test.png'));
    }

    public function testItKeepsMediaSegmentsInsideThePath()
    {
        $this->assertSame('wysiwyg/media/test.png', $this->mediaUrlParser->parse('http://localhost/media/wysiwyg/media/test.png'));
        $this->assertSame('wysiwyg/pub/media/test.png', $this->mediaUrlParser->parse('http://localhost/media/wysiwyg/pub/media/test.png'));
    }
}
