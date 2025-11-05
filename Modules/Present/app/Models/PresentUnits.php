<?php

namespace Modules\Present\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
// use Modules\Present\Database\Factories\PresentUnitsFactory;

class PresentUnits extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'unit_id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'notes0',
        'notes1',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $table = 'present_units';

    // protected static function newFactory(): PresentUnitsFactory
    // {
    //     // return PresentUnitsFactory::new();
    // }
}
