<?php

namespace MageSuite\Media\Service;

class MediaResolver
{
    /**
     * @var MediaUrlParser
     */
    protected $mediaUrlParser;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        MediaUrlParser $mediaUrlParser
    )
    {
        $this->storeManager = $storeManager;
        $this->mediaUrlParser = $mediaUrlParser;
    }

    public function resolve($mediaPath)
    {
        $url = $this->mediaUrlParser->parse($mediaPath);

        return $this->getUrl($url);
    }

    protected function getUrl($url)
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $url;
    }
}