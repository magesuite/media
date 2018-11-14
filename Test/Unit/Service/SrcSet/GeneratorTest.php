<?php

namespace MageSuite\Media\Test\Unit\Service\SrcSet;

class GeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryListStub;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \MageSuite\Media\Service\SrcSet\Generator
     */
    protected $generator;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->directoryListStub = $this
            ->getMockBuilder(\Magento\Framework\App\Filesystem\DirectoryList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->generator = $this->objectManager->create(
            \MageSuite\Media\Service\SrcSet\Generator::class,
            ['directoryList' => $this->directoryListStub]
        );
    }

    public function testItCorrectlyResolvesSrcSet()
    {
        $wysiwygUploadDirectoryPath = realpath(__DIR__ . '/../../assets');

        $this->directoryListStub->method('getPath')->willReturn($wysiwygUploadDirectoryPath);

        $this->assertEquals(
            'http://localhost/pub/media/wysiwyg/test.jpg 1920w, http://localhost/pub/media/wysiwyg/.thumbs/480/test.jpg 480w, http://localhost/pub/media/wysiwyg/.thumbs/768/test.jpg 768w',
            $this->generator->generateSrcSet('wysiwyg/test.jpg')
        );
    }

    public function testItCorrectlyResolvesSrcSetByDensity()
    {
        $wysiwygUploadDirectoryPath = realpath(__DIR__ . '/../../assets');

        $this->directoryListStub->method('getPath')->willReturn($wysiwygUploadDirectoryPath);

        $this->assertEquals(
            'http://localhost/pub/media/wysiwyg/.thumbs/480/test.jpg, http://localhost/pub/media/wysiwyg/.thumbs/960/test.jpg 2x',
            $this->generator->generateSrcSetByDensity('wysiwyg/test.jpg')
        );
    }

    public function testItReturnsEmptySrcSetWhenFileDoesNotExist()
    {
        $wysiwygUploadDirectoryPath = realpath(__DIR__ . '/../assets');

        $this->directoryListStub->method('getPath')->willReturn($wysiwygUploadDirectoryPath);

        $this->assertEquals(
            '',
            $this->generator->generateSrcSet('{{media url="wysiwyg/not_existing.jpg"}}')
        );
    }

    public function testItReturnsEmptySrcSetWhenFileIsGif()
    {
        $wysiwygUploadDirectoryPath = realpath(__DIR__ . '/../assets');

        $this->directoryListStub->method('getPath')->willReturn($wysiwygUploadDirectoryPath);

        $this->assertEquals(
            '',
            $this->generator->generateSrcSet('wysiwyg/not_existing.jpg')
        );
    }
}