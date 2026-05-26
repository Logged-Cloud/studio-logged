<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_renders(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $response->assertSeeText('Studio Logged');
    }

    public function test_lab_lists_templates(): void
    {
        $response = $this->get('/lab');
        $response->assertOk();
        $response->assertSeeText('Template lab');
        $response->assertSeeText('Product landing');
    }

    public function test_playground_mounts_against_a_session_page(): void
    {
        $response = $this->get('/playground');
        $response->assertOk();
        $response->assertSee('data-component="page-studio.page-builder"', false);
    }

    public function test_playground_topnav_picker_lists_the_demo_pages_so_the_editor_can_be_pointed_at_them(): void
    {
        // The picker rebinds the editor to a different Page row · no
        // template copying, no fork-into-session. Each entry is a real
        // Page id discovered from the seeded demo routes.
        $this->artisan('db:seed', ['--class' => \Database\Seeders\ModelFinderDemoSeeder::class]);
        $this->artisan('db:seed', ['--class' => \Database\Seeders\ModelFinderRouteSeeder::class]);

        $response = $this->get('/playground');

        $response->assertOk();
        $response->assertSee('My session page');
        $response->assertSee('Article · text manipulation graph');
        $response->assertSee('Product · math + currency graph');
        $response->assertSee('Customer · split + concat graph');
        $response->assertSee('Vintage photo · image pipeline graph');
    }

    public function test_playground_with_page_query_param_mounts_against_that_demo_page(): void
    {
        $this->artisan('db:seed', ['--class' => \Database\Seeders\ModelFinderDemoSeeder::class]);
        $this->artisan('db:seed', ['--class' => \Database\Seeders\ModelFinderRouteSeeder::class]);

        $rd     = \LoggedCloud\PageStudio\Models\RouteDefinition::where('name', 'products.show')->firstOrFail();
        $page   = \LoggedCloud\PageStudio\Models\Page::where('route_id', $rd->id)->firstOrFail();

        $response = $this->get('/playground?page='.$page->id);

        $response->assertOk();
        // The Livewire page-builder mounts with the requested page id ·
        // it lives inside the wire:snapshot JSON which is HTML-encoded,
        // so let Laravel escape our needle (default) and match the
        // encoded form.
        $response->assertSee('"pageId":'.$page->id);
    }

    public function test_playground_unknown_page_id_falls_back_to_the_session_page(): void
    {
        // Stale bookmark / hand-edited URL · should never 404 the
        // playground.
        $response = $this->get('/playground?page=999999');

        $response->assertOk();
    }

    public function test_preview_renders_the_session_block_tree(): void
    {
        $response = $this->withSession([])->get('/preview');
        $response->assertOk();
    }

    public function test_lab_use_swaps_the_session_page_and_redirects(): void
    {
        $response = $this->post('/lab/use/blog');
        $response->assertRedirect('/playground');

        $follow = $this->get('/preview');
        $follow->assertSeeText('Why we built page-studio');
    }

    public function test_landing_is_a_page_studio_page_rendered_through_PageRenderer(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        // Hero heading from DemoTemplates::home()
        $response->assertSeeText('A visual page-builder for Laravel');
        // Animated text reel (Alpine x-data carries the phrases as JSON)
        $response->assertSee('Marketing teams', false);
        // Dogfood banner copy
        $response->assertSee('This page is itself built with', false);
    }

    public function test_playground_seed_home_forks_the_dogfooded_page(): void
    {
        $response = $this->get('/playground?seed=home');
        $response->assertRedirect('/playground');

        $preview = $this->get('/preview');
        $preview->assertSeeText('A visual page-builder for Laravel');
    }

    public function test_landing_template_feature_columns_actually_render_their_content(): void
    {
        // Regression · the Landing template used to pass column children
        // under a 'columns' key on a 2-slot columns block, so the rendered
        // output had empty <div></div> pairs where the three features
        // should have been. Lock the shape via a content assertion.
        $this->post('/lab/use/landing');
        $preview = $this->get('/preview');

        $preview->assertOk();
        $preview->assertSeeText('Code-defined blocks');
        $preview->assertSeeText('Drop-in install');
        $preview->assertSeeText('Variables aware');
    }

    public function test_status_template_panel_columns_actually_render_their_content(): void
    {
        // Same shape bug also lived in the Status template.
        $this->post('/lab/use/status');
        $preview = $this->get('/preview');

        $preview->assertOk();
        $preview->assertSeeText('Operational');
        $preview->assertSeeText('Dashboard');
        $preview->assertSeeText('Email delivery');
    }
}
