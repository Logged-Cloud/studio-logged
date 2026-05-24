<?php

namespace Database\Seeders;

use App\Models\PreyType;
use Illuminate\Database\Seeder;

class PreyTypeSeeder extends Seeder
{
    /**
     * Seeded entries match the keys already stored on feeding events,
     * snakes.prey_type and species.typical_prey from before the table
     * existed. icon_svg holds a path `d` string drawn at 24x24 with
     * stroke=currentColor; admins can edit or extend the set.
     */
    public function run(): void
    {
        $rows = [
            [
                'key' => 'mouse',
                'name' => 'Mouse',
                'subtitle' => 'Pinkie · Fuzzy · Hopper · Adult',
                'icon_svg' => 'M9 14m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0 M5.5 10m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0 M12.5 10m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0 M11.5 14.5L17 18',
                'sort_order' => 10,
            ],
            [
                'key' => 'rat',
                'name' => 'Rat',
                'subtitle' => 'Pup · Weaner · Small · Medium · Large · Jumbo',
                'icon_svg' => 'M4 14.5a5 3.5 0 1 0 10 0a5 3.5 0 1 0 -10 0 M5.5 11.5m-1.5 0a1.5 1.5 0 1 0 3 0a1.5 1.5 0 1 0 -3 0 M11.5 11m-1.5 0a1.5 1.5 0 1 0 3 0a1.5 1.5 0 1 0 -3 0 M14 15.5C16 16 19 17 21 19.5',
                'sort_order' => 20,
            ],
            [
                'key' => 'chick',
                'name' => 'Chick',
                'subtitle' => 'Day-old · Older',
                'icon_svg' => 'M12 13m-5.5 0a5.5 5.5 0 1 0 11 0a5.5 5.5 0 1 0 -11 0 M10.5 11m-.7 0a.7 .7 0 1 0 1.4 0a.7 .7 0 1 0 -1.4 0 M17.5 12.5l2 .5l-2 .8z M9 19l-1 3 M15 19l1 3',
                'sort_order' => 30,
            ],
            [
                'key' => 'gerbil',
                'name' => 'Gerbil',
                'subtitle' => 'Hognose / picky-feeder fallback',
                'icon_svg' => 'M11 14m-4 0a4 3.5 0 1 0 8 0a4 3.5 0 1 0 -8 0 M8 10.5m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0 M14.5 14.5C17 15 20 16.5 21.5 19',
                'sort_order' => 40,
            ],
            [
                'key' => 'asf',
                'name' => 'African soft-furred rat',
                'subtitle' => 'ASF · scent-aggressive prey',
                'icon_svg' => 'M4 14.5a5 3.5 0 1 0 10 0a5 3.5 0 1 0 -10 0 M5 11m-1.8 0a1.8 1.8 0 1 0 3.6 0a1.8 1.8 0 1 0 -3.6 0 M12 11m-1.8 0a1.8 1.8 0 1 0 3.6 0a1.8 1.8 0 1 0 -3.6 0 M14 15.5C16 16 19 17 21 19.5 M3 8l1.5 1.5 M15 7l-1 1.5',
                'sort_order' => 50,
            ],
            [
                'key' => 'fish',
                'name' => 'Fish',
                'subtitle' => 'Garter / water snake',
                'icon_svg' => 'M3 12c3-5 9-5 13-2 1 .8 2 2 3 2-1 0-2 1.2-3 2-4 3-10 3-13-2z M7 11m-.7 0a.7 .7 0 1 0 1.4 0a.7 .7 0 1 0 -1.4 0 M19 9l3-3v12l-3-3',
                'sort_order' => 60,
            ],
            [
                'key' => 'earthworm',
                'name' => 'Earthworm',
                'subtitle' => 'Garter / juveniles',
                'icon_svg' => 'M3 17c2-3 5-3 7 0s5 3 7 0 4-3 4 0 M20.5 16.5m-.7 0a.7 .7 0 1 0 1.4 0a.7 .7 0 1 0 -1.4 0',
                'sort_order' => 70,
            ],
            [
                'key' => 'frog',
                'name' => 'Frog',
                'subtitle' => 'Hognose / amphibian-eaters',
                'icon_svg' => 'M4 15c0-4 4-7 8-7s8 3 8 7c0 3-3 5-8 5s-8-2-8-5z M8 11m-1.4 0a1.4 1.4 0 1 0 2.8 0a1.4 1.4 0 1 0 -2.8 0 M16 11m-1.4 0a1.4 1.4 0 1 0 2.8 0a1.4 1.4 0 1 0 -2.8 0 M9 16q3 2 6 0',
                'sort_order' => 80,
            ],
            [
                'key' => 'slug',
                'name' => 'Slug',
                'subtitle' => 'Garter / amphibian-eaters',
                'icon_svg' => 'M3 17c4-5 11-5 16-2 1 1 2 2 2 3H3z M19 13l3-3v3z M6 16m-.5 0a.5 .5 0 1 0 1 0a.5 .5 0 1 0 -1 0',
                'sort_order' => 90,
            ],
            [
                'key' => 'lizard',
                'name' => 'Lizard',
                'subtitle' => 'Anole-eaters / juveniles',
                'icon_svg' => 'M3 14q4-3 8-1q2 1 4 1q3 0 5-3 M11 14l-1 4 M14 14.5l3 3 M7 12m-.6 0a.6 .6 0 1 0 1.2 0a.6 .6 0 1 0 -1.2 0',
                'sort_order' => 95,
            ],
            [
                'key' => 'other',
                'name' => 'Other',
                'subtitle' => 'Not in the list',
                'icon_svg' => 'M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0 M9.5 9.5a2.5 2.5 0 0 1 5 0c0 1.5-2.5 2-2.5 4 M12 17.5h.01',
                'sort_order' => 999,
            ],
        ];

        foreach ($rows as $row) {
            PreyType::updateOrCreate(['key' => $row['key']], $row + ['active' => true]);
        }
    }
}
