<?php

namespace App\PageStudio;

use Illuminate\Support\Str;

class DemoTemplates
{
    /**
     * Slug → metadata + block-tree factory. Each factory returns a fresh
     * block list every call so two visitors picking the same template
     * don't get the same block IDs.
     */
    public static function all(): array
    {
        return [
            'blank' => [
                'name'    => 'Blank',
                'summary' => 'Start with one heading and write your own page from scratch.',
                'blocks'  => fn () => [
                    self::block('heading', ['text' => 'Untitled page', 'level' => 'h1', 'align' => 'left']),
                ],
            ],
            'landing' => [
                'name'    => 'Product landing',
                'summary' => 'Hero, three-up feature row, social proof quote, closing CTA.',
                'blocks'  => fn () => [
                    self::block('hero', [
                        'heading'    => 'Build pages, ship faster',
                        'subheading' => "A visual page-builder that lives inside your Laravel app. No headless CMS to operate, no separate admin to deploy.",
                        'cta_label'  => 'Get started',
                        'cta_href'   => '#',
                    ]),
                    self::block('columns', [], [
                        'columns' => [
                            [self::block('heading', ['text' => 'Code-defined blocks', 'level' => 'h3', 'align' => 'left']),
                             self::block('paragraph', ['text' => 'Drop a PHP class into your app and it shows up in the palette next reload.'])],
                            [self::block('heading', ['text' => 'Drop-in install', 'level' => 'h3', 'align' => 'left']),
                             self::block('paragraph', ['text' => 'composer require, one migration, mount a Livewire component. No second service.'])],
                            [self::block('heading', ['text' => 'Variables aware', 'level' => 'h3', 'align' => 'left']),
                             self::block('paragraph', ['text' => 'Route segments, model lookups, transformation graphs all flow into the page renderer.'])],
                        ],
                    ]),
                    self::block('quote', [
                        'text'   => 'We replaced our CMS with logged-cloud/page-studio in an afternoon. The team noticed instantly.',
                        'author' => 'A happy customer',
                    ]),
                    self::block('button', ['label' => 'Read the docs', 'href' => 'https://github.com/Logged-Cloud/page-studio', 'variant' => 'primary']),
                ],
            ],
            'blog' => [
                'name'    => 'Blog post',
                'summary' => 'Headline, lead paragraph, body sections, a pull quote, a CTA at the end.',
                'blocks'  => fn () => [
                    self::block('heading', ['text' => 'Why we built page-studio', 'level' => 'h1', 'align' => 'left']),
                    self::block('paragraph', ['text' => 'A short story about replacing CKEditor with something authors actually like, in 600 words.']),
                    self::block('heading', ['text' => 'The problem', 'level' => 'h2', 'align' => 'left']),
                    self::block('paragraph', ['text' => 'CKEditor + custom shortcodes works until the second author lands on the team. Then the brittle HTML starts showing.']),
                    self::block('quote', ['text' => 'Editors should not be writing div soup to make a layout that the design system already supports.', 'author' => 'me, hopefully']),
                    self::block('heading', ['text' => 'The fix', 'level' => 'h2', 'align' => 'left']),
                    self::block('paragraph', ['text' => 'Drag-and-drop blocks scoped to the route, with variables threaded through cleanly. Done.']),
                    self::block('button', ['label' => 'Try the demo', 'href' => '/playground', 'variant' => 'primary']),
                ],
            ],
            'email' => [
                'name'    => 'Email blast',
                'summary' => 'Banner heading, single-column body, list of bullets, two CTA buttons.',
                'blocks'  => fn () => [
                    self::block('heading', ['text' => 'You are invited', 'level' => 'h1', 'align' => 'center']),
                    self::block('paragraph', ['text' => "Hi {{ recipient.first_name }}, we are running a webinar next Tuesday on \"Building pages without writing HTML\". One hour, live demo, Q&A."]),
                    self::block('list', [
                        'items' => "Tuesday 7pm\n45-minute walkthrough\n15-minute Q&A\nReplay if you can't make it",
                        'style' => 'bullet',
                    ]),
                    self::block('button', ['label' => 'Save my seat', 'href' => '#', 'variant' => 'primary']),
                    self::block('divider', []),
                    self::block('paragraph', ['text' => "If this is not for you, no worries, you can {{ unsubscribe_url }} any time."]),
                ],
            ],
            'status' => [
                'name'    => 'Status page',
                'summary' => 'Service status grid, recent incidents, planned maintenance notice.',
                'blocks'  => fn () => [
                    self::block('heading', ['text' => 'All systems operational', 'level' => 'h1', 'align' => 'center']),
                    self::block('paragraph', ['text' => 'Last checked just now. Green ticks mean all checks passed in the last 60 seconds.']),
                    self::block('columns', [], [
                        'columns' => [
                            [self::block('panel', ['title' => 'API', 'tone' => 'success'], ['body' => [self::block('paragraph', ['text' => '✓ Operational'])]])],
                            [self::block('panel', ['title' => 'Dashboard', 'tone' => 'success'], ['body' => [self::block('paragraph', ['text' => '✓ Operational'])]])],
                            [self::block('panel', ['title' => 'Email delivery', 'tone' => 'warning'], ['body' => [self::block('paragraph', ['text' => '! Delayed · investigating'])]])],
                        ],
                    ]),
                    self::block('heading', ['text' => 'Planned maintenance', 'level' => 'h2', 'align' => 'left']),
                    self::block('paragraph', ['text' => 'Sunday 02:00-04:00 UTC · database failover drill. Brief 30s API pause expected.']),
                ],
            ],
        ];
    }

    public static function find(string $slug): ?array
    {
        return self::all()[$slug] ?? null;
    }

    /**
     * Build a block dict with a fresh unique id. Layout blocks pass their
     * `children` slot tree through the third arg.
     */
    protected static function block(string $type, array $settings, array $children = []): array
    {
        $b = ['id' => 'b-'.Str::random(6), 'type' => $type, 'settings' => $settings];
        if (! empty($children)) $b['children'] = $children;
        return $b;
    }
}
