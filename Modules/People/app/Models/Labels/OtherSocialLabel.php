<?php

namespace Modules\People\Models\Labels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\People\Models\OtherSocial;
use Modules\People\Models\Concerns\HasAudit;

/**
 * Lookup: "other socials" channel names (Email, Instagram, Twitter,
 * WhatsApp, LinkedIn, etc.). Email lives here too per the design
 * decision (no separate emails table).
 */
class OtherSocialLabel extends Model
{
    use HasAudit;
    use SoftDeletes;

    protected $table = 'people_label_other_socials';
    protected $primaryKey = 'other_social_label_id';

    protected $fillable = [
        'name',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function otherSocials()
    {
        return $this->hasMany(OtherSocial::class, 'other_social_label_id', 'other_social_label_id');
    }
}
