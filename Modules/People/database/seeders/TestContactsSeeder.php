<?php

namespace Modules\People\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\People\Models\Contact;

/**
 * TestContactsSeeder
 *
 * Inserts two minimal contacts so that downstream steps (CardDAV sync
 * test, web UI prototype) have something to display. Idempotent - if
 * a row with the same first_name+last_name already exists it is
 * left alone and not duplicated.
 *
 * created_by/updated_by are filled with the first user-id in the
 * users table so the seeder works on a fresh install (where there
 * is no authenticated session for the HasAudit trait to draw from).
 */
class TestContactsSeeder extends Seeder
{
    public function run(): void
    {
        $userId = DB::table('users')->orderBy('id')->value('id');
        if ($userId === null) {
            $this->command?->warn('TestContactsSeeder skipped: no users in the users table. Run UserSeeder first.');

            return;
        }

        $rows = [
            [
                'first_name' => 'Test',
                'last_name'  => 'One',
                'gender'     => 'Male',
                'notes'      => 'Auto-created by TestContactsSeeder. Used to smoke-test the CardDAV sync in Step 3.',
            ],
            [
                'first_name' => 'Test',
                'last_name'  => 'Two',
                'gender'     => 'Female',
                'notes'      => 'Auto-created by TestContactsSeeder. Used to smoke-test the CardDAV sync in Step 3.',
            ],
        ];

        foreach ($rows as $data) {
            // Idempotent guard: skip if a contact with the same name pair already exists.
            $exists = Contact::where('first_name', $data['first_name'])
                ->where('last_name', $data['last_name'])
                ->exists();

            if ($exists) {
                $this->command?->line("  skip: {$data['first_name']} {$data['last_name']} already present");

                continue;
            }

            $contact = Contact::create(array_merge($data, [
                'created_by' => $userId,
                'updated_by' => $userId,
            ]));

            $this->command?->line("  created: {$contact->first_name} {$contact->last_name} (contact_id={$contact->contact_id}, uuid={$contact->uuid})");
        }
    }
}
