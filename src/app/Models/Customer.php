<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LoggedCloud\PageStudio\Attributes\ExposeToModelFinder;

/**
 * Personalisation example · "find by email" is the canonical flow,
 * with company + name available downstream. `internal_notes` is
 * deliberately kept out of the expose list so a manually-wired edge
 * can't surface it.
 */
#[ExposeToModelFinder(
    label:      'Customer',
    findBy:     ['id', 'email'],
    searchable: ['name', 'email', 'company'],
    expose:     ['id', 'name', 'email', 'company', 'created_at'],
)]
class Customer extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
}
