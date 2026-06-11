<?php

namespace Modules\People\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\People\Models\Concerns\HasAudit;

/**
 * AddressBook  -  the CardDAV container that holds contacts.
 *
 * For the single-user setup there is only ever one row in this table
 * (seeded by the migration as "My Contacts"). Step 3 (sabre/dav) will
 * read this row when exposing the CardDAV collection to DAVx5.
 *
 * sync_token is bumped on every contact insert/update/delete (logic
 * lives in the Contact model). It powers incremental sync.
 */
class AddressBook extends Model
{
    use HasAudit;
    use SoftDeletes;

    protected $table = 'people_addressbooks';
    protected $primaryKey = 'addressbook_id';

    protected $fillable = [
        'uri',
        'displayname',
        'description',
        'sync_token',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'sync_token' => 'integer',
    ];
}
