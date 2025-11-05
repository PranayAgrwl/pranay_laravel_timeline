<?php

namespace Modules\Present\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Present\Models\PresentUnits;
use Modules\Present\Models\PresentLogs;
// use Modules\Present\Database\Factories\PresentHabitsFactory;

class PresentHabits extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'present_habits';
    protected $primaryKey = 'habit_id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = 
    [
        'habit_name',
        'unit_id',
        'notes',
        'status',
        'private',
        'sort_order',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * Get the unit that owns the habit.
     */
    public function unit(): BelongsTo
    {
        // $this->belongsTo(RelatedModel, foreignKey, ownerKey)
        return $this->belongsTo(PresentUnits::class, 'unit_id', 'unit_id');
    }

    /**
     * Get the logs for the habit.
     */
    public function logs(): HasMany
    {
        // One habit has many log entries (PresentLogs model).
        // It connects this model's primary key 'habit_id' to the logs model's foreign key 'habit_id'.
        return $this->hasMany(PresentLogs::class, 'habit_id', 'habit_id');
    }

    // protected static function newFactory(): PresentHabitsFactory
    // {
    //     // return PresentHabitsFactory::new();
    // }
}
