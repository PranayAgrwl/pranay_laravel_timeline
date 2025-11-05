<?php

namespace Modules\Present\Livewire\Habits;

use Livewire\Component;
use Modules\Present\Models\PresentHabits;
use Modules\Present\Models\PresentUnits;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $habit_name = '';
    public $unit_id = '';
    public $notes = '';
    public $status = 'active';
    public $private = 'open';

    public $showCreateForm = false;
    public $editHabitId = null;

    protected function rules()
    {
        $id = $this->editHabitId;
        return[
            'habit_name' => 'required|string|max:100|unique:present_habits,habit_name,' . $id . ',habit_id',
            'unit_id' => 'required|exists:present_units,unit_id',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'private' => 'required|in:open,private',
        ];
    }

    public function save()
    {
        $this->validate();

        $lastSortOrder = PresentHabits::max('sort_order') ?? 0;
        $nextSortOrder = $lastSortOrder + 1;

        PresentHabits::create([
            'habit_name' => $this->habit_name,
            'unit_id' => $this->unit_id,
            'notes' => $this->notes,
            'status' => $this->status,
            'private' => $this->private,
            'sort_order' => $nextSortOrder,
            'created_by' => Auth::id(),
        ]);

        $this->reset(['habit_name', 'unit_id', 'notes', 'status', 'private', 'showCreateForm']);
        session()->flash('success', 'Habit Created Successfully!');
    }

    public function deleteHabit($habitId)
    {
        $habit = PresentHabits::findOrFail($habitId);
        $deletedSortOrder = $habit->sort_order;

        $habit->deleted_by = Auth::id();
        $habit->sort_order = null;

        $habit->save();
        $habit->delete();

        if ($deletedSortOrder !== null) {
        // Find all habits whose sort_order is greater than the deleted habit's old order and decrement their sort_order by 1.
        PresentHabits::where('sort_order', '>', $deletedSortOrder)
            ->decrement('sort_order');
    }

        session()->flash('success', "Habit '{$habit->habit_name}' Deleted Successfully!");
    }

    public function toggleCreateForm()
    {
        $this->showCreateForm = !$this->showCreateForm;
        $this->cancelEdit();
    }

    public function editHabit($habitId)
    {
        $this->resetValidation();
        $this->reset(['showCreateForm']);

        $habit = PresentHabits::findOrFail($habitId);

        $this->editHabitId = $habit->habit_id;
        $this->habit_name = $habit->habit_name;
        $this->unit_id = $habit->unit_id;
        $this->notes = $habit->notes;
        $this->status = $habit->status;
        $this->private = $habit->private;
    }

    public function updateHabit()
    {
        $this->validate();

        $habit = PresentHabits::findOrFail($this->editHabitId);

        $habit->update([
            'habit_name' => $this->habit_name,
            'unit_id' => $this->unit_id,
            'notes' => $this->notes,
            'status' => $this->status,
            'private' => $this->private,
            'updated_by' => Auth::id(),
        ]);

        $this->reset(['editHabitId','habit_name', 'unit_id', 'notes', 'status', 'private']);
        session()->flash('success', "Habit '{$habit->habit_name}' Updated Successfully!");
    }

    public function cancelEdit()
    {
        $this->reset(['editHabitId', 'habit_name', 'unit_id', 'notes', 'status', 'private']);
        $this->resetValidation();
    }

    // --- NEW SORTING LOGIC ---

    /**
     * Moves a habit up in the sort order (decreases sort_order value).
     * This swaps the current habit with the one directly above it.
     */
    public function moveUp($habitId)
    {
        $currentHabit = PresentHabits::findOrFail($habitId);

        // Find the habit immediately ABOVE the current one in the sort order
        $previousHabit = PresentHabits::where('sort_order', '<', $currentHabit->sort_order)
            ->orderBy('sort_order', 'desc') // Get the closest (highest sort_order less than current)
            ->first();

        if ($previousHabit) {
            // Swap sort_order values
            $currentSortOrder = $currentHabit->sort_order;
            $previousSortOrder = $previousHabit->sort_order;

            $currentHabit->sort_order = $previousSortOrder;
            $previousHabit->sort_order = $currentSortOrder;
            
            $currentHabit->save();
            $previousHabit->save();
        }
    }

    /**
     * Moves a habit down in the sort order (increases sort_order value).
     * This swaps the current habit with the one directly below it.
     */
    public function moveDown($habitId)
    {
        $currentHabit = PresentHabits::findOrFail($habitId);

        // Find the habit immediately BELOW the current one in the sort order
        $nextHabit = PresentHabits::where('sort_order', '>', $currentHabit->sort_order)
            ->orderBy('sort_order', 'asc') // Get the closest (lowest sort_order greater than current)
            ->first();

        if ($nextHabit) {
            // Swap sort_order values
            $currentSortOrder = $currentHabit->sort_order;
            $nextSortOrder = $nextHabit->sort_order;

            $currentHabit->sort_order = $nextSortOrder;
            $nextHabit->sort_order = $currentSortOrder;
            
            $currentHabit->save();
            $nextHabit->save();
        }
    }

    public function render()
    {
        $habits = PresentHabits::with('unit')
            ->orderBy('sort_order', 'asc') 
            ->get();

        $minSortOrder = $habits->min('sort_order');
        $maxSortOrder = $habits->max('sort_order');

        $units = PresentUnits::where('status', 'active')->pluck('name', 'unit_id');

        return view('present::livewire.habits.index', [
            'habits' => $habits,
            'units' => $units,
            'minSortOrder' => $minSortOrder, // Pass to view to hide 'Up' button for first item
            'maxSortOrder' => $maxSortOrder, // Pass to view to hide 'Down' button for last item
        ]);
    }
}
