<?php

namespace Modules\People\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\People\Models\Concerns\HasAudit;

/**
 * Contact  -  the central People-module entity.
 *
 * Notable column:
 *   uuid  -  required by CardDAV (Step 3) as the vCard UID. Auto-
 *            generated on insert by Laravel's HasUuids trait. Never
 *            shown in the UI; purely for sync stability.
 *
 * Soft-delete is the proper removal path; FKs on every dependent
 * table are RESTRICT so a contact with phones/dates/etc. can't be
 * hard-deleted by accident.
 */
class Contact extends Model
{
    use HasAudit;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'people_contacts';
    protected $primaryKey = 'contact_id';

    protected $fillable = [
        'uuid',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * Tell HasUuids which columns are UUIDs. Without this it would
     * try to fill the auto-increment primary key with a UUID.
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    // ---------- relationships ----------

    public function nickNames()
    {
        return $this->hasMany(NickName::class, 'contact_id', 'contact_id');
    }

    public function groupLinks()
    {
        return $this->hasMany(GroupLink::class, 'contact_id', 'contact_id');
    }

    public function groups()
    {
        return $this->belongsToMany(
            Group::class,
            'people_group_links',
            'contact_id',
            'group_id',
            'contact_id',
            'group_id'
        );
    }

    public function relationsFrom()
    {
        return $this->hasMany(Relation::class, 'contact_from_id', 'contact_id');
    }

    public function relationsTo()
    {
        return $this->hasMany(Relation::class, 'contact_to_id', 'contact_id');
    }

    public function addressLinks()
    {
        return $this->hasMany(AddressLink::class, 'contact_id', 'contact_id');
    }

    public function addresses()
    {
        return $this->belongsToMany(
            Address::class,
            'people_address_link',
            'contact_id',
            'address_id',
            'contact_id',
            'address_id'
        );
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'contact_id', 'contact_id');
    }

    public function phones()
    {
        return $this->hasMany(Phone::class, 'contact_id', 'contact_id');
    }

    public function dates()
    {
        return $this->hasMany(ContactDate::class, 'contact_id', 'contact_id');
    }

    public function work()
    {
        return $this->hasMany(Work::class, 'contact_id', 'contact_id');
    }

    public function otherSocials()
    {
        return $this->hasMany(OtherSocial::class, 'contact_id', 'contact_id');
    }
}
