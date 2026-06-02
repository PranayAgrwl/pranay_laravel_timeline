<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Module: Present  |  Table: present_logs
 *
 * WHY THIS MIGRATION
 * -------------------
 * The Logs feature is moving from a 2-state outcome model (yes / no, with "no"
 * doubling as the default-unrecorded value) to a clearer 3-persisted-state model:
 *
 *      yes          - habit was completed
 *      no           - habit was attempted but not completed
 *      not_possible - habit could not be performed for legitimate reasons
 *
 * The fourth UI state, "not done", is intentionally NOT a database value. It is
 * a pure placeholder that means "no log row exists for this habit + date + user".
 * The unique constraint (habit_id, log_date, created_by) guarantees one row max,
 * so the absence of a row unambiguously represents "not done".
 *
 * We also drop the previous `DEFAULT 'no'` because every persisted row must now
 * carry an explicit outcome chosen by the user; defaulting to 'no' would
 * silently corrupt data if any future code path forgets to set the column.
 *
 * Raw ALTER is used because Doctrine DBAL handles MySQL enums unreliably.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE present_logs MODIFY COLUMN outcome ENUM('yes','no','not_possible') NOT NULL");
    }

    public function down(): void
    {
        // Any 'not_possible' rows must be coerced into the smaller value-set
        // before MySQL will accept the narrower ENUM definition, otherwise the
        // ALTER fails on truncated data. We map them to 'no' (the closest
        // semantic neighbour in the old binary world).
        DB::table('present_logs')
            ->where('outcome', 'not_possible')
            ->update(['outcome' => 'no']);

        DB::statement("ALTER TABLE present_logs MODIFY COLUMN outcome ENUM('yes','no') NOT NULL DEFAULT 'no'");
    }
};
