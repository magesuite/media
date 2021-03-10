<?php

namespace MageSuite\Media\Test\Unit\Service;

class MediaResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \MageSuite\Media\Service\MediaResolver
     */
    protected $mediaResolver;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->mediaResolver = $this->objectManager->create(\MageSuite\Media\Service\MediaResolver::class);
    }

    public function testItCorrectlyResolvesMediaPath()
    {
        $url = $this->mediaResolver->resolve('{{media url="wysiwyg/test.png"}}');
        $url = str_replace('pub/', '', $url);
        $this->assertEquals(
            'http://localhost/media/wysiwyg/test.png',
            $url
        );
    }
}
