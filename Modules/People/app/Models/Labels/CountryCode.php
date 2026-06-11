<?php

namespace Modules\People\Models\Labels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\People\Models\Concerns\HasAudit;
use Modules\People\Models\Phone;

/**
 * Lookup: international dial codes (+91 India, +1 USA, etc.).
 * VARCHAR(7) accommodates leading "+" and rare hyphenated codes.
 */
class CountryCode extends Model
{
    use HasAudit;
    use SoftDeletes;

    protected $table = 'people_country_codes';
    protected $primaryKey = 'country_id';

    protected $fillable = [
        'country_code',
        'name',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function phones()
    {
        return $this->hasMany(Phone::class, 'country_id', 'country_id');
    }
}
