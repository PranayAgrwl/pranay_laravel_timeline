<?php

namespace Modules\People\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\People\Models\Concerns\HasAudit;

/**
 * Work  -  employment record (one row per job).
 *
 *   address_id  nullable  some jobs are remote or address-unknown.
 *   ended_on    nullable  NULL = "currently employed here".
 */
class Work extends Model
{
    use HasAudit;
    use SoftDeletes;

    protected $table = 'people_work';
    protected $primaryKey = 'work_id';

    protected $fillable = [
        'contact_id',
        'address_id',
        'started_on',
        'ended_on',
        'post',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'started_on' => 'date',
        'ended_on'   => 'date',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'contact_id');
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id', 'address_id');
    }
}
