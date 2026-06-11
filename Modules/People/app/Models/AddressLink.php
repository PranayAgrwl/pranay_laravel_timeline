<?php

namespace Modules\People\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\People\Models\Concerns\HasAudit;

/**
 * AddressLink  -  junction row for contact <-> address.
 * Uniqueness of (address_id, contact_id) is enforced at the DB level.
 */
class AddressLink extends Model
{
    use HasAudit;
    use SoftDeletes;

    protected $table = 'people_address_link';
    protected $primaryKey = 'address_link_id';

    protected $fillable = [
        'address_id',
        'contact_id',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id', 'address_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'contact_id');
    }
}
