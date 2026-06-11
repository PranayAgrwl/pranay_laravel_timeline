<?php

namespace Modules\People\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\People\Models\Concerns\HasAudit;
use Modules\People\Models\Labels\DateLabel;
use Modules\People\Models\Labels\ReminderLabel;

/**
 * ContactDate  -  a significant date associated with a contact
 * (birthday, anniversary, joining-date, etc.).
 *
 * Named ContactDate (not Date) to avoid name collision with PHP's
 * common "Date" semantics across libraries; the underlying table is
 * still people_dates.
 *
 *   recurrence = false  one-shot date (a specific year's event)
 *   recurrence = true   recurs annually on the same month-day
 */
class ContactDate extends Model
{
    use HasAudit;
    use SoftDeletes;

    protected $table = 'people_dates';
    protected $primaryKey = 'date_id';

    protected $fillable = [
        'contact_id',
        'label_date_id',
        'date',
        'time',
        'reminder_id',
        'recurrence',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'date'       => 'date',
        'recurrence' => 'boolean',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'contact_id');
    }

    public function label()
    {
        return $this->belongsTo(DateLabel::class, 'label_date_id', 'label_date_id');
    }

    public function reminder()
    {
        return $this->belongsTo(ReminderLabel::class, 'reminder_id', 'reminder_id');
    }
}
