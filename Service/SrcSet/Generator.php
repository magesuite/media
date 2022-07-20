<?php

namespace MageSuite\Media\Service\SrcSet;

class Generator
{
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $filterProvider;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider
    )
    {
        $this->storeManager = $storeManager;
        $this->directoryList = $directoryList;
        $this->filterProvider = $filterProvider;
    }

    /**
     * @param $originalImageUrl
     * @return string
     */
    public function generateSrcSet($originalImageUrl)
    {
        $originalImagePath = $this->getMediaDirectoryPath() . $originalImageUrl;
        $originalImageName = pathinfo($originalImagePath, PATHINFO_BASENAME);
        $originalImageDirectory = dirname($originalImagePath);

        if (!$this->imageFileExist($originalImagePath) OR $this->isGif($originalImagePath)) {
            return '';
        }

        list($originalImageWidth) = getimagesize($originalImagePath);

        $srcSet[] = $this->generateSrcSetElement($this->getUrl($originalImageUrl), $originalImageWidth);

        $thumbsDirectory = $originalImageDirectory . '/.thumbs';

        foreach (\MageSuite\Media\Service\Thumbnail\Generator::WIDTHS_DEFAULT as $resizedImageWidth) {
            if ($resizedImageWidth >= $originalImageWidth) {
                continue;
            }

            $resizedFilePath = $thumbsDirectory . '/' . $resizedImageWidth . '/' . $originalImageName;

            if (!file_exists($resizedFilePath)) {
                continue;
            }

            $srcSet[] = $this->generateSrcSetElement($this->getUrlByPath($resizedFilePath), $resizedImageWidth);
        }

        return implode(', ', $srcSet);
    }

    /**
     * @param $originalImageUrl
     * @return string
     */
    public function generateSrcSetByDensity($originalImageUrl)
    {
        $originalImagePath = $this->getMediaDirectoryPath() . $originalImageUrl;
        $originalImageName = pathinfo($originalImagePath, PATHINFO_BASENAME);
        $originalImageDirectory = dirname($originalImagePath);

        if(!$this->imageFileExist($originalImagePath) OR $this->isGif($originalImagePath)){
            return '';
        }

        list($originalImageWidth) = getimagesize($originalImagePath);

        $thumbsDirectory = $originalImageDirectory . '/.thumbs';
        $srcSet = [];

        foreach (\MageSuite\Media\Service\Thumbnail\Generator::WIDTHS_CATEGORY as $resizedImageWidth) {
            if ($resizedImageWidth >= $originalImageWidth) {
                continue;
            }

            $resizedFilePath = $thumbsDirectory . '/' . $resizedImageWidth . '/' . $originalImageName;

            if (!file_exists($resizedFilePath)) {
                continue;
            }

            $srcSet[] = $this->getUrlByPath($resizedFilePath);
        }

        if (count($srcSet) != 2) {
            return '';
        }

        return vsprintf('%s, %s 2x', $srcSet);
    }

    protected function imageFileExist($originalImagePath) {
        if (!file_exists($originalImagePath) OR !is_file($originalImagePath)) {
            return false;
        }

        return true;
    }

    protected function isGif($originalImagePath) {
        $pathParts = pathinfo($originalImagePath);

        return $pathParts['extension'] === 'gif';
    }

    protected function getUrl($url)
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $url;
    }

    /**
     * @return string
     */
    protected function getMediaDirectoryPath()
    {
        return $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA) . '/';
    }

    /**
     * @return mixed
     */
    protected function getUrlByPath($imagePath)
    {
        $url = str_replace($this->getMediaDirectoryPath(), '', $imagePath);

        return $this->getUrl($url);
    }

    protected function generateSrcSetElement($url, $width)
    {
        return sprintf('%s %sw', $url, $width);
    }
}
