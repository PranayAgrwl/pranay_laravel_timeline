<?php

namespace Modules\People\Models\Labels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\People\Models\Concerns\HasAudit;
use Modules\People\Models\Phone;

/**
 * Lookup: phone-line label (Mobile, Home, Work, Fax, etc.).
 */
class PhoneLabel extends Model
{
    use HasAudit;
    use SoftDeletes;

    protected $table = 'people_label_phone';
    protected $primaryKey = 'phone_label_id';

    protected $fillable = [
        'name',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function phones()
    {
        return $this->hasMany(Phone::class, 'phone_label_id', 'phone_label_id');
    }
}
