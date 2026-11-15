<?php

namespace MageSuite\Media\Service;

class MediaUrlParser
{
    public function parse($mediaPath)
    {
        if (preg_match('/\bhttps?:\/\//i', $mediaPath)) {
            $path = (string)parse_url($mediaPath, PHP_URL_PATH);

            return preg_replace('/^\/(?:pub\/)?media\//', '', $path);
        } else {
            preg_match_all('/url="(?<url>.*?)"/si', $mediaPath, $results, PREG_PATTERN_ORDER);
        }

        return $results['url'][0] ?? '';
    }
}
