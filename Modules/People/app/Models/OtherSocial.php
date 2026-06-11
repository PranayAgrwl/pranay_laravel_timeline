<?php

namespace Modules\People\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\People\Models\Concerns\HasAudit;
use Modules\People\Models\Labels\OtherSocialLabel;

/**
 * OtherSocial  -  a miscellaneous social presence per contact
 * (email address, Instagram handle, Twitter URL, LinkedIn, etc.).
 *
 * `link` is TEXT so it can hold a full URL or a short handle.
 */
class OtherSocial extends Model
{
    use HasAudit;
    use SoftDeletes;

    protected $table = 'people_other_socials';
    protected $primaryKey = 'other_social_id';

    protected $fillable = [
        'other_social_label_id',
        'contact_id',
        'link',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function label()
    {
        return $this->belongsTo(OtherSocialLabel::class, 'other_social_label_id', 'other_social_label_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'contact_id');
    }
}
