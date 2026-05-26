<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Populates the three demo models the playground uses to exercise
 * the Model finder node · Article (find by slug), Product (find by
 * sku), Customer (find by email). Idempotent · runs upsert so a
 * re-seed during demos doesn't blow up on unique constraints.
 */
class ModelFinderDemoSeeder extends Seeder
{
    public function run(): void
    {
        Article::query()->upsert(
            [
                [
                    'slug'         => 'getting-started',
                    'title'        => 'Getting started with Studio',
                    'excerpt'      => 'A two-minute tour of the page builder, route variables, and the Model finder.',
                    'body'         => "Studio is the visual side of page-studio.\n\nStart by dropping a heading and a paragraph from the palette, then wire a route variable into them.",
                    'author_name'  => 'The Studio team',
                    'published_at' => now()->subDays(7),
                ],
                [
                    'slug'         => 'model-finder',
                    'title'        => 'Pulling rows in with the Model finder',
                    'excerpt'      => 'How #[ExposeToModelFinder] turns any Eloquent model into a page-builder variable source.',
                    'body'         => "Mark the model with #[ExposeToModelFinder], rebuild the discovery cache, and the FQCN dropdown picks it up.",
                    'author_name'  => 'The Studio team',
                    'published_at' => now()->subDays(2),
                ],
                [
                    'slug'         => 'collab',
                    'title'        => 'Real-time collab in 8 seconds',
                    'excerpt'      => 'Polling-only cross-tab sync · no Echo, no Reverb.',
                    'body'         => "Every 8 seconds the heartbeat polls for peer edits and merges them into the local tree.",
                    'author_name'  => 'The Studio team',
                    'published_at' => now()->subHours(12),
                ],
            ],
            ['slug'],
        );

        Product::query()->upsert(
            [
                [
                    'sku'         => 'STUDIO-PRO',
                    'name'        => 'Studio Pro licence',
                    'description' => 'Self-hosted page-studio licence for one Laravel app · includes the visual builder, node graph, and live collab.',
                    'price_cents' => 49900,
                    'currency'    => 'GBP',
                    'image_url'   => 'https://placehold.co/600x400/2C66E8/ffffff?text=Studio+Pro',
                ],
                [
                    'sku'         => 'STUDIO-TEAM',
                    'name'        => 'Studio Team licence',
                    'description' => 'Five-seat team licence with shared revisions and an audit log.',
                    'price_cents' => 199900,
                    'currency'    => 'GBP',
                    'image_url'   => 'https://placehold.co/600x400/16A34A/ffffff?text=Studio+Team',
                ],
                [
                    'sku'         => 'STUDIO-CLOUD',
                    'name'        => 'Studio Cloud add-on',
                    'description' => 'Optional managed presence + revision storage tier · stacks on any Studio licence.',
                    'price_cents' => 999,
                    'currency'    => 'GBP',
                    'image_url'   => 'https://placehold.co/600x400/F59E0B/ffffff?text=Studio+Cloud',
                ],
            ],
            ['sku'],
        );

        Customer::query()->upsert(
            [
                [
                    'name'            => 'Ada Lovelace',
                    'email'           => 'ada@example.com',
                    'company'         => 'Analytical Engines Ltd',
                    'internal_notes'  => 'High-touch · invoice manually.',
                ],
                [
                    'name'            => 'Grace Hopper',
                    'email'           => 'grace@example.com',
                    'company'         => 'Compiler Co',
                    'internal_notes'  => 'Prefers email · no phone.',
                ],
                [
                    'name'            => 'Alan Turing',
                    'email'           => 'alan@example.com',
                    'company'         => 'Bletchley Park',
                    'internal_notes'  => null,
                ],
            ],
            ['email'],
        );
    }
}
