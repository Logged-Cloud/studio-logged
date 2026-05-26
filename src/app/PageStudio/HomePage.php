<?php

namespace App\PageStudio;

use Illuminate\Support\Facades\Cache;
use LoggedCloud\PageStudio\Models\Page;

/**
 * Single-page accessor for the dogfooded home page · the / route renders
 * THIS Page through PageRenderer. The id is cached so we don't query for
 * the same row on every landing hit; cache-miss falls back to a seed-
 * or-find against the meta blob.
 */
class HomePage
{
    protected const CACHE_KEY = 'studio.home_page_id';

    public static function get(): Page
    {
        $id = Cache::get(self::CACHE_KEY);
        if ($id && ($page = Page::find($id))) return $page;

        // No cache, no luck. Find the existing row by the sentinel meta key
        // or create one with the curated home template.
        $page = Page::query()
            ->where('meta->kind', 'home')
            ->first()
            ?? Page::create([
                'blocks' => DemoTemplates::home(),
                'meta'   => ['kind' => 'home'],
                'status' => 'published',
            ]);

        Cache::forever(self::CACHE_KEY, $page->id);
        return $page;
    }

    /**
     * Force a re-seed · used by the studio:reset-home command when the
     * curated template changes and we want the live home page to pick it
     * up without a full redeploy.
     */
    public static function reseed(): Page
    {
        Cache::forget(self::CACHE_KEY);
        $existing = Page::query()
            ->where('meta->kind', 'home')
            ->first();
        if ($existing) {
            $existing->update(['blocks' => DemoTemplates::home(), 'status' => 'published']);
            Cache::forever(self::CACHE_KEY, $existing->id);
            return $existing;
        }
        return self::get();
    }
}
