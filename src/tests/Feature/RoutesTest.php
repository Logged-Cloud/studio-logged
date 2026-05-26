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

    public function test_playground_topnav_offers_a_template_picker_so_visitors_can_change_the_page(): void
    {
        // The picker is what closes the "from the playground we should be
        // able to change the page" UX gap · without it the only way to
        // swap content was a separate /lab visit.
        $response = $this->get('/playground');

        $response->assertOk();
        $response->assertSee('Load template…');
        $response->assertSee('Product landing'); // one of the seeded templates
        $response->assertSee('action=""', false); // form is rendered, action set client-side from the picked slug
    }

    public function test_playground_topnav_offers_demo_route_shortcuts(): void
    {
        $response = $this->get('/playground');

        $response->assertOk();
        $response->assertSee('Open demo route…');
        $response->assertSee('/docs/getting-started');
        $response->assertSee('/products/STUDIO-PRO');
        $response->assertSee('/customers/ada@example.com');
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
