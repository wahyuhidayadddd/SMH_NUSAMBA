<?php

namespace Tests\Feature;

use App\Models\CmsPage;
use App\Support\CmsPageSource;
use App\Support\PageAnimationTiming;
use Database\Seeders\CmsPageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CmsPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_organized_page_sources_keep_their_existing_cms_paths(): void
    {
        $this->seed(CmsPageSeeder::class);

        $this->assertDatabaseCount('cms_pages', 30);
        $this->assertDatabaseHas('cms_pages', [
            'path' => 'index.html',
            'section' => 'Beranda',
        ]);
        $this->assertDatabaseHas('cms_pages', [
            'path' => 'HOME.html',
            'section' => 'Beranda',
        ]);
        $this->assertDatabaseHas('cms_pages', [
            'path' => 'blog/post.html',
            'section' => 'Blog',
        ]);
        $this->assertDatabaseMissing('cms_pages', [
            'path' => 'beranda/index.html',
        ]);
    }

    public function test_published_site_pages_are_served_as_html(): void
    {
        CmsPage::create([
            'path' => 'KONTAK.html',
            'title' => 'Home',
            'section' => 'Kontak',
            'content' => '<!doctype html><title>Home</title>',
            'is_published' => true,
        ]);

        $this->get('/KONTAK.html')
            ->assertOk()
            ->assertHeader('content-type', 'text/html; charset=UTF-8')
            ->assertSee('/assets/css/responsive.css')
            ->assertSee('<title>Home</title>', false);
    }

    public function test_beranda_pages_are_served_from_their_separate_live_sources(): void
    {
        CmsPage::create([
            'path' => 'index.html',
            'title' => 'Stale CMS index',
            'section' => 'Beranda',
            'content' => '<title>Stale CMS index</title>',
            'is_published' => true,
        ]);
        CmsPage::create([
            'path' => 'HOME.html',
            'title' => 'Stale CMS home',
            'section' => 'Beranda',
            'content' => '<title>Stale CMS home</title>',
            'is_published' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('<title>HOME</title>', false)
            ->assertSee('/assets/js/home-carousel-swipe.js', false)
            ->assertSee('id="carousel-32d8" data-interval="3000"', false)
            ->assertSee('/assets/css/responsive.css?v=', false)
            ->assertDontSee('Stale CMS index');

        $this->get('/HOME.html')
            ->assertOk()
            ->assertSee('http-equiv="refresh"', false)
            ->assertDontSee('Stale CMS home');
    }

    public function test_page_stylesheets_are_versioned_to_show_design_updates(): void
    {
        CmsPage::create([
            'path' => 'index.html',
            'title' => 'Home',
            'section' => 'Beranda',
            'content' => '<!doctype html><html><head><link rel="stylesheet" href="/assets/css/index.css"></head><body></body></html>',
            'is_published' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('href="/assets/css/index.css?v='.filemtime(public_path('assets/css/index.css')).'"', false);
    }

    public function test_home_page_has_a_loader_without_changing_saved_content(): void
    {
        $content = '<!doctype html><html><head><title>Home</title></head><body><main>Home</main></body></html>';
        $page = CmsPage::create([
            'path' => 'index.html',
            'title' => 'Home',
            'section' => 'Beranda',
            'content' => $content,
            'is_published' => true,
        ]);
        CmsPage::create([
            'path' => 'KONTAK.html',
            'title' => 'Kontak',
            'section' => 'Kontak',
            'content' => '<!doctype html><html><head><title>Kontak</title></head><body></body></html>',
            'is_published' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('data-page-loader', false)
            ->assertSee('/assets/js/page-loader.js', false)
            ->assertSee('/assets/js/partners-carousel.js', false)
            ->assertSee('/assets/js/promo-carousel.js', false)
            ->assertSee('/assets/js/home-carousel-swipe.js', false);

        $this->get('/KONTAK.html')
            ->assertOk()
            ->assertDontSee('data-page-loader', false)
            ->assertDontSee('/assets/js/page-loader.js', false)
            ->assertDontSee('/assets/js/partners-carousel.js', false)
            ->assertDontSee('/assets/js/promo-carousel.js', false);

        $this->assertDatabaseHas('cms_pages', [
            'id' => $page->id,
            'content' => $content,
        ]);
    }

    public function test_assets_use_the_forwarded_https_scheme_behind_a_proxy(): void
    {
        CmsPage::create([
            'path' => 'index.html',
            'title' => 'Home',
            'section' => 'Beranda',
            'content' => '<!doctype html><html><head><title>Home</title></head><body></body></html>',
            'is_published' => true,
        ]);

        $this->get('/', [
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-Host' => 'cms.example.test',
        ])
            ->assertOk()
            ->assertSee('href="https://cms.example.test/assets/css/responsive.css?v=', false);
    }

    public function test_unpublished_site_pages_are_not_publicly_available(): void
    {
        CmsPage::create([
            'path' => 'draft.html',
            'title' => 'Draft',
            'section' => 'Beranda',
            'content' => '<title>Draft</title>',
            'is_published' => false,
        ]);

        $this->get('/draft.html')->assertNotFound();
    }

    public function test_scroll_animations_start_without_delay_and_keep_saved_content_unchanged(): void
    {
        $content = '<!doctype html><html><head><title>Home</title></head><body><div data-animation-name="customAnimationIn" data-animation-delay="1000" data-animation-duration="1000"></div></body></html>';
        $page = CmsPage::create([
            'path' => 'index.html',
            'title' => 'Home',
            'section' => 'Beranda',
            'content' => $content,
            'is_published' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('data-animation-delay="0"', false)
            ->assertSee('data-animation-duration="300"', false);

        $this->actingAsAdmin()
            ->get(route('cms.pages.preview', $page))
            ->assertOk()
            ->assertSee('data-animation-delay="0"', false)
            ->assertSee('data-animation-duration="300"', false);

        $this->assertDatabaseHas('cms_pages', [
            'id' => $page->id,
            'content' => $content,
        ]);
    }

    public function test_animation_timing_normalization_preserves_short_durations_and_caps_unquoted_values(): void
    {
        $content = '<div data-animation-delay="150" data-animation-duration="200"></div><div data-animation-delay=700 data-animation-duration=900></div>';

        $this->assertSame(
            '<div data-animation-delay="0" data-animation-duration="200"></div><div data-animation-delay=0 data-animation-duration=300></div>',
            PageAnimationTiming::normalize($content),
        );
    }

    public function test_cms_requires_login_and_can_update_a_page(): void
    {
        config([
            'cms.admin_email' => 'admin',
            'cms.admin_password' => 'admin',
        ]);
        $page = CmsPage::create([
            'path' => 'landing-draft.html',
            'title' => 'Home',
            'meta_description' => 'Original description',
            'section' => 'Beranda',
            'content' => '<!doctype html><html><head><title>Home</title></head><body><p>Old content</p></body></html>',
            'is_published' => true,
        ]);

        $this->get('/admin')->assertRedirect(route('cms.login'));

        $this->post(route('cms.login.submit'), [
            'email' => 'admin',
            'password' => 'admin',
        ])->assertRedirect(route('cms.index'));

        $this->get(route('cms.index'))
            ->assertOk()
            ->assertSee('Kelola konten')
            ->assertSee('Beranda');

        $this->patch(route('cms.pages.update', $page), [
            'title' => 'Beranda Baru',
            'meta_description' => 'Deskripsi beranda baru',
            'content' => '<!doctype html><html><head><title>Home</title></head><body><p>New content</p></body></html>',
            'is_published' => '1',
        ])->assertRedirect(route('cms.index', ['page' => $page->id, 'tab' => 'Beranda']));

        $this->assertDatabaseHas('cms_pages', [
            'id' => $page->id,
            'title' => 'Beranda Baru',
            'meta_description' => 'Deskripsi beranda baru',
            'content' => '<!doctype html><html><head><title>Beranda Baru</title><meta name="description" content="Deskripsi beranda baru"></head><body><p>New content</p></body></html>',
            'is_published' => true,
        ]);
    }

    public function test_beranda_cms_pages_use_live_sources_in_read_only_preview(): void
    {
        $this->seed(CmsPageSeeder::class);

        $homePage = CmsPage::query()->where('path', 'HOME.html')->firstOrFail();
        $indexPage = CmsPage::query()->where('path', 'index.html')->firstOrFail();

        $this->actingAsAdmin()
            ->get(route('cms.index', ['page' => $homePage->id, 'tab' => 'Beranda']))
            ->assertOk()
            ->assertSee('beranda/home.html')
            ->assertSee('Perubahan file langsung tampil di website')
            ->assertDontSee('Simpan perubahan');

        $this->actingAsAdmin()
            ->patch(route('cms.pages.update', $homePage), [
                'title' => 'Edit CMS yang tidak boleh diterapkan',
                'meta_description' => '',
                'content' => '<title>Stale CMS content</title>',
            ])
            ->assertRedirect(route('cms.index', ['page' => $homePage->id, 'tab' => 'Beranda']));

        $this->assertDatabaseHas('cms_pages', [
            'id' => $homePage->id,
            'content' => $homePage->content,
        ]);

        $this->assertSame('beranda/index.html', CmsPageSource::liveSourcePathFor($indexPage->path));
        $this->assertSame('beranda/home.html', CmsPageSource::liveSourcePathFor($homePage->path));
    }

    public function test_visual_preview_and_media_upload_require_authentication(): void
    {
        $page = CmsPage::create([
            'path' => 'index.html',
            'title' => 'Home',
            'section' => 'Beranda',
            'content' => '<!doctype html><html><head><title>Home</title></head><body><p>Home</p></body></html>',
            'is_published' => true,
        ]);

        $this->get(route('cms.pages.preview', $page))->assertRedirect(route('cms.login'));
        $this->post(route('cms.media.store'))->assertRedirect(route('cms.login'));
    }

    public function test_media_upload_rejects_svg_files(): void
    {
        $this->actingAsAdmin()
            ->postJson(route('cms.media.store'), [
                'image' => UploadedFile::fake()->create('unsafe.svg', 1, 'image/svg+xml'),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('image');
    }

    public function test_non_source_page_preview_injects_editor_assets_without_changing_saved_content(): void
    {
        config([
            'cms.admin_email' => 'admin',
            'cms.admin_password' => 'admin',
        ]);
        $page = CmsPage::create([
            'path' => 'KONTAK.html',
            'title' => 'Kontak',
            'section' => 'Kontak',
            'content' => '<!doctype html><html><head><title>Home</title></head><body><p>Home</p></body></html>',
            'is_published' => true,
        ]);

        $this->actingAsAdmin()
            ->get(route('cms.pages.preview', $page))
            ->assertOk()
            ->assertHeader('cache-control', 'no-store, private')
            ->assertSee('cms-page-editor.js')
            ->assertSee('cms-page-editor.css');

        $this->assertDatabaseHas('cms_pages', [
            'id' => $page->id,
            'content' => '<!doctype html><html><head><title>Home</title></head><body><p>Home</p></body></html>',
        ]);
    }

    private function actingAsAdmin(): static
    {
        config([
            'cms.admin_email' => 'admin',
            'cms.admin_password' => 'admin',
        ]);

        return $this->withSession(['cms_authenticated' => true]);
    }
}
