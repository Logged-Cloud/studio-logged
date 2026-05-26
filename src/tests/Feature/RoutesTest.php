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
}
