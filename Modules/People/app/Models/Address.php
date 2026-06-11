<?php

namespace Modules\People\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\People\Models\Concerns\HasAudit;

/**
 * Address  -  a specific dwelling/unit AT a location (e.g. "Flat 3A"
 * at a building location, "Suite 204" at an office complex).
 *
 *   type = 0  residence
 *   type = 1  work
 *   type = 2  other
 */
class Address extends Model
{
    use HasAudit;
    use SoftDeletes;

    public const TYPE_RESIDENCE = 0;
    public const TYPE_WORK      = 1;
    public const TYPE_OTHER     = 2;

    protected $table = 'people_address';
    protected $primaryKey = 'address_id';

    protected $fillable = [
        'loc_id',
        'name',
        'floor',
        'type',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'type' => 'integer',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class, 'loc_id', 'location_id');
    }

    public function addressLinks()
    {
        return $this->hasMany(AddressLink::class, 'address_id', 'address_id');
    }

    public function contacts()
    {
        return $this->belongsToMany(
            Contact::class,
            'people_address_link',
            'address_id',
            'contact_id',
            'address_id',
            'contact_id'
        );
    }

    public function work()
    {
        return $this->hasMany(Work::class, 'address_id', 'address_id');
    }
}
