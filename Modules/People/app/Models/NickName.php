<?php

namespace Modules\People\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\People\Models\Concerns\HasAudit;

/**
 * NickName  -  one-to-many on contact (Pranu, PA, Boss, ...).
 */
class NickName extends Model
{
    use HasAudit;
    use SoftDeletes;

    protected $table = 'people_nick_names';
    protected $primaryKey = 'nick_name_id';

    protected $fillable = [
        'contact_id',
        'nick_name',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'contact_id');
    }
}
