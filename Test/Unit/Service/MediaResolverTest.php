<?php

namespace MageSuite\Media\Test\Unit\Service;

class MediaResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * @var \MageSuite\Media\Service\MediaResolver
     */
    private $mediaResolver;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->mediaResolver = $this->objectManager->create(\MageSuite\Media\Service\MediaResolver::class);
    }

    public function testItCorrectlyResolvesMediaPath()
    {
        $this->assertEquals(
            'http://localhost/pub/media/wysiwyg/test.png',
            $this->mediaResolver->resolve('{{media url="wysiwyg/test.png"}}')
        );
    }
}
