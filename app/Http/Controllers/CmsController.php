<?php

namespace App\Http\Controllers;

use App\Models\CmsPage;
use App\Support\CmsPageSource;
use App\Support\PageAnimationTiming;
use App\Support\PageAssetVersioning;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CmsController extends Controller
{
    public function index(Request $request): View
    {
        $pages = CmsPage::query()->orderBy('path')->get();
        $selectedPage = $pages->firstWhere('id', (int) $request->query('page'))
            ?? $pages->firstWhere('path', 'index.html')
            ?? $pages->first();

        return view('cms.index', [
            'pagesBySection' => $pages->groupBy('section'),
            'selectedPage' => $selectedPage,
            'activeSection' => $request->query('tab', $selectedPage?->section),
        ]);
    }

    public function preview(CmsPage $page): Response
    {
        $sourcePath = CmsPageSource::liveSourcePathFor($page->path);
        $content = $sourcePath === null
            ? $page->content
            : CmsPageSource::read($sourcePath);
        $content = PageAnimationTiming::normalize($content);
        $content = PageAssetVersioning::versionStylesheets($content);

        if ($sourcePath !== null) {
            $stylesheet = '<link rel="stylesheet" href="'.asset('assets/css/responsive.css').'?v='.filemtime(public_path('assets/css/responsive.css')).'">';
            $content = preg_replace('/<\/head>/i', $stylesheet.'</head>', $content, 1, $replacements);

            if ($replacements === 0) {
                $content = $stylesheet.$content;
            }

            if ($page->path === 'index.html') {
                $script = '<script src="'.asset('assets/js/home-carousel-swipe.js').'?v='.filemtime(public_path('assets/js/home-carousel-swipe.js')).'" defer></script>';
                $content = preg_replace('/<\/head>/i', $script.'</head>', $content, 1, $replacements);

                if ($replacements === 0) {
                    $content = $script.$content;
                }
            }

            return response($content, 200, [
                'Cache-Control' => 'private, no-store',
                'Content-Type' => 'text/html; charset=UTF-8',
                'X-Frame-Options' => 'SAMEORIGIN',
            ]);
        }

        $editorAssets = '<link rel="stylesheet" href="'.asset('assets/css/responsive.css').'?v='.filemtime(public_path('assets/css/responsive.css')).'" data-cms-preview>'.
            '<link rel="stylesheet" href="'.asset('assets/css/cms-page-editor.css').'" data-cms-preview>'.
            '<script src="'.asset('assets/js/cms-page-editor.js').'" data-cms-preview defer></script>';
        $content = preg_replace('/<\/head>/i', $editorAssets.'</head>', $content, 1, $replacements);

        if ($replacements === 0) {
            $content = $editorAssets.$content;
        }

        return response($content, 200, [
            'Cache-Control' => 'private, no-store',
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }

    public function update(Request $request, CmsPage $page): RedirectResponse
    {
        $liveSourcePath = CmsPageSource::liveSourcePathFor($page->path);

        if ($liveSourcePath !== null) {
            return redirect()
                ->route('cms.index', ['page' => $page->id, 'tab' => $page->section])
                ->with('status', 'Halaman ini memakai sumber langsung: '.$liveSourcePath.'. Edit file tersebut untuk mengubah website.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string', 'max:5242880'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $title = e($validated['title']);
        $description = e($validated['meta_description'] ?? '');
        $content = $this->updateHeadTag($validated['content'], '/<title\b[^>]*>.*?<\/title>/is', '<title>'.$title.'</title>');
        $content = $this->updateHeadTag(
            $content,
            '/<meta\b(?=[^>]*\bname\s*=\s*(["\'])description\1)[^>]*>/is',
            '<meta name="description" content="'.$description.'">',
            '<meta name="description" content="'.$description.'">',
        );

        $page->update([
            'title' => $validated['title'],
            'meta_description' => $validated['meta_description'] ?? null,
            'content' => $content,
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()
            ->route('cms.index', ['page' => $page->id, 'tab' => $page->section])
            ->with('status', 'Konten berhasil disimpan.');
    }

    public function syncSource(CmsPage $page): RedirectResponse
    {
        $liveSourcePath = CmsPageSource::liveSourcePathFor($page->path);

        if ($liveSourcePath !== null) {
            return redirect()
                ->route('cms.index', ['page' => $page->id, 'tab' => $page->section])
                ->with('status', 'Halaman ini sudah memakai sumber langsung: '.$liveSourcePath.'.');
        }

        $sourcePath = CmsPageSource::sourcePathFor($page->path);

        abort_if($sourcePath === null, 404);

        $content = CmsPageSource::read($sourcePath);
        $page->update([
            'title' => CmsPageSource::extractTitle($content, $page->path),
            'meta_description' => CmsPageSource::extractMetaDescription($content),
            'content' => $content,
        ]);

        return redirect()
            ->route('cms.index', ['page' => $page->id, 'tab' => $page->section])
            ->with('status', 'Konten '.$sourcePath.' berhasil dimuat ke halaman CMS ini.');
    }

    private function updateHeadTag(string $content, string $pattern, string $replacement, ?string $missingTag = null): string
    {
        $updated = preg_replace($pattern, $replacement, $content, 1, $count);

        if ($updated === null) {
            throw new \RuntimeException('Metadata halaman tidak dapat diperbarui.');
        }

        if ($count === 0 && $missingTag !== null) {
            $updated = preg_replace('/<\/head>/i', $missingTag.'</head>', $updated, 1, $headCount);

            if ($updated === null || $headCount === 0) {
                throw new \RuntimeException('Tag head halaman tidak ditemukan untuk menyimpan metadata.');
            }
        }

        return $updated;
    }
}
