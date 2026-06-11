<?php

namespace Modules\People\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\People\Models\Concerns\HasAudit;
use Modules\People\Models\Labels\DocumentType;

/**
 * Document  -  a file (PAN scan, passport scan, etc.) associated
 * with a contact.
 *
 * `file_address` is the storage path; the exact format isn't locked
 * here - upload code in the controller/Livewire layer (Step 4) is the
 * single source of truth for path conventions.
 */
class Document extends Model
{
    use HasAudit;
    use SoftDeletes;

    protected $table = 'people_documents';
    protected $primaryKey = 'document_id';

    protected $fillable = [
        'document_type_id',
        'contact_id',
        'file_name',
        'file_address',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function type()
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id', 'document_type_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'contact_id');
    }
}
