<?php

namespace Modules\Future\Http\Livewire;

use Livewire\Component;
use Modules\Future\Models\TaskMaster;

class Index extends Component
{
    // Public property to store the items being displayed (files/folders)
    public $items;

    // Public property to track the current parent folder ID
    public $parentId = null;

    // Public property to track the full path for UI display (e.g., "Home / Documents")
    public $path = 'Home';

    public function mount($parentId = null)
    {
        $this->parentId = $parentId;
        $this->loadItems();
        $this->loadPath();
    }

    public function loadItems()
    {
        // Load items based on the current parentId, ordered by type (folders first)
        $this->items = TaskMaster::query()
            ->where('parent_id', $this->parentId)
            ->orderByRaw("FIELD(type, 'folder', 'file')")
            ->orderBy('title')
            ->get();
    }

    public function loadPath()
    {
        // This method builds the navigation path (You can expand this later)
        if ($this->parentId === null) {
            $this->path = 'Home';
            return;
        }

        // For now, let's keep it simple until the full path logic is implemented,
        // but it ensures the component is ready for mounting with an ID.
    }

    /**
     * Navigate into a folder.
     */
    public function openFolder($itemId)
    {
        $folder = TaskMaster::find($itemId);

        // Basic check to ensure it's a folder before navigating
        if ($folder && $folder->type === 'folder') {
            $this->parentId = $itemId;
            $this->loadItems();
            $this->loadPath(); // Recalculate the path after navigation
        }
    }

    public function render()
    {
        // The Livewire view is rendered here
        return view('future::livewire.index')
            ->layout('layouts.app'); // Assuming you have a main app layout
    }
}