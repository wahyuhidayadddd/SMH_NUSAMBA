<?php

namespace App\Support;

class PageAssetVersioning
{
    public static function versionStylesheets(string $content): string
    {
        return preg_replace_callback(
            '/<link\b[^>]*>/i',
            static function (array $linkMatch): string {
                $link = preg_replace_callback(
                    '/\bhref\s*=\s*(["\'])(.*?)\1/i',
                    static function (array $hrefMatch): string {
                        $url = $hrefMatch[2];
                        $path = parse_url($url, PHP_URL_PATH);

                        if (! is_string($path) || ! str_starts_with($path, '/assets/css/')) {
                            return $hrefMatch[0];
                        }

                        $assetPath = public_path(ltrim($path, '/'));

                        if (! is_file($assetPath)) {
                            return $hrefMatch[0];
                        }

                        $query = parse_url($url, PHP_URL_QUERY);
                        parse_str(is_string($query) ? $query : '', $queryParameters);
                        $queryParameters['v'] = filemtime($assetPath);
                        $versionedUrl = $path.'?'.http_build_query($queryParameters);

                        return str_replace($url, $versionedUrl, $hrefMatch[0]);
                    },
                    $linkMatch[0],
                );

                return $link;
            },
            $content,
        );
    }
}
