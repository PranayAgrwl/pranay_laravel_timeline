<?php

namespace Modules\People\Models\Labels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\People\Models\ContactDate;
use Modules\People\Models\Concerns\HasAudit;

/**
 * Lookup: reminder presets - couples a label ("Cake Cutting", "Gift
 * Getting") with the number of hours in advance you want the
 * notification ("4", "168").
 *
 * The actual scheduling system isn't built yet; this table just
 * stores the intent so future code has something to schedule against.
 */
class ReminderLabel extends Model
{
    use HasAudit;
    use SoftDeletes;

    protected $table = 'people_label_reminders';
    protected $primaryKey = 'reminder_id';

    protected $fillable = [
        'reminder_name',
        'hours_prior',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'hours_prior' => 'integer',
    ];

    public function contactDates()
    {
        return $this->hasMany(ContactDate::class, 'reminder_id', 'reminder_id');
    }
}
