<?php

namespace Modules\Future\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class TaskMaster extends Model
{
    protected $table = 'future_task_master';

    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'title',
        'type',
        'content',
        'created_by', 
        'edited_by', 
        'deleted_by',
    ];

    protected $casts = [
        'type' => 'string',
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto-set created_by on creation
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
            }
        });

        // Auto-set edited_by on update
        static::updating(function ($model) {
            if (Auth::check()) {
                $model->edited_by = Auth::id();
            }
        });

        // Auto-set deleted_by on soft delete
        static::deleting(function ($model) {
            if (Auth::check()) {
                $model->deleted_by = Auth::id();
                // Save the model to store the deleted_by ID before the soft deletion occurs
                $model->save(); 
            }
        });
    }

    /**
     * Get the parent item (folder) of this item.
     */
    public function parent(): BelongsTo
    {
        // This links the 'parent_id' column to another TaskMaster ID
        return $this->belongsTo(TaskMaster::class, 'parent_id');
    }

    /**
     * Get the children items (files/folders) within this item.
     */
    public function children(): HasMany
    {
        // This links all other TaskMaster records where their 'parent_id' matches this item's 'id'
        return $this->hasMany(TaskMaster::class, 'parent_id');
    }

    /**
     * Get the user who created this item.
     */
    public function creator(): BelongsTo
    {
        // This links the 'created_by' column to the 'id' column of the User model
        return $this->belongsTo(User::class, 'created_by');
    }
}
