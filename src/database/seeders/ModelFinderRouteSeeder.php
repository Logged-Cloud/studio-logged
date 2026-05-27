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
        $this->ensureSettingSocketsRoute();
        $this->ensureProceduralRoute();
    }

    // ─── /showcase/procedural · gradient / stripes / checkerboard / noise

    protected function ensureProceduralRoute(): void
    {
        $route = RouteDefinition::firstOrCreate(
            ['name' => 'showcase.procedural'],
            ['method' => 'GET', 'path_template' => '/showcase/procedural', 'description' => 'Procedural geometry-style image nodes · gradients, stripes, checkerboards, noise · all wirable.'],
        );
        if ($route->segments()->count() === 0) {
            RouteSegment::create(['route_id' => $route->id, 'position' => 0, 'kind' => 'literal', 'literal_value' => 'showcase']);
            RouteSegment::create(['route_id' => $route->id, 'position' => 1, 'kind' => 'literal', 'literal_value' => 'procedural']);
        }

        $this->ensurePage($route->id, [
            $this->block('hero', [
                'heading'    => 'Procedural images',
                'subheading' => 'Four generators · gradient, stripes, checkerboard, noise. Each emits an SVG data URI that the filter chain (brightness / hue-rotate / blur / etc.) composes on top of.',
                'cta_label'  => '', 'cta_href' => '', 'align' => 'left',
            ]),
            $this->block('columns', [], [
                'left' => [
                    $this->block('heading', ['text' => 'Gradient',  'level' => 'h3', 'align' => 'left']),
                    $this->block('image',   ['src' => '{{ proc_gradient }}', 'alt' => 'Linear gradient']),
                ],
                'right' => [
                    $this->block('heading', ['text' => 'Gradient → hue-rotate', 'level' => 'h3', 'align' => 'left']),
                    $this->block('image',   ['src' => '{{ proc_gradient_rot }}', 'alt' => 'Gradient with hue-rotate']),
                ],
            ]),
            $this->block('columns', [], [
                'left' => [
                    $this->block('heading', ['text' => 'Stripes',     'level' => 'h3', 'align' => 'left']),
                    $this->block('image',   ['src' => '{{ proc_stripes }}', 'alt' => 'Stripes pattern']),
                ],
                'right' => [
                    $this->block('heading', ['text' => 'Checkerboard', 'level' => 'h3', 'align' => 'left']),
                    $this->block('image',   ['src' => '{{ proc_check }}', 'alt' => 'Checkerboard pattern']),
                ],
            ]),
            $this->block('heading',   ['text' => 'Noise',  'level' => 'h3', 'align' => 'left']),
            $this->block('paragraph', ['text' => 'Deterministic fractal noise via SVG feTurbulence. Change the seed in the graph to reroll.']),
            $this->block('image',     ['src' => '{{ proc_noise }}', 'alt' => 'Fractal noise']),
        ]);

        $this->ensureGraph($route->id, $this->proceduralGraph());
    }

    protected function proceduralGraph(): array
    {
        return [
            'nodes' => [
                // Gradient (direct + rotated)
                $this->node('gr',       'image.gradient',   ['from' => '#2C66E8', 'to' => '#E11D48', 'angle' => 45,  'width' => 600, 'height' => 220], 0,   20),
                $this->node('out-gr',   'output',           ['name' => 'proc_gradient'],     720,   20),
                $this->node('gr-rot',   'image.hue_rotate', ['value' => 90],                 320,  140),
                $this->node('out-gr-r', 'output',           ['name' => 'proc_gradient_rot'], 720,  140),

                // Stripes (with a wired angle from a constant)
                $this->node('const-30',  'source.constant', ['value' => '30'],               0,   260),
                $this->node('st',       'image.stripes',    ['a' => '#2C66E8', 'b' => '#0E1116', 'width' => 28, 'angle' => 0, 'imgWidth' => 600, 'imgHeight' => 220], 320, 260),
                $this->node('out-st',   'output',           ['name' => 'proc_stripes'],      720,  260),

                // Checkerboard with two colors
                $this->node('ck',       'image.checkerboard', ['a' => '#16A34A', 'b' => '#0E1116', 'cell' => 24, 'imgWidth' => 600, 'imgHeight' => 220], 320, 380),
                $this->node('out-ck',   'output',           ['name' => 'proc_check'],        720,  380),

                // Noise
                $this->node('nz',       'image.noise',      ['seed' => 7, 'scale' => 0.65, 'octaves' => 3, 'imgWidth' => 600, 'imgHeight' => 220], 320, 500),
                $this->node('out-nz',   'output',           ['name' => 'proc_noise'],        720,  500),
            ],
            'edges' => [
                // gradient (passthrough)
                $this->edge('gr',       'image', 'out-gr',    'value'),

                // gradient → hue rotate (90deg)
                $this->edge('gr',       'image', 'gr-rot',    'image'),
                $this->edge('gr-rot',   'image', 'out-gr-r',  'value'),

                // stripes (angle wired from a Constant · 30deg)
                $this->edge('const-30', 'value', 'st',        'angle'),
                $this->edge('st',       'image', 'out-st',    'value'),

                // checkerboard + noise passthrough
                $this->edge('ck',       'image', 'out-ck',    'value'),
                $this->edge('nz',       'image', 'out-nz',    'value'),
            ],
        ];
    }

    // ─── /showcase/setting-sockets · selects + numbers wired into settings ──

    protected function ensureSettingSocketsRoute(): void
    {
        $route = RouteDefinition::firstOrCreate(
            ['name' => 'showcase.setting-sockets'],
            ['method' => 'GET', 'path_template' => '/showcase/setting-sockets', 'description' => 'Demo · every setting is a socket. Constants drive a Math node\'s op, a Format-Date format string, and an image filter amount.'],
        );
        if ($route->segments()->count() === 0) {
            RouteSegment::create(['route_id' => $route->id, 'position' => 0, 'kind' => 'literal', 'literal_value' => 'showcase']);
            RouteSegment::create(['route_id' => $route->id, 'position' => 1, 'kind' => 'literal', 'literal_value' => 'setting-sockets']);
        }

        $this->ensurePage($route->id, [
            $this->block('hero', [
                'heading'    => 'Setting sockets',
                'subheading' => 'Every settable field carries a wireable pip · selects, numbers, format strings, you name it. Three demos below · all the parameters come from upstream nodes, not the right-hand panel.',
                'cta_label'  => '',
                'cta_href'   => '',
                'align'      => 'left',
            ]),
            $this->block('heading',   ['text' => '1 · Math op wired from a constant', 'level' => 'h2', 'align' => 'left']),
            $this->block('paragraph', ['text' => '7 {{ op_glyph }} 3 = {{ math_result }} · the math node\'s `op` setting is a select dropdown, BUT the value is being driven by a Constant wired into the socket instead.']),

            $this->block('heading',   ['text' => '2 · Date format wired from a constant', 'level' => 'h2', 'align' => 'left']),
            $this->block('paragraph', ['text' => 'Today rendered as: {{ today_pretty }} · the format-date node\'s `format` setting is a plain text field, wired in from a Constant carrying "l, F j Y".']),

            $this->block('heading',   ['text' => '3 · Image filter amount wired from a route variable', 'level' => 'h2', 'align' => 'left']),
            $this->block('paragraph', ['text' => 'The same solid image, but the hue-rotate amount is a math result fed back into the filter\'s `value` setting. Reload to see different angles.']),
            $this->block('image',     ['src' => '{{ wired_filter_image }}', 'alt' => 'Solid image with wire-driven hue rotate']),
        ]);

        $this->ensureGraph($route->id, $this->settingSocketsGraph());
    }

    protected function settingSocketsGraph(): array
    {
        return [
            'nodes' => [
                // Demo 1 · wire a Constant into the math node's `op` select setting.
                $this->node('const-a',    'source.constant', ['value' => '7'],  0,    20),
                $this->node('const-b',    'source.constant', ['value' => '3'],  0,   100),
                $this->node('const-op',   'source.constant', ['value' => '*'],  0,   180),
                $this->node('const-glyph','source.constant', ['value' => '×'],  0,   260),
                $this->node('math',       'transform.math',  ['op' => '+'],     320,   60),  // static fallback = +, wire overrides to *
                $this->node('out-result', 'output',          ['name' => 'math_result'],  720,  60),
                $this->node('out-glyph',  'output',          ['name' => 'op_glyph'],     720, 260),

                // Demo 2 · wire a Constant into the format_date node's `format` text setting.
                $this->node('now',        'source.now',           [],                                                  0, 420),
                $this->node('const-fmt',  'source.constant',      ['value' => 'l, F j Y'],                             0, 500),
                $this->node('fmt-date',   'transform.format_date',['format' => 'Y-m-d', 'offset_amount' => 0, 'offset_unit' => 'days'], 320, 460),
                $this->node('out-today',  'output',               ['name' => 'today_pretty'],                          720, 460),

                // Demo 3 · math result wired into image.hue_rotate's `value` setting.
                // Plus a Color constant wired into image.solid's `color` setting.
                $this->node('const-30',   'source.constant',  ['value' => '30'],   0, 700),
                $this->node('const-4',    'source.constant',  ['value' => '4'],    0, 780),
                $this->node('mul',        'transform.math',   ['op' => '*'],       320, 740),
                $this->node('col',        'source.color',     ['color' => '#2C66E8'], 0, 880),
                $this->node('solid',      'image.solid',      ['color' => '#000000', 'width' => 600, 'height' => 220], 320, 880),
                $this->node('hue',        'image.hue_rotate', ['value' => '0'],    520, 740),
                $this->node('out-image',  'output',           ['name' => 'wired_filter_image'], 720, 740),
            ],
            'edges' => [
                // Demo 1
                $this->edge('const-a',     'value', 'math',       'a'),
                $this->edge('const-b',     'value', 'math',       'b'),
                $this->edge('const-op',    'value', 'math',       'op'),       // SELECT setting wired!
                $this->edge('math',        'value', 'out-result', 'value'),
                $this->edge('const-glyph', 'value', 'out-glyph',  'value'),

                // Demo 2
                $this->edge('now',         'value', 'fmt-date',   'value'),
                $this->edge('const-fmt',   'value', 'fmt-date',   'format'),   // TEXT setting wired!
                $this->edge('fmt-date',    'value', 'out-today',  'value'),

                // Demo 3
                $this->edge('const-30',    'value', 'mul',        'a'),
                $this->edge('const-4',     'value', 'mul',        'b'),
                $this->edge('col',         'color', 'solid',      'color'),    // COLOR setting wired!
                $this->edge('solid',       'image', 'hue',        'image'),
                $this->edge('mul',         'value', 'hue',        'value'),    // NUMBER setting wired!
                $this->edge('hue',         'image', 'out-image',  'value'),
            ],
        ];
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
            $this->block('divider', ['style' => 'solid']),
            $this->block('heading',   ['text' => 'Generative image nodes', 'level' => 'h2', 'align' => 'left']),
            $this->block('paragraph', ['text' => 'No source URL · just a color, a solid-image builder, and a hue-rotate whose degrees are wired in from a math node. 30 × 6 = 180°.']),
            $this->block('columns', [], [
                'left' => [
                    $this->block('heading',   ['text' => 'Solid → hue-rotate', 'level' => 'h3', 'align' => 'left']),
                    $this->block('paragraph', ['text' => 'source.color → image.solid → image.hue_rotate (degrees from math)']),
                    $this->block('image',     ['src' => '{{ hue_shifted }}', 'alt' => 'Color piped through solid + hue rotate']),
                ],
                'right' => [
                    $this->block('heading',   ['text' => 'Solid + opacity', 'level' => 'h3', 'align' => 'left']),
                    $this->block('paragraph', ['text' => 'source.color → image.solid → image.opacity 0.7']),
                    $this->block('image',     ['src' => '{{ cool_solid }}', 'alt' => 'Faded solid color']),
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
                // ── Real-image branches ──────────────────────────
                $this->node('img-src',     'image.source',     ['url' => $url], 0, 360),

                $this->node('out-original','output',           ['name' => 'original'],  1640, 60),

                // Sepia only
                $this->node('sepia-only',  'image.sepia',      ['value' => '1.0'], 320, 220),
                $this->node('out-sepia',   'output',           ['name' => 'sepia'], 1640, 220),

                // Vintage chain · brightness → sepia → blur
                $this->node('vin-bright',  'image.brightness', ['value' => '1.1'], 320, 380),
                $this->node('vin-sepia',   'image.sepia',      ['value' => '0.7'], 520, 380),
                $this->node('vin-blur',    'image.blur',       ['value' => '0.5'], 720, 380),
                $this->node('out-vintage', 'output',           ['name' => 'vintage'], 1640, 380),

                // Dramatic chain · brightness ↓ → contrast ↑ → grayscale
                $this->node('dr-bright',   'image.brightness', ['value' => '0.85'], 320, 540),
                $this->node('dr-contrast', 'image.contrast',   ['value' => '1.3'],  520, 540),
                $this->node('dr-gray',     'image.grayscale',  ['value' => '1.0'],  720, 540),
                $this->node('out-dramatic','output',           ['name' => 'dramatic'], 1640, 540),

                // ── NEW · color + solid-image + wire-driven hue ──
                // A color constant pipes into a solid-image builder,
                // whose output feeds a hue-rotate whose DEGREES are
                // also wired in from a math node · everything below
                // the line is the graph's own arithmetic.
                $this->node('col-red',     'source.color',     ['color' => '#e11d48'], 0, 760),
                $this->node('solid-img',   'image.solid',      ['color' => '#000000', 'width' => 600, 'height' => 220], 320, 760),

                // Two constants + a math op produce the rotation
                // angle dynamically · 30 * 6 = 180deg.
                $this->node('const-30',    'source.constant',  ['value' => '30'],  0, 900),
                $this->node('const-6',     'source.constant',  ['value' => '6'],   0, 1000),
                $this->node('math-mul',    'transform.math',   ['op' => '*'],     320, 940),
                $this->node('hue-shift',   'image.hue_rotate', ['value' => '0'],  720, 760),
                $this->node('out-shift',   'output',           ['name' => 'hue_shifted'], 1640, 760),

                // ── Bonus · gradient between two colors ─────────
                // image.solid renders only one color, but two solids
                // composed via opacity give a "fake gradient" feel ·
                // shows authors how to stack the same node twice.
                $this->node('col-blue',    'source.color',     ['color' => '#2563eb'], 0, 1160),
                $this->node('solid-blue',  'image.solid',      ['color' => '#000000', 'width' => 600, 'height' => 220], 320, 1160),
                $this->node('blue-faded',  'image.opacity',    ['value' => '0.7'],  520, 1160),
                $this->node('out-cool',    'output',           ['name' => 'cool_solid'], 1640, 1160),
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

                // NEW: color → solid → hue with wired degrees
                $this->edge('col-red',    'color', 'solid-img', 'color'),
                $this->edge('solid-img',  'image', 'hue-shift', 'image'),
                $this->edge('const-30',   'value', 'math-mul',  'a'),
                $this->edge('const-6',    'value', 'math-mul',  'b'),
                $this->edge('math-mul',   'value', 'hue-shift', 'value'),
                $this->edge('hue-shift',  'image', 'out-shift', 'value'),

                // Bonus: faded blue solid
                $this->edge('col-blue',   'color', 'solid-blue', 'color'),
                $this->edge('solid-blue', 'image', 'blue-faded', 'image'),
                $this->edge('blue-faded', 'image', 'out-cool',   'value'),
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
