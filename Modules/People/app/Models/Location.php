<?php

namespace Modules\People\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\People\Models\Concerns\HasAudit;

/**
 * Location  -  reusable geographic place (a building, a complex, a
 * landmark). Multiple Address rows can sit at the same location.
 */
class Location extends Model
{
    use HasAudit;
    use SoftDeletes;

    protected $table = 'people_locations';
    protected $primaryKey = 'location_id';

    protected $fillable = [
        'name',
        'street',
        'area',
        'near',
        'pin_code',
        'city',
        'province',
        'country',
        'lat',
        'lon',
        'g_maps_link',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'lat' => 'decimal:7',
        'lon' => 'decimal:7',
    ];

    public function addresses()
    {
        return $this->hasMany(Address::class, 'loc_id', 'location_id');
    }
}
