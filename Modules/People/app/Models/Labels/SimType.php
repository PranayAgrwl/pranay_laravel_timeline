<?php

namespace Modules\People\Models\Labels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\People\Models\Concerns\HasAudit;
use Modules\People\Models\Phone;

/**
 * Lookup: your physical SIMs (typically Work, Personal).
 *
 * A phone row may reference one of these via phones.sim_id to
 * indicate the SIM the contact's number is associated with.
 */
class SimType extends Model
{
    use HasAudit;
    use SoftDeletes;

    protected $table = 'people_sim_types';
    protected $primaryKey = 'sim_id';

    protected $fillable = [
        'sim_name',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function phones()
    {
        return $this->hasMany(Phone::class, 'sim_id', 'sim_id');
    }
}
