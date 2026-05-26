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
 * Seeds three curated demo routes that show off the Model finder
 * with the new #[ExposeToModelFinder] attribute and its per-model
 * findBy / expose lists. Each route is a complete worked example ·
 * route variable → Model finder → block tree using the exposed
 * fields as {{ }} substitutions.
 *
 *   - /docs/{slug}           Article finder by slug
 *   - /products/{sku}        Product finder by sku
 *   - /customers/{email}     Customer finder by email
 *
 * Idempotent · routes are looked up by name so re-running the
 * seeder during a demo refresh doesn't duplicate.
 */
class ModelFinderRouteSeeder extends Seeder
{
    public function run(): void
    {
        $slug  = $this->ensureVariable('slug', 'slug',  ['examples' => ['getting-started', 'model-finder', 'collab']]);
        $sku   = $this->ensureVariable('sku',  'custom', ['examples' => ['STUDIO-PRO', 'STUDIO-TEAM', 'STUDIO-CLOUD'], 'regex' => '[A-Z0-9-]+']);
        $email = $this->ensureVariable('email','any',   ['examples' => ['ada@example.com', 'grace@example.com', 'alan@example.com']]);

        $this->ensureDocsRoute($slug);
        $this->ensureProductsRoute($sku);
        $this->ensureCustomersRoute($email);
    }

    protected function ensureVariable(string $name, string $type, array $extra = []): Variable
    {
        return Variable::firstOrCreate(
            ['name' => $name],
            array_merge(['type' => $type, 'examples' => $extra['examples'] ?? []], $extra),
        );
    }

    protected function ensureDocsRoute(Variable $slug): void
    {
        $route = RouteDefinition::firstOrCreate(
            ['name' => 'docs.show'],
            ['method' => 'GET', 'path_template' => '/docs/{slug}', 'description' => 'Article finder demo · find by slug'],
        );

        if ($route->segments()->count() === 0) {
            RouteSegment::create(['route_id' => $route->id, 'position' => 0, 'kind' => 'literal',  'literal_value' => 'docs']);
            RouteSegment::create(['route_id' => $route->id, 'position' => 1, 'kind' => 'variable', 'variable_id'   => $slug->id]);
        }

        $this->ensurePage($route->id, [
            $this->block('heading', ['text' => '{{ article_title }}', 'level' => 'h1', 'align' => 'left']),
            $this->block('paragraph', ['text' => '{{ article_excerpt }}']),
            $this->block('paragraph', ['text' => '{{ article_body }}']),
            $this->block('paragraph', ['text' => 'By {{ article_author }} · published {{ article_published }}']),
        ]);

        $this->ensureGraph($route->id, $this->articleGraph($slug));
    }

    protected function ensureProductsRoute(Variable $sku): void
    {
        $route = RouteDefinition::firstOrCreate(
            ['name' => 'products.show'],
            ['method' => 'GET', 'path_template' => '/products/{sku}', 'description' => 'Product finder demo · find by sku'],
        );

        if ($route->segments()->count() === 0) {
            RouteSegment::create(['route_id' => $route->id, 'position' => 0, 'kind' => 'literal',  'literal_value' => 'products']);
            RouteSegment::create(['route_id' => $route->id, 'position' => 1, 'kind' => 'variable', 'variable_id'   => $sku->id]);
        }

        $this->ensurePage($route->id, [
            $this->block('heading', ['text' => '{{ product_name }}', 'level' => 'h1', 'align' => 'left']),
            $this->block('image', ['src' => '{{ product_image }}', 'alt' => '{{ product_name }}']),
            $this->block('paragraph', ['text' => '{{ product_description }}']),
            $this->block('paragraph', ['text' => '{{ product_currency }} {{ product_price_cents }} (in pence)']),
        ]);

        $this->ensureGraph($route->id, $this->productGraph($sku));
    }

    protected function ensureCustomersRoute(Variable $email): void
    {
        $route = RouteDefinition::firstOrCreate(
            ['name' => 'customers.show'],
            ['method' => 'GET', 'path_template' => '/customers/{email}', 'description' => 'Customer finder demo · find by email · internal_notes deliberately not exposed'],
        );

        if ($route->segments()->count() === 0) {
            RouteSegment::create(['route_id' => $route->id, 'position' => 0, 'kind' => 'literal',  'literal_value' => 'customers']);
            RouteSegment::create(['route_id' => $route->id, 'position' => 1, 'kind' => 'variable', 'variable_id'   => $email->id]);
        }

        $this->ensurePage($route->id, [
            $this->block('heading', ['text' => 'Hi {{ customer_name }}!', 'level' => 'h1', 'align' => 'left']),
            $this->block('paragraph', ['text' => 'You\'re signed in as {{ customer_email }} · {{ customer_company }}.']),
            $this->block('paragraph', ['text' => 'Note · internal_notes is a real column on this model but is NOT exposed via the attribute, so it cannot be wired into a block.']),
        ]);

        $this->ensureGraph($route->id, $this->customerGraph($email));
    }

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

    /**
     * Build the Article graph · route var slug → Model finder
     * (Article, findBy slug, expose fields) → per-column outputs
     * mapped onto block-friendly variable names.
     */
    protected function articleGraph(Variable $slug): array
    {
        $routeNodeId  = 'src-slug';
        $finderNodeId = 'src-article';
        $titleOutId   = 'out-article-title';
        $excerptOutId = 'out-article-excerpt';
        $bodyOutId    = 'out-article-body';
        $authorOutId  = 'out-article-author';
        $publishedOut = 'out-article-published';

        return [
            'nodes' => [
                $this->node($routeNodeId,  'source.route_variable', ['variable_name' => 'slug'], 0,   180),
                $this->node($finderNodeId, 'source.model_finder',   ['model_class' => 'App\\Models\\Article', 'finder_key' => 'slug', 'expose_fields' => true], 320, 80),
                $this->node($titleOutId,    'output', ['name' => 'article_title'],     720,  20),
                $this->node($excerptOutId,  'output', ['name' => 'article_excerpt'],   720, 100),
                $this->node($bodyOutId,     'output', ['name' => 'article_body'],      720, 180),
                $this->node($authorOutId,   'output', ['name' => 'article_author'],    720, 260),
                $this->node($publishedOut,  'output', ['name' => 'article_published'], 720, 340),
            ],
            'edges' => [
                $this->edge($routeNodeId,  'value', $finderNodeId, 'key'),
                $this->edge($finderNodeId, 'title',         $titleOutId,    'value'),
                $this->edge($finderNodeId, 'excerpt',       $excerptOutId,  'value'),
                $this->edge($finderNodeId, 'body',          $bodyOutId,     'value'),
                $this->edge($finderNodeId, 'author_name',   $authorOutId,   'value'),
                $this->edge($finderNodeId, 'published_at',  $publishedOut,  'value'),
            ],
        ];
    }

    protected function productGraph(Variable $sku): array
    {
        return [
            'nodes' => [
                $this->node('src-sku',     'source.route_variable', ['variable_name' => 'sku'], 0, 180),
                $this->node('src-product', 'source.model_finder',   ['model_class' => 'App\\Models\\Product', 'finder_key' => 'sku', 'expose_fields' => true], 320, 80),
                $this->node('out-name',     'output', ['name' => 'product_name'],         720,  20),
                $this->node('out-desc',     'output', ['name' => 'product_description'],  720, 100),
                $this->node('out-price',    'output', ['name' => 'product_price_cents'],  720, 180),
                $this->node('out-currency', 'output', ['name' => 'product_currency'],     720, 260),
                $this->node('out-image',    'output', ['name' => 'product_image'],        720, 340),
            ],
            'edges' => [
                $this->edge('src-sku',     'value', 'src-product', 'key'),
                $this->edge('src-product', 'name',         'out-name',     'value'),
                $this->edge('src-product', 'description',  'out-desc',     'value'),
                $this->edge('src-product', 'price_cents',  'out-price',    'value'),
                $this->edge('src-product', 'currency',     'out-currency', 'value'),
                $this->edge('src-product', 'image_url',    'out-image',    'value'),
            ],
        ];
    }

    protected function customerGraph(Variable $email): array
    {
        return [
            'nodes' => [
                $this->node('src-email',    'source.route_variable', ['variable_name' => 'email'], 0, 180),
                $this->node('src-customer', 'source.model_finder',   ['model_class' => 'App\\Models\\Customer', 'finder_key' => 'email', 'expose_fields' => true], 320, 80),
                $this->node('out-name',    'output', ['name' => 'customer_name'],    720,  20),
                $this->node('out-email',   'output', ['name' => 'customer_email'],   720, 100),
                $this->node('out-company', 'output', ['name' => 'customer_company'], 720, 180),
            ],
            'edges' => [
                $this->edge('src-email',    'value', 'src-customer', 'key'),
                $this->edge('src-customer', 'name',    'out-name',    'value'),
                $this->edge('src-customer', 'email',   'out-email',   'value'),
                $this->edge('src-customer', 'company', 'out-company', 'value'),
            ],
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
