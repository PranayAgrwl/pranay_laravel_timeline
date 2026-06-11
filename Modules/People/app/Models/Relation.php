<?php

namespace Modules\People\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\People\Models\Concerns\HasAudit;
use Modules\People\Models\Labels\RelationType;

/**
 * Relation  -  directed edge between two contacts of a given type.
 *
 * AUTO-MIRROR  (the core behaviour of this model)
 * ----------------------------------------------------------------
 * When a relation is saved and its RelationType row has mirror=1,
 * the inverse relation is created automatically:
 *
 *      Type 'spouse'  -> mirror back as 'spouse'  (self-pair)
 *      Type 'parent'  -> mirror back as 'child'   (cross-pair)
 *      Type 'child'   -> mirror back as 'parent'  (cross-pair)
 *
 * Anything else with mirror=1 falls back to "same type both ways"
 * (the spouse-style self-pair). Add new cross-pairs in the
 * INVERSE_PAIRS table below if you ever introduce them (e.g.
 * sibling <-> sibling, in-law <-> in-law).
 *
 * IMPORTANT: this mapping is BY NAME. If you rename the rows
 * (e.g. change 'parent' to 'father') the mirror logic will silently
 * stop pairing them. The bible and the relation_types lookup are
 * the source of truth - keep them in sync.
 *
 * Recursion is prevented via the static $mirroring flag: the model
 * sets it during the inner save so the listener short-circuits the
 * second pass.
 *
 * Mirror rows are also soft-deleted together: deleting A->B (parent)
 * also soft-deletes the auto-paired B->A (child).
 */
class Relation extends Model
{
    use HasAudit;
    use SoftDeletes;

    protected $table = 'people_relations';
    protected $primaryKey = 'relation_id';

    protected $fillable = [
        'contact_from_id',
        'contact_to_id',
        'relation_type_id',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * Cross-pair name mapping. Both directions are listed so a lookup
     * works regardless of which side we're mirroring from.
     *
     * Self-pair types (where saving X mirrors back as X - e.g. spouse)
     * are NOT in this table; they fall through to the default branch.
     */
    private const INVERSE_PAIRS = [
        'parent' => 'child',
        'child'  => 'parent',
    ];

    /**
     * Re-entrancy guard. When the listener is creating/deleting an
     * auto-mirror row it flips this flag so the listener won't fire
     * for the mirror's own save/delete event.
     */
    private static bool $mirroring = false;

    protected static function booted(): void
    {
        // Auto-create the inverse on insert (not on update - updating
        // an existing relation shouldn't spawn a fresh mirror).
        static::created(function (self $relation) {
            if (self::$mirroring) {
                return;
            }
            $relation->createInverseIfNeeded();
        });

        // Auto-soft-delete the inverse when one side is soft-deleted.
        static::deleted(function (self $relation) {
            if (self::$mirroring) {
                return;
            }
            $relation->deleteInverseIfPresent();
        });
    }

    public function fromContact()
    {
        return $this->belongsTo(Contact::class, 'contact_from_id', 'contact_id');
    }

    public function toContact()
    {
        return $this->belongsTo(Contact::class, 'contact_to_id', 'contact_id');
    }

    public function type()
    {
        return $this->belongsTo(RelationType::class, 'relation_type_id', 'relation_type_id');
    }

    /**
     * If the relation's type is flagged mirror=true, create the
     * inverse row (if it doesn't already exist).
     */
    protected function createInverseIfNeeded(): void
    {
        $type = $this->type()->first();
        if (! $type || ! $type->mirror) {
            return;
        }

        $inverseTypeId = $this->resolveInverseTypeId($type);
        if ($inverseTypeId === null) {
            return;
        }

        // Don't duplicate if the inverse already exists.
        $alreadyThere = static::where('contact_from_id', $this->contact_to_id)
            ->where('contact_to_id', $this->contact_from_id)
            ->where('relation_type_id', $inverseTypeId)
            ->exists();
        if ($alreadyThere) {
            return;
        }

        self::$mirroring = true;
        try {
            static::create([
                'contact_from_id'  => $this->contact_to_id,
                'contact_to_id'    => $this->contact_from_id,
                'relation_type_id' => $inverseTypeId,
                'notes'            => '(auto-mirrored from relation_id='.$this->relation_id.')',
                'created_by'       => $this->created_by,
                'updated_by'       => $this->updated_by,
            ]);
        } finally {
            self::$mirroring = false;
        }
    }

    /**
     * If a mirrored counterpart exists for the deleted relation,
     * soft-delete it too so the family tree stays consistent.
     */
    protected function deleteInverseIfPresent(): void
    {
        $type = $this->type()->withTrashed()->first();
        if (! $type || ! $type->mirror) {
            return;
        }

        $inverseTypeId = $this->resolveInverseTypeId($type);
        if ($inverseTypeId === null) {
            return;
        }

        $inverse = static::where('contact_from_id', $this->contact_to_id)
            ->where('contact_to_id', $this->contact_from_id)
            ->where('relation_type_id', $inverseTypeId)
            ->first();
        if (! $inverse) {
            return;
        }

        self::$mirroring = true;
        try {
            $inverse->delete();
        } finally {
            self::$mirroring = false;
        }
    }

    /**
     * Given a RelationType, find the relation_type_id of its inverse:
     *   - cross-pair (parent <-> child)  via INVERSE_PAIRS by name
     *   - default fall-through            mirror back as the SAME type
     */
    protected function resolveInverseTypeId(RelationType $type): ?int
    {
        $inverseName = self::INVERSE_PAIRS[strtolower($type->name)] ?? null;

        if ($inverseName === null) {
            // self-pair (spouse <-> spouse, sibling <-> sibling, etc.)
            return (int) $type->relation_type_id;
        }

        $inverse = RelationType::query()
            ->whereRaw('LOWER(name) = ?', [$inverseName])
            ->first();

        return $inverse?->relation_type_id !== null
            ? (int) $inverse->relation_type_id
            : null;
    }
}
