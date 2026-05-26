<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LoggedCloud\PageStudio\Attributes\ExposeToModelFinder;

/**
 * Docs / blog example · classic finder use case is "look up the
 * article by its URL slug". Authors drop a Model finder node, pick
 * Article, set `findBy` to `slug`, and pipe the title + body into a
 * page template.
 */
#[ExposeToModelFinder(
    label:      'Article',
    findBy:     ['id', 'slug'],
    searchable: ['title', 'body'],
    expose:     ['id', 'slug', 'title', 'excerpt', 'body', 'author_name', 'published_at'],
)]
class Article extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }
}
