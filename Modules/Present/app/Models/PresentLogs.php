<?php

namespace Modules\Present\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Modules\Present\Database\Factories\PresentLogsFactory;

class PresentLogs extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'present_logs';
    protected $primaryKey = 'log_id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = 
    [
        'habit_id',
        'outcome',
        'value',
        'log_date',
        'log_time',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function habit(): BelongsTo
    {
        return $this->belongsTo(PresentHabits::class, 'habit_id', 'habit_id');
    }

    // protected static function newFactory(): PresentLogsFactory
    // {
    //     // return PresentLogsFactory::new();
    // }
}
