<?php

namespace Modules\People\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\People\Models\Concerns\HasAudit;

/**
 * Group  -  hierarchical (tree-style) bucket of contacts.
 *
 * Backed by the table `people_group_names`. Root groups have
 * parent_group_id = NULL; descendants point at their immediate
 * parent. Membership is NOT inherited - if a contact is in a child
 * group they're NOT automatically counted in the parent. Add them
 * to both explicitly if you want both buckets to list them.
 */
class Group extends Model
{
    use HasAudit;
    use SoftDeletes;

    protected $table = 'people_group_names';
    protected $primaryKey = 'group_id';

    protected $fillable = [
        'name',
        'purpose',
        'parent_group_id',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function parent()
    {
        return $this->belongsTo(Group::class, 'parent_group_id', 'group_id');
    }

    public function children()
    {
        return $this->hasMany(Group::class, 'parent_group_id', 'group_id');
    }

    public function groupLinks()
    {
        return $this->hasMany(GroupLink::class, 'group_id', 'group_id');
    }

    public function contacts()
    {
        return $this->belongsToMany(
            Contact::class,
            'people_group_links',
            'group_id',
            'contact_id',
            'group_id',
            'contact_id'
        );
    }
}
