<?php

namespace Modules\People\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\People\Models\Concerns\HasAudit;
use Modules\People\Models\Labels\CountryCode;
use Modules\People\Models\Labels\PhoneLabel;
use Modules\People\Models\Labels\SimType;

/**
 * Phone  -  a phone number for a contact, with label (Mobile / Home /
 * Work / ...), country code, and optionally the SIM it lives on.
 *
 * `phone` is VARCHAR to preserve leading zeros, spaces, and dashes.
 *
 * Uniqueness of (contact_id, phone, country_id) is enforced at the
 * DB level so the same number can't appear twice on the same contact.
 */
class Phone extends Model
{
    use HasAudit;
    use SoftDeletes;

    protected $table = 'people_phones';
    protected $primaryKey = 'phone_id';

    protected $fillable = [
        'contact_id',
        'phone_label_id',
        'country_id',
        'phone',
        'sim_id',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'contact_id');
    }

    public function label()
    {
        return $this->belongsTo(PhoneLabel::class, 'phone_label_id', 'phone_label_id');
    }

    public function country()
    {
        return $this->belongsTo(CountryCode::class, 'country_id', 'country_id');
    }

    public function sim()
    {
        return $this->belongsTo(SimType::class, 'sim_id', 'sim_id');
    }
}
