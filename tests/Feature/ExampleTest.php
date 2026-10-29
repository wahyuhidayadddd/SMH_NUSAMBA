<?php

namespace Tests\Feature;

use App\Models\CmsPage;
use Database\Seeders\CmsPageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $this->seed(CmsPageSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertSee('/assets/css/nicepage.css')
            ->assertSee('/assets/css/responsive.css');

        $this->assertSame(30, CmsPage::count());

        foreach (CmsPage::query()->get() as $page) {
            $this->get('/'.$page->path)
                ->assertOk()
                ->assertSee('/assets/css/responsive.css?v=');
        }
    }
}
