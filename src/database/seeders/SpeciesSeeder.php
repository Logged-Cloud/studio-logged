<?php

namespace Database\Seeders;

use App\Models\Species;
use Illuminate\Database\Seeder;

class SpeciesSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['Ball Python',         'Python regius',           14, 'mouse'],
            ['Corn Snake',          'Pantherophis guttatus',    7, 'mouse'],
            ['California Kingsnake','Lampropeltis californiae', 7, 'mouse'],
            ['Western Hognose',     'Heterodon nasicus',        7, 'mouse'],
            ['Brazilian Rainbow Boa','Epicrates cenchria',     10, 'rat'],
            ['Boa Constrictor',     'Boa constrictor',         14, 'rat'],
            ['Carpet Python',       'Morelia spilota',         14, 'rat'],
            ['Garter Snake',        'Thamnophis sirtalis',      5, 'other'],
            ['Milk Snake',          'Lampropeltis triangulum',  7, 'mouse'],
            ['Rosy Boa',            'Lichanura trivirgata',    10, 'mouse'],
            ['Children\'s Python',  'Antaresia childreni',     10, 'mouse'],
            ['Reticulated Python',  'Malayopython reticulatus', 14, 'rat'],
        ];

        foreach ($rows as [$common, $scientific, $interval, $prey]) {
            Species::updateOrCreate(
                ['common_name' => $common],
                [
                    'scientific_name' => $scientific,
                    'default_feeding_interval_days' => $interval,
                    'typical_prey' => $prey,
                    'active' => true,
                ],
            );
        }
    }
}
