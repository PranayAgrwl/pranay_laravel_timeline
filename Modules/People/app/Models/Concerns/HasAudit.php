<?php

namespace Modules\People\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * HasAudit
 *
 * Auto-fills the 3 audit user-columns on every save / delete event:
 *   created_by   on inserting    -> set to current logged-in user
 *   updated_by   on updating      -> set to current logged-in user
 *   deleted_by   on (soft)deleting -> set to current logged-in user
 *
 * The matching timestamps (created_at, updated_at, deleted_at) are
 * already handled by Laravel's standard $timestamps and SoftDeletes
 * behaviour - we only fill the user-id columns here.
 *
 * If no user is authenticated at the time of the event (e.g. console
 * seeding, scheduled jobs) we silently skip - the migrations declare
 * `created_by` as NOT NULL so seeders / artisan must explicitly set
 * it; this avoids silently storing NULL where we promised a value.
 *
 * Used by every People-module model via:
 *     use HasAudit;
 */
trait HasAudit
{
    public static function bootHasAudit(): void
    {
        static::creating(function (Model $model) {
            if (Auth::check()) {
                if (empty($model->created_by)) {
                    $model->created_by = Auth::id();
                }
                if (empty($model->updated_by)) {
                    $model->updated_by = Auth::id();
                }
            }
        });

        static::updating(function (Model $model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });

        static::deleting(function (Model $model) {
            if (Auth::check() && in_array('deleted_by', $model->getFillable(), true)) {
                $model->deleted_by = Auth::id();
                // For soft-deletes we must persist deleted_by BEFORE the
                // soft-delete flag is set, otherwise Eloquent's atomic
                // update of deleted_at would skip our column change.
                $model->saveQuietly();
            }
        });
    }
}
