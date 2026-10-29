<?php

namespace App\Http\Controllers;

use App\Models\CmsPage;
use App\Support\CmsPageSource;
use App\Support\PageAnimationTiming;
use App\Support\PageAssetVersioning;
use Illuminate\Http\Response;

class SitePageController extends Controller
{
    public function home(): Response
    {
        return $this->renderPage('index.html');
    }

    public function show(string $path): Response
    {
        return $this->renderPage($path);
    }

    private function renderPage(string $path): Response
    {
        $sourcePath = CmsPageSource::liveSourcePathFor($path);

        if ($sourcePath !== null) {
            $content = CmsPageSource::read($sourcePath);
        } else {
            $page = CmsPage::query()
                ->where('path', $path)
                ->where('is_published', true)
                ->firstOrFail();
            $content = $page->content;
        }

        $content = PageAnimationTiming::normalize($content);
        $content = PageAssetVersioning::versionStylesheets($content);
        $stylesheet = '<link rel="stylesheet" href="'.asset('assets/css/responsive.css').'?v='.filemtime(public_path('assets/css/responsive.css')).'">';
        $content = preg_replace('/<\/head>/i', $stylesheet.'</head>', $content, 1, $replacements);

        if ($replacements === 0) {
            $content = $stylesheet.$content;
        }

        if ($path === 'index.html') {
            $content = preg_replace(
                '/<\/head>/i',
                '<script src="'.asset('assets/js/page-loader.js').'" defer></script></head>',
                $content,
                1,
                $headReplacements,
            );

            if ($headReplacements === 0) {
                $content = '<script src="'.asset('assets/js/page-loader.js').'" defer></script>'.$content;
            }

            $content = preg_replace(
                '/<\/head>/i',
                '<script src="'.asset('assets/js/partners-carousel.js').'" defer></script></head>',
                $content,
                1,
                $headReplacements,
            );

            if ($headReplacements === 0) {
                $content = '<script src="'.asset('assets/js/partners-carousel.js').'" defer></script>'.$content;
            }

            $content = preg_replace(
                '/<\/head>/i',
                '<script src="'.asset('assets/js/promo-carousel.js').'?v='.filemtime(public_path('assets/js/promo-carousel.js')).'" defer></script></head>',
                $content,
                1,
                $headReplacements,
            );

            if ($headReplacements === 0) {
                $content = '<script src="'.asset('assets/js/promo-carousel.js').'?v='.filemtime(public_path('assets/js/promo-carousel.js')).'" defer></script>'.$content;
            }

            $content = preg_replace(
                '/<\/head>/i',
                '<script src="'.asset('assets/js/home-carousel-swipe.js').'?v='.filemtime(public_path('assets/js/home-carousel-swipe.js')).'" defer></script></head>',
                $content,
                1,
                $headReplacements,
            );

            if ($headReplacements === 0) {
                $content = '<script src="'.asset('assets/js/home-carousel-swipe.js').'?v='.filemtime(public_path('assets/js/home-carousel-swipe.js')).'" defer></script>'.$content;
            }

            $loader = '<div class="page-loader" data-page-loader role="status" aria-label="Memuat halaman"><span class="page-loader__spinner" aria-hidden="true"></span><span class="page-loader__label">BPR NUSAMBA</span></div>';
            $content = preg_replace('/<body\b[^>]*>/i', '$0'.$loader, $content, 1, $bodyReplacements);

            if ($bodyReplacements === 0) {
                $content = $loader.$content;
            }
        }

        return response($content, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }
}
