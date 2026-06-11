<?php

namespace Modules\People\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\People\Models\Concerns\HasAudit;

/**
 * GroupLink  -  junction row for the contacts <-> groups relation.
 * Uniqueness of (group_id, contact_id) is enforced at the DB level.
 */
class GroupLink extends Model
{
    use HasAudit;
    use SoftDeletes;

    protected $table = 'people_group_links';
    protected $primaryKey = 'group_link_id';

    protected $fillable = [
        'group_id',
        'contact_id',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'group_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'contact_id');
    }
}
