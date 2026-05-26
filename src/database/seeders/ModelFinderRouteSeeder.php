<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use LoggedCloud\PageStudio\Models\NodeGraph;
use LoggedCloud\PageStudio\Models\Page;
use LoggedCloud\PageStudio\Models\RouteDefinition;
use LoggedCloud\PageStudio\Models\RouteSegment;
use LoggedCloud\PageStudio\Models\Variable;

/**
 * Seeds four curated demo routes that exercise the Variables
 * Modifier (node graph). Each graph is intentionally non-trivial ·
 * the previous revision had three routes with one finder each, which
 * told you nothing about the transforms available downstream.
 *
 *   /docs/{slug}        Article    text manipulation · concat, uppercase,
 *                                  length, math, format_date
 *   /products/{sku}     Product    numeric pipeline · math /100, currency
 *                                  format, uppercase
 *   /customers/{email}  Customer   string split + first + concat
 *   /showcase/vintage   (none)     image pipeline · brightness + sepia +
 *                                  blur composed end-to-end
 *
 * Idempotent · routes are looked up by name and graphs by route_id,
 * so re-running the seeder during a demo doesn't duplicate.
 */
class ModelFinderRouteSeeder extends Seeder
{
    public function run(): void
    {
        $slug  = $this->ensureVariable('slug',  'slug',   ['examples' => ['getting-started', 'model-finder', 'collab']]);
        $sku   = $this->ensureVariable('sku',   'custom', ['examples' => ['STUDIO-PRO', 'STUDIO-TEAM', 'STUDIO-CLOUD'], 'regex' => '[A-Z0-9-]+']);
        $email = $this->ensureVariable('email', 'any',    ['examples' => ['ada@example.com', 'grace@example.com', 'alan@example.com']]);

        $this->ensureDocsRoute($slug);
        $this->ensureProductsRoute($sku);
        $this->ensureCustomersRoute($email);
        $this->ensureShowcaseRoute();
    }

    protected function ensureVariable(string $name, string $type, array $extra = []): Variable
    {
        return Variable::firstOrCreate(
            ['name' => $name],
            array_merge(['type' => $type, 'examples' => $extra['examples'] ?? []], $extra),
        );
    }

    // ─── /docs/{slug} · text manipulation ──────────────────────────

    protected function ensureDocsRoute(Variable $slug): void
    {
        $route = RouteDefinition::firstOrCreate(
            ['name' => 'docs.show'],
            ['method' => 'GET', 'path_template' => '/docs/{slug}', 'description' => 'Article · text manipulation through the Variables Modifier'],
        );
        if ($route->segments()->count() === 0) {
            RouteSegment::create(['route_id' => $route->id, 'position' => 0, 'kind' => 'literal',  'literal_value' => 'docs']);
            RouteSegment::create(['route_id' => $route->id, 'position' => 1, 'kind' => 'variable', 'variable_id'   => $slug->id]);
        }

        $this->ensurePage($route->id, [
            $this->block('hero', [
                'heading'    => '{{ article_title_loud }}',
                'subheading' => '{{ article_excerpt }}',
                'cta_label'  => '',
                'cta_href'   => '',
                'align'      => 'left',
            ]),
            $this->block('paragraph', ['text' => '{{ byline }} · published {{ article_published_pretty }} · ~{{ reading_minutes }} min read']),
            $this->block('paragraph', ['text' => '{{ article_body }}']),
        ]);

        $this->ensureGraph($route->id, $this->articleGraph());
    }

    protected function articleGraph(): array
    {
        return [
            'nodes' => [
                // Sources
                $this->node('src-slug',      'source.route_variable', ['variable_name' => 'slug'], 0,  400),
                $this->node('src-article',   'source.model_finder',   ['model_class' => 'App\\Models\\Article', 'finder_key' => 'slug', 'expose_fields' => true], 280, 200),
                $this->node('const-by',      'source.constant',       ['value' => 'By '], 0, 720),
                $this->node('const-chars',   'source.constant',       ['value' => '1500'], 0, 880),

                // Text transforms
                $this->node('upper-title',   'transform.uppercase',      [],  720,  20),
                $this->node('concat-byline', 'transform.concat',         ['separator' => ''], 720, 540),
                $this->node('length-body',   'transform.length',         [],  720, 700),
                $this->node('math-minutes',  'transform.math',           ['op' => '/'], 920, 700),
                $this->node('fmt-minutes',   'transform.number_format',  ['decimals' => 0, 'thousands_separator' => ',', 'decimal_separator' => '.'], 1120, 700),

                // Date format
                $this->node('fmt-date',      'transform.format_date',    ['format' => 'F j, Y', 'offset_amount' => 0, 'offset_unit' => 'days'], 720, 380),

                // Outputs (all wired downstream of the transforms)
                $this->node('out-title-loud', 'output', ['name' => 'article_title_loud'],     1320,  20),
                $this->node('out-title',      'output', ['name' => 'article_title'],          1320, 100),
                $this->node('out-excerpt',    'output', ['name' => 'article_excerpt'],        1320, 180),
                $this->node('out-body',       'output', ['name' => 'article_body'],           1320, 260),
                $this->node('out-pub',        'output', ['name' => 'article_published_pretty'], 1320, 380),
                $this->node('out-byline',     'output', ['name' => 'byline'],                 1320, 540),
                $this->node('out-minutes',    'output', ['name' => 'reading_minutes'],        1320, 700),
            ],
            'edges' => [
                // slug → finder.key
                $this->edge('src-slug',    'value', 'src-article', 'key'),

                // title → uppercase → output
                $this->edge('src-article', 'title', 'upper-title', 'text'),
                $this->edge('upper-title', 'value', 'out-title-loud', 'value'),

                // direct outputs
                $this->edge('src-article', 'title',   'out-title',   'value'),
                $this->edge('src-article', 'excerpt', 'out-excerpt', 'value'),
                $this->edge('src-article', 'body',    'out-body',    'value'),

                // published_at → format_date → output
                $this->edge('src-article', 'published_at', 'fmt-date', 'value'),
                $this->edge('fmt-date',    'value',        'out-pub',  'value'),

                // "By " + author_name → byline
                $this->edge('const-by',     'value',       'concat-byline', 'a'),
                $this->edge('src-article',  'author_name', 'concat-byline', 'b'),
                $this->edge('concat-byline','value',       'out-byline',    'value'),

                // body length / 1500 chars-per-minute → number_format → reading_minutes
                $this->edge('src-article',  'body',  'length-body',  'value'),
                $this->edge('length-body',  'value', 'math-minutes', 'a'),
                $this->edge('const-chars',  'value', 'math-minutes', 'b'),
                $this->edge('math-minutes', 'value', 'fmt-minutes',  'value'),
                $this->edge('fmt-minutes',  'value', 'out-minutes',  'value'),
            ],
        ];
    }

    // ─── /products/{sku} · numeric + currency ──────────────────────

    protected function ensureProductsRoute(Variable $sku): void
    {
        $route = RouteDefinition::firstOrCreate(
            ['name' => 'products.show'],
            ['method' => 'GET', 'path_template' => '/products/{sku}', 'description' => 'Product · math + currency format through the Variables Modifier'],
        );
        if ($route->segments()->count() === 0) {
            RouteSegment::create(['route_id' => $route->id, 'position' => 0, 'kind' => 'literal',  'literal_value' => 'products']);
            RouteSegment::create(['route_id' => $route->id, 'position' => 1, 'kind' => 'variable', 'variable_id'   => $sku->id]);
        }

        $this->ensurePage($route->id, [
            $this->block('hero', [
                'heading'    => '{{ product_name_loud }}',
                'subheading' => '{{ product_description }}',
                'cta_label'  => 'Buy now',
                'cta_href'   => '#',
                'align'      => 'left',
            ]),
            $this->block('image', ['src' => '{{ product_image }}', 'alt' => '{{ product_name }}']),
            $this->block('paragraph', ['text' => '{{ product_price_pretty }} · price in pounds, calculated by the node graph from price_cents.']),
        ]);

        $this->ensureGraph($route->id, $this->productGraph());
    }

    protected function productGraph(): array
    {
        return [
            'nodes' => [
                $this->node('src-sku',         'source.route_variable', ['variable_name' => 'sku'], 0, 280),
                $this->node('src-product',     'source.model_finder',   ['model_class' => 'App\\Models\\Product', 'finder_key' => 'sku', 'expose_fields' => true], 280, 100),
                $this->node('const-hundred',   'source.constant',       ['value' => '100'], 0, 480),

                // Math: price_cents / 100 → currency_format → £499.00
                $this->node('math-divide',     'transform.math',           ['op' => '/'], 720, 380),
                $this->node('fmt-currency',    'transform.currency_format',['currency' => 'GBP', 'locale' => 'en_GB', 'decimals' => 2], 920, 380),

                // name → uppercase for the hero heading
                $this->node('upper-name',      'transform.uppercase',      [], 720, 20),

                // Outputs
                $this->node('out-name',        'output', ['name' => 'product_name'],          1320, 100),
                $this->node('out-name-loud',   'output', ['name' => 'product_name_loud'],     1320,  20),
                $this->node('out-desc',        'output', ['name' => 'product_description'],   1320, 180),
                $this->node('out-image',       'output', ['name' => 'product_image'],         1320, 260),
                $this->node('out-price',       'output', ['name' => 'product_price_pretty'],  1320, 380),
            ],
            'edges' => [
                $this->edge('src-sku',       'value', 'src-product', 'key'),

                $this->edge('src-product',   'name',         'upper-name',   'text'),
                $this->edge('upper-name',    'value',        'out-name-loud','value'),

                $this->edge('src-product',   'name',         'out-name',     'value'),
                $this->edge('src-product',   'description',  'out-desc',     'value'),
                $this->edge('src-product',   'image_url',    'out-image',    'value'),

                // price_cents / 100 → currency format → output
                $this->edge('src-product',   'price_cents',  'math-divide',  'a'),
                $this->edge('const-hundred', 'value',        'math-divide',  'b'),
                $this->edge('math-divide',   'value',        'fmt-currency', 'value'),
                $this->edge('fmt-currency',  'value',        'out-price',    'value'),
            ],
        ];
    }

    // ─── /customers/{email} · split + concat ───────────────────────

    protected function ensureCustomersRoute(Variable $email): void
    {
        $route = RouteDefinition::firstOrCreate(
            ['name' => 'customers.show'],
            ['method' => 'GET', 'path_template' => '/customers/{email}', 'description' => 'Customer · split + first + concat through the Variables Modifier'],
        );
        if ($route->segments()->count() === 0) {
            RouteSegment::create(['route_id' => $route->id, 'position' => 0, 'kind' => 'literal',  'literal_value' => 'customers']);
            RouteSegment::create(['route_id' => $route->id, 'position' => 1, 'kind' => 'variable', 'variable_id'   => $email->id]);
        }

        $this->ensurePage($route->id, [
            $this->block('hero', [
                'heading'    => '{{ greeting }}!',
                'subheading' => 'Signed in as {{ customer_email }} · {{ customer_company_loud }}',
                'cta_label'  => '',
                'cta_href'   => '',
                'align'      => 'left',
            ]),
            $this->block('paragraph', ['text' => 'The greeting is built in the node graph · split the name on space, take the first word, concat with "Hi ".']),
            $this->block('paragraph', ['text' => 'internal_notes is a real column on Customer but deliberately NOT in the #[ExposeToModelFinder] allowlist · it cannot be wired into any block.']),
        ]);

        $this->ensureGraph($route->id, $this->customerGraph());
    }

    protected function customerGraph(): array
    {
        return [
            'nodes' => [
                $this->node('src-email',     'source.route_variable', ['variable_name' => 'email'], 0, 280),
                $this->node('src-customer',  'source.model_finder',   ['model_class' => 'App\\Models\\Customer', 'finder_key' => 'email', 'expose_fields' => true], 280, 100),
                $this->node('const-hi',      'source.constant',       ['value' => 'Hi '], 0, 540),

                // name → split " " → first → "Ada"
                $this->node('split-name',    'transform.split',  ['delimiter' => ' '], 720,  20),
                $this->node('first-name',    'transform.first',  [], 920, 20),
                // "Hi " + first name → greeting
                $this->node('concat-greet',  'transform.concat', ['separator' => ''], 1120, 540),
                // company → uppercase
                $this->node('upper-company', 'transform.uppercase', [], 720, 700),

                // Outputs
                $this->node('out-name',         'output', ['name' => 'customer_name'],          1320, 100),
                $this->node('out-greeting',     'output', ['name' => 'greeting'],               1320,  20),
                $this->node('out-email',        'output', ['name' => 'customer_email'],         1320, 260),
                $this->node('out-company',      'output', ['name' => 'customer_company'],       1320, 540),
                $this->node('out-company-loud', 'output', ['name' => 'customer_company_loud'],  1320, 700),
            ],
            'edges' => [
                $this->edge('src-email',     'value', 'src-customer', 'key'),

                $this->edge('src-customer',  'name',  'split-name',   'text'),
                $this->edge('split-name',    'value', 'first-name',   'array'),
                $this->edge('const-hi',      'value', 'concat-greet', 'a'),
                $this->edge('first-name',    'value', 'concat-greet', 'b'),
                $this->edge('concat-greet',  'value', 'out-greeting', 'value'),

                $this->edge('src-customer',  'name',    'out-name',    'value'),
                $this->edge('src-customer',  'email',   'out-email',   'value'),
                $this->edge('src-customer',  'company', 'out-company', 'value'),

                $this->edge('src-customer',  'company',     'upper-company',    'text'),
                $this->edge('upper-company', 'value',       'out-company-loud', 'value'),
            ],
        ];
    }

    // ─── /showcase/vintage · image pipeline ────────────────────────

    protected function ensureShowcaseRoute(): void
    {
        $route = RouteDefinition::firstOrCreate(
            ['name' => 'showcase.vintage'],
            ['method' => 'GET', 'path_template' => '/showcase/vintage', 'description' => 'Image pipeline · brightness + sepia + blur composed through the Variables Modifier'],
        );
        if ($route->segments()->count() === 0) {
            RouteSegment::create(['route_id' => $route->id, 'position' => 0, 'kind' => 'literal', 'literal_value' => 'showcase']);
            RouteSegment::create(['route_id' => $route->id, 'position' => 1, 'kind' => 'literal', 'literal_value' => 'vintage']);
        }

        $this->ensurePage($route->id, [
            $this->block('hero', [
                'heading'    => 'Image pipeline',
                'subheading' => 'Same source URL, four CSS-filter chains composed entirely in the node graph.',
                'cta_label'  => '',
                'cta_href'   => '',
                'align'      => 'left',
            ]),
            $this->block('columns', [], [
                'left' => [
                    $this->block('heading',   ['text' => 'Original',   'level' => 'h3', 'align' => 'left']),
                    $this->block('image',     ['src' => '{{ original }}', 'alt' => 'Original']),
                ],
                'right' => [
                    $this->block('heading',   ['text' => 'Sepia',  'level' => 'h3', 'align' => 'left']),
                    $this->block('image',     ['src' => '{{ sepia }}', 'alt' => 'Sepia-toned']),
                ],
            ]),
            $this->block('columns', [], [
                'left' => [
                    $this->block('heading',   ['text' => 'Vintage chain', 'level' => 'h3', 'align' => 'left']),
                    $this->block('paragraph', ['text' => 'Brightness 1.1 · sepia 0.7 · blur 0.5px']),
                    $this->block('image',     ['src' => '{{ vintage }}', 'alt' => 'Vintage filter chain']),
                ],
                'right' => [
                    $this->block('heading',   ['text' => 'Dramatic chain', 'level' => 'h3', 'align' => 'left']),
                    $this->block('paragraph', ['text' => 'Brightness 0.85 · contrast 1.3 · grayscale 1']),
                    $this->block('image',     ['src' => '{{ dramatic }}', 'alt' => 'Dramatic filter chain']),
                ],
            ]),
        ]);

        $this->ensureGraph($route->id, $this->showcaseGraph());
    }

    protected function showcaseGraph(): array
    {
        $url = 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=600&q=80';
        return [
            'nodes' => [
                // Single source feeds every branch.
                $this->node('img-src',     'image.source',     ['url' => $url], 0, 360),

                // Branch A: original (passthrough)
                $this->node('out-original','output',           ['name' => 'original'],  1320, 60),

                // Branch B: sepia only
                $this->node('sepia-only',  'image.sepia',      ['value' => '1.0'], 320, 220),
                $this->node('out-sepia',   'output',           ['name' => 'sepia'], 1320, 220),

                // Branch C: vintage chain · brightness → sepia → blur
                $this->node('vin-bright',  'image.brightness', ['value' => '1.1'], 320, 380),
                $this->node('vin-sepia',   'image.sepia',      ['value' => '0.7'], 520, 380),
                $this->node('vin-blur',    'image.blur',       ['value' => '0.5'], 720, 380),
                $this->node('out-vintage', 'output',           ['name' => 'vintage'], 1320, 380),

                // Branch D: dramatic chain · brightness ↓ → contrast ↑ → grayscale
                $this->node('dr-bright',   'image.brightness', ['value' => '0.85'], 320, 540),
                $this->node('dr-contrast', 'image.contrast',   ['value' => '1.3'],  520, 540),
                $this->node('dr-gray',     'image.grayscale',  ['value' => '1.0'],  720, 540),
                $this->node('out-dramatic','output',           ['name' => 'dramatic'], 1320, 540),
            ],
            'edges' => [
                // Original → output
                $this->edge('img-src', 'image', 'out-original', 'value'),

                // Sepia only
                $this->edge('img-src',    'image', 'sepia-only', 'image'),
                $this->edge('sepia-only', 'image', 'out-sepia',  'value'),

                // Vintage chain
                $this->edge('img-src',    'image', 'vin-bright', 'image'),
                $this->edge('vin-bright', 'image', 'vin-sepia',  'image'),
                $this->edge('vin-sepia',  'image', 'vin-blur',   'image'),
                $this->edge('vin-blur',   'image', 'out-vintage','value'),

                // Dramatic chain
                $this->edge('img-src',    'image', 'dr-bright',   'image'),
                $this->edge('dr-bright',  'image', 'dr-contrast', 'image'),
                $this->edge('dr-contrast','image', 'dr-gray',     'image'),
                $this->edge('dr-gray',    'image', 'out-dramatic','value'),
            ],
        ];
    }

    // ─── helpers ───────────────────────────────────────────────────

    protected function ensurePage(int $routeId, array $blocks): void
    {
        Page::updateOrCreate(
            ['route_id' => $routeId],
            ['blocks' => $blocks, 'status' => 'published'],
        );
    }

    protected function ensureGraph(int $routeId, array $graph): void
    {
        NodeGraph::updateOrCreate(
            ['route_id' => $routeId],
            ['nodes' => $graph['nodes'], 'edges' => $graph['edges']],
        );
    }

    protected function block(string $type, array $settings = [], array $children = []): array
    {
        return [
            'id'       => Str::random(8),
            'type'     => $type,
            'settings' => $settings,
            'children' => $children ?: new \stdClass(),
        ];
    }

    protected function node(string $id, string $type, array $settings, int $x, int $y): array
    {
        return [
            'id'       => $id,
            'type'     => $type,
            'settings' => $settings,
            'position' => ['x' => $x, 'y' => $y],
        ];
    }

    protected function edge(string $fromNode, string $fromSocket, string $toNode, string $toSocket): array
    {
        return [
            'id'         => Str::random(8),
            'from_node'  => $fromNode,
            'from_socket'=> $fromSocket,
            'to_node'    => $toNode,
            'to_socket'  => $toSocket,
        ];
    }
}
