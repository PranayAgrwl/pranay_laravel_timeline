<?php

namespace Modules\People\Models\Labels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\People\Models\ContactDate;
use Modules\People\Models\Concerns\HasAudit;

/**
 * Lookup: date kinds (Birthday, Anniversary, etc.).
 */
class DateLabel extends Model
{
    use HasAudit;
    use SoftDeletes;

    protected $table = 'people_label_dates';
    protected $primaryKey = 'label_date_id';

    protected $fillable = [
        'name',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function contactDates()
    {
        return $this->hasMany(ContactDate::class, 'label_date_id', 'label_date_id');
    }
}
