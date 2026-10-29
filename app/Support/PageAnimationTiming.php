<?php

namespace App\Support;

final class PageAnimationTiming
{
    public static function normalize(string $content): string
    {
        $content = preg_replace_callback(
            '/(\bdata-animation-delay\s*=\s*)(?:(["\'])(\d+(?:\.\d+)?)\2|(\d+(?:\.\d+)?))/i',
            fn (array $matches): string => $matches[1].($matches[2] !== '' ? $matches[2].'0'.$matches[2] : '0'),
            $content,
        );

        if ($content === null) {
            throw new \RuntimeException('Jeda animasi halaman tidak dapat diperbarui.');
        }

        $content = preg_replace_callback(
            '/(\bdata-animation-duration\s*=\s*)(?:(["\'])(\d+(?:\.\d+)?)\2|(\d+(?:\.\d+)?))/i',
            fn (array $matches): string => $matches[1].($matches[2] !== '' ? $matches[2].min(300, (int) $matches[3]).$matches[2] : (string) min(300, (int) $matches[4])),
            $content,
        );

        if ($content === null) {
            throw new \RuntimeException('Durasi animasi halaman tidak dapat diperbarui.');
        }

        return $content;
    }
}
