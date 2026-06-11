<?php

namespace Modules\People\Models\Labels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\People\Models\Document;
use Modules\People\Models\Concerns\HasAudit;

/**
 * Lookup: document kinds (PAN, Aadhaar, Passport, DL, Voter ID, etc.).
 */
class DocumentType extends Model
{
    use HasAudit;
    use SoftDeletes;

    protected $table = 'people_document_types';
    protected $primaryKey = 'document_type_id';

    protected $fillable = [
        'name',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function documents()
    {
        return $this->hasMany(Document::class, 'document_type_id', 'document_type_id');
    }
}
