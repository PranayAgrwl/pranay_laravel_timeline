<?php

namespace Modules\People\Models\Labels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\People\Models\Concerns\HasAudit;
use Modules\People\Models\Relation;

/**
 * Lookup: relation types catalogue
 * (standalone, friend, colleague, parent, child, service, spouse).
 *
 * The `mirror` flag tells the Relation model whether saving a
 * relation of this type should auto-create the inverse. See
 * Modules\People\Models\Relation::booted() for the mapping rule.
 */
class RelationType extends Model
{
    use HasAudit;
    use SoftDeletes;

    protected $table = 'people_relation_types';
    protected $primaryKey = 'relation_type_id';

    protected $fillable = [
        'name',
        'mirror',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'mirror' => 'boolean',
    ];

    public function relations()
    {
        return $this->hasMany(Relation::class, 'relation_type_id', 'relation_type_id');
    }
}
