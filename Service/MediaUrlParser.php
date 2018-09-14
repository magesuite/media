<?php

namespace MageSuite\Media\Service;

class MediaUrlParser
{
    public function parse($mediaPath)
    {
        if (preg_match('/\bhttps?:\/\//i', $mediaPath)) {
            $path = parse_url($mediaPath, PHP_URL_PATH);
            $path = ltrim($path, '/media/');
            $path = ltrim($path, '/pub/media/');

            return $path;
        } else {
            preg_match_all('/url="(?<url>.*?)"/si', $mediaPath, $results, PREG_PATTERN_ORDER);
        }

        return $results['url'][0];
    }
}