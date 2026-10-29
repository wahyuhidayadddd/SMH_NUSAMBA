<?php

namespace Database\Seeders;

use App\Models\CmsPage;
use App\Support\CmsPageSource;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use RuntimeException;

class CmsPageSeeder extends Seeder
{
    public function run(): void
    {
        $directory = resource_path('content/pages');

        if (! File::isDirectory($directory)) {
            throw new RuntimeException('Sumber halaman tidak ditemukan: '.$directory);
        }

        $sourcePaths = config('cms-pages.source_paths', []);

        foreach (File::allFiles($directory) as $file) {
            if (strtolower($file->getExtension()) !== 'html') {
                continue;
            }

            $sourcePath = str_replace('\\', '/', $file->getRelativePathname());
            $path = $sourcePaths[$sourcePath] ?? $sourcePath;
            $content = CmsPageSource::read($sourcePath);
            $title = CmsPageSource::extractTitle($content, $path);

            CmsPage::updateOrCreate(
                ['path' => $path],
                [
                    'title' => $title,
                    'meta_description' => CmsPageSource::extractMetaDescription($content),
                    'section' => $this->sectionFor($path),
                    'content' => $content,
                    'is_published' => true,
                ],
            );
        }
    }

    private function sectionFor(string $path): string
    {
        $upperPath = strtoupper($path);

        return match (true) {
            str_starts_with($upperPath, 'BLOG/') => 'Blog',
            str_starts_with($upperPath, 'TENTANG-') => 'Tentang',
            str_starts_with($upperPath, 'LAYANAN-') => 'Layanan',
            str_starts_with($upperPath, 'PUBLIKASI-') => 'Publikasi',
            str_starts_with($upperPath, 'KARIR') => 'Karir',
            str_starts_with($upperPath, 'KONTAK') => 'Kontak',
            str_starts_with($upperPath, 'MARKETING') => 'Marketing',
            str_starts_with($upperPath, 'RIPLAY') => 'Informasi',
            default => 'Beranda',
        };
    }
}
