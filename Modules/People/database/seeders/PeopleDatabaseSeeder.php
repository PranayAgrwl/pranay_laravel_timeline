<?php

namespace Modules\People\Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * PeopleDatabaseSeeder
 *
 * Master seeder for the People module. Runs every sub-seeder in
 * dependency order. Lookup tables (relation_types, label_phone,
 * country_codes, etc.) are intentionally NOT seeded - they're
 * populated via the UI/SQL by the user.
 */
class PeopleDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TestContactsSeeder::class,
        ]);
    }
}
