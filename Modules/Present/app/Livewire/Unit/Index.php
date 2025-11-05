<?php

namespace Modules\Present\Livewire\Unit;

use Livewire\Component;
use Modules\Present\Models\PresentUnits;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $name = '';
    public $notes0 = '';
    public $notes1 = '';
    public $status = 'active';

    public $showCreateForm = false;

    public $editUnitId = null;

    protected function rules()
    {
        $id = $this->editUnitId;
        return [
            'name' => 'required|string|max:100|unique:present_units,name,' . $id . ',unit_id',
            'notes0' => 'nullable|string',
            'notes1' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function save()
    {
        $this->validate();

        PresentUnits::create([
            'name' => $this->name,
            'notes0' => $this->notes0,
            'notes1' => $this->notes1,
            'status' => $this->status,
            'created_by' => Auth::id(),
        ]);

        $this->reset(['name', 'notes0', 'notes1', 'status', 'showCreateForm']);

        session()->flash('success', 'Unit Created Successfully!');
    }

    public function deleteUnit($unitId)
    {
        $unit = PresentUnits::findOrFail($unitId);
        $unit->delete();
        session()->flash('success', "Unit '{$unit->name}' Deleted Successfully!");
    }

    public function toggleCreateForm()
    {
        $this->showCreateForm = !$this->showCreateForm;
    }

    public function editUnit($unitId)
    {
        $this->resetValidation();
        $this->reset(['showCreateForm']);

        $unit = PresentUnits::findOrFail($unitId);

        $this->editUnitId = $unit->unit_id;
        $this->name = $unit->name;
        $this->notes0 = $unit->notes0;
        $this->notes1 = $unit->notes1;
        $this->status = $unit->status;
    }

    public function updateUnit()
    {
        $this->validate($this->rules());

        $unit = PresentUnits::findOrFail($this->editUnitId);

        $unit->update([
            'name' => $this->name,
            'notes0' => $this->notes0,
            'notes1' => $this->notes1,
            'status' => $this->status,
            'updated_by' => Auth::id(),
        ]);

        $this->reset(['editUnitId', 'name', 'notes0', 'notes1', 'status']);

        session()->flash('success', "Unit '{$unit->name}' Updated Successfully!");
    }

    public function cancelEdit()
    {
        $this->reset(['editUnitId', 'name', 'notes0', 'notes1', 'status']);
        $this->resetValidation();
    }

    public function render()
    {
        $units = PresentUnits::all();

        return view('present::livewire.unit.index', [
            'units' => $units,
        ]);
    }
}
