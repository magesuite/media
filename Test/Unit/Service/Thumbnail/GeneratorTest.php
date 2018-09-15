<?php

namespace MageSuite\Navigation\Test\Unit\Service\Thumbnail;

class GeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \MageSuite\Media\Service\Thumbnail\Generator
     */
    protected $thumbnailGenerator;

    protected $targetWidthsDefault= [480, 768, 1024, 1440];
    protected $targetWidthsCategory = [480, 960];

    protected $thumbsDirectory = __DIR__ . '/../../assets/.thumbs';

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->thumbnailGenerator = $this->objectManager->create(\MageSuite\Media\Service\Thumbnail\Generator::class);

        $this->cleanThumbsDirectory();
    }

    public function testItResizesImagesProperly()
    {

        $this->thumbnailGenerator->generateThumbnails(realpath(__DIR__ . '/../../assets/test.jpg'));

        foreach ($this->targetWidthsDefault as $targetWidth) {
            list($resizedImageWidth) = getimagesize($this->thumbsDirectory . '/' . $targetWidth . '/test.jpg');

            $this->assertEquals($targetWidth, $resizedImageWidth);
        }
    }

    public function testItResizesImagesForCategoryProperly()
    {

        $this->thumbnailGenerator->generateThumbnails(realpath(__DIR__ . '/../../assets/test.jpg'), 'category');

        foreach ($this->targetWidthsCategory as $targetWidth) {
            list($resizedImageWidth) = getimagesize($this->thumbsDirectory . '/' . $targetWidth . '/test.jpg');

            $this->assertEquals($targetWidth, $resizedImageWidth);
        }
    }

    public function testFileNoExist()
    {
        $result = $this->thumbnailGenerator->generateThumbnails(realpath(__DIR__ . '/../../assets/no_exist.jpg'));

        $this->assertEmpty($result);
    }

    public function cleanThumbsDirectory()
    {
        if (!file_exists($this->thumbsDirectory)) {
            return;
        }

        $this->deleteDirectory($this->thumbsDirectory);
    }

    public function deleteDirectory($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->deleteDirectory("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }
}