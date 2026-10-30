<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class CmsPageSource
{
    public static function liveSourcePathFor(string $pagePath): ?string
    {
        $sourcePaths = config('cms-pages.live_source_paths', []);
        $sourcePath = $sourcePaths[$pagePath] ?? null;

        return is_string($sourcePath) ? $sourcePath : null;
    }

    public static function sourcePathForPage(string $pagePath): ?string
    {
        return self::liveSourcePathFor($pagePath) ?? self::sourcePathFor($pagePath);
    }

    public static function sourcePathFor(string $pagePath): ?string
    {
        $sourcePath = array_search($pagePath, config('cms-pages.source_paths', []), true);

        return is_string($sourcePath) ? $sourcePath : null;
    }

    public static function read(string $sourcePath): string
    {
        $directory = realpath(resource_path('content/pages'));
        $file = $directory === false
            ? false
            : realpath($directory.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $sourcePath));

        if ($directory === false) {
            throw new RuntimeException('Sumber halaman tidak ditemukan: '.$sourcePath);
        }

        if ($file === false) {
            abort(404, 'Sumber halaman tidak ditemukan.');
        }

        if (! str_starts_with($file, $directory.DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('Lokasi sumber halaman tidak valid: '.$sourcePath);
        }

        abort_unless(File::isFile($file), 404, 'Sumber halaman tidak ditemukan.');

        return self::rewriteAssetPaths(File::get($file));
    }

    public static function extractTitle(string $content, string $path): string
    {
        if (preg_match('/<title\b[^>]*>(.*?)<\/title>/is', $content, $matches)) {
            $title = html_entity_decode(strip_tags($matches[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');

            if (filled(trim($title))) {
                return trim($title);
            }
        }

        return Str::headline(pathinfo($path, PATHINFO_FILENAME));
    }

    public static function extractMetaDescription(string $content): ?string
    {
        if (preg_match_all('/<meta\b[^>]*>/is', $content, $tags)) {
            foreach ($tags[0] as $tag) {
                if (preg_match('/\bname\s*=\s*(["\'])description\1/i', $tag) && preg_match('/\bcontent\s*=\s*(["\'])(.*?)\1/is', $tag, $matches)) {
                    return Str::limit(html_entity_decode($matches[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'), 500, '');
                }
            }
        }

        return null;
    }

    private static function rewriteAssetPaths(string $content): string
    {
        $rewrite = static function (string $url): string {
            if ($url === '' || preg_match('/^(?:[a-z][a-z0-9+.-]*:|\/\/|#)/i', $url)) {
                return $url;
            }

            $parts = parse_url($url);
            $path = $parts['path'] ?? '';
            $path = preg_replace('#^(?:\.\.?/)+#', '', $path);
            $path = ltrim($path, '/');

            if ($path === '') {
                return $url;
            }

            if (str_starts_with($path, 'images/')) {
                $assetPath = 'assets/'.$path;
            } elseif (str_starts_with($path, 'files/')) {
                $assetPath = 'assets/'.$path;
            } elseif (str_starts_with($path, 'intlTelInput/')) {
                $assetPath = 'assets/vendor/'.$path;
            } else {
                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $assetPath = match ($extension) {
                    'css' => 'assets/css/'.$path,
                    'js' => 'assets/js/'.$path,
                    default => null,
                };
            }

            if ($assetPath === null) {
                return $url;
            }

            $suffix = isset($parts['query']) ? '?'.$parts['query'] : '';
            $suffix .= isset($parts['fragment']) ? '#'.$parts['fragment'] : '';

            return '/'.$assetPath.$suffix;
        };

        $content = preg_replace_callback(
            '/\b(?:src|href|poster)\s*=\s*(["\'])(.*?)\1/i',
            static function (array $matches) use ($rewrite): string {
                return str_replace($matches[2], $rewrite($matches[2]), $matches[0]);
            },
            $content,
        );

        return preg_replace_callback(
            '/url\(\s*(["\']?)([^)\'"]+)\1\s*\)/i',
            static function (array $matches) use ($rewrite): string {
                return 'url('.$matches[1].$rewrite(trim($matches[2])).$matches[1].')';
            },
            $content,
        );
    }
}
