<?php

namespace MageSuite\Media\Service;

class SrcSetResolver
{
    /**
     * @var SrcSet\Generator
     */
    protected $srcSetGenerator;

    /**
     * @var MediaUrlParser
     */
    protected $mediaUrlParser;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    public function __construct(
        \Magento\Framework\App\CacheInterface $cache,
        \MageSuite\Media\Service\SrcSet\Generator $srcSetGenerator,
        MediaUrlParser $mediaUrlParser
    )
    {
        $this->cache = $cache;
        $this->srcSetGenerator = $srcSetGenerator;
        $this->mediaUrlParser = $mediaUrlParser;
    }

    public function resolveSrcSet($mediaPath)
    {
        $originalImageUrl = $this->mediaUrlParser->parse($mediaPath);
        $cacheIdentifier = 'src_set_' . md5($originalImageUrl);

        $srcSet = $this->cache->load($cacheIdentifier);

        if ($srcSet == null) {
            $srcSet = $this->srcSetGenerator->generateSrcSet($originalImageUrl);

            $this->cache->save($srcSet, $cacheIdentifier, ['src_sets']);
        }

        return $srcSet;
    }

    public function resolveSrcSetByDensity($mediaPath)
    {
        $originalImageUrl = $this->mediaUrlParser->parse($mediaPath);
        $cacheIdentifier = 'src_set_density_' . md5($originalImageUrl);

        $srcSet = $this->cache->load($cacheIdentifier);

        if ($srcSet == null) {
            $srcSet = $this->srcSetGenerator->generateSrcSetByDensity($originalImageUrl);

            $this->cache->save($srcSet, $cacheIdentifier, ['src_sets']);
        }

        return $srcSet;
    }
}