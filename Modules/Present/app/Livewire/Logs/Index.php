<?php

namespace Modules\Present\Livewire\Logs;

use Livewire\Component;
use Modules\Present\Models\PresentHabits;
use Modules\Present\Models\PresentLogs;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Index extends Component
{
    // The current date the user is viewing/logging for
    public $log_date; 
    
    // Array to hold all habit data and log inputs for the current date
    // Structure: [habit_id => ['log_id', 'outcome', 'value', 'log_time', 'notes']]
    public $logData = [];

    // --- INITIALIZATION ---

    public function mount()
    {
        // Default the log_date to today
        $this->log_date = Carbon::today()->toDateString();
        // Load initial data for the current date
        $this->loadLogData();
    }
    
    // --- DATE NAVIGATION ---

    public function previousDay()
    {
        $this->log_date = Carbon::parse($this->log_date)->subDay()->toDateString();
        $this->loadLogData();
    }
    public function nextDay()
    {
        $this->log_date = Carbon::parse($this->log_date)->addDay()->toDateString();
        $this->loadLogData();
    }
    
    // --- DATA LOADING ---

    /**
     * Loads habits and merges existing logs for the current $log_date.
     */
    protected function loadLogData()
    {
        // 1. Fetch all active habits, ordered by sort_order, with unit data
        $habits = PresentHabits::with('unit')
            ->where('status', 'active')
            ->orderBy('sort_order', 'asc')
            ->get();
            
        // 2. Fetch all existing logs for the current user and selected date
        $existingLogs = PresentLogs::where('log_date', $this->log_date)
            ->where('created_by', Auth::id())
            ->get()
            ->keyBy('habit_id'); // Key the logs by habit_id for easy lookup
            
        $this->logData = [];
        
        // 3. Initialize $logData array
        foreach ($habits as $habit) {
            $log = $existingLogs->get($habit->habit_id);

            $this->logData[$habit->habit_id] = [
                // Display data
                'habit_name' => $habit->habit_name,
                'unit_name' => $habit->unit->name ?? 'N/A',
                'habit_unit_id' => $habit->unit_id,
                
                // Log data (pre-filled if log exists, otherwise defaults)
                'log_id' => $log->log_id ?? null, // Key for updating existing log
                'outcome' => $log->outcome ?? 'no',
                'value' => $log->value ?? null,
                
                // Format log_time for easy HTML input (YYYY-MM-DDTHH:MM)
                'log_time' => ($log->log_time ?? null) ? Carbon::parse($log->log_time)->format('Y-m-d\TH:i') : null,
                'notes' => $log->notes ?? null,
            ];
        }
    }
    
    // --- SUBMISSION ---
    
    protected function rules()
    {
        // Dynamic validation rules based on logData keys
        $rules = [];
        foreach ($this->logData as $habitId => $data) {
            
            // Rule 1: Outcome is always required
            $rules["logData.$habitId.outcome"] = 'required|in:yes,no';
            
            // Rule 2: Value for habit - can be nullable
            $rules["logData.$habitId.value"] = 'nullable|numeric';

            // Rule 3: Technical log time must be a valid datetime format
            $rules["logData.$habitId.log_time"] = 'nullable|date_format:Y-m-d\TH:i';
            
            // Rule 4: Notes
            $rules["logData.$habitId.notes"] = 'nullable|string|max:1000';
        }
        return $rules;
    }

    /**
     * Saves or updates log entries for ALL habits visible on the current date.
     */
    public function saveLogs()
    {
        $this->validate();
        $userId = Auth::id();
        $logsSaved = 0;

        foreach ($this->logData as $habitId => $data) {
            // Check if any data has been entered/changed
            if ($data['outcome'] !== 'no' || $data['value'] !== null || $data['notes'] !== null) {
                
                // Prepare data for saving
                $saveData = [
                    'habit_id' => $habitId,
                    'outcome' => $data['outcome'],
                    'value' => $data['value'] === '' ? null : $data['value'],
                    'log_date' => $this->log_date, // Logical reporting date
                    'notes' => $data['notes'],
                    'created_by' => $userId,
                ];
                
                // Handle log_time conversion
                if (!empty($data['log_time'])) {
                    // Convert the HTML datetime-local format back to standard datetime
                    $saveData['log_time'] = Carbon::createFromFormat('Y-m-d\TH:i', $data['log_time']);
                } else {
                    $saveData['log_time'] = null;
                }

                if ($data['log_id']) {
                    // UPDATE existing log
                    PresentLogs::where('log_id', $data['log_id'])->update($saveData + ['updated_by' => $userId]);
                    $logsSaved++;
                } else {
                    // CREATE new log (The unique constraint ensures one log per habit per day)
                    PresentLogs::create($saveData);
                    $logsSaved++;
                }
            }
        }
        
        // Reload data to reflect changes and update form states (especially log_id for new entries)
        $this->loadLogData(); 
        
        session()->flash('success', "{$logsSaved} habit logs saved for " . Carbon::parse($this->log_date)->format('M d, Y'));
    }

    // --- RENDERING ---

    public function render()
    {
        // Ensure data is loaded if component is rendered before loadLogData() is called
        if (empty($this->logData)) {
            $this->loadLogData();
        }

        // Pass the habits data structure to the view
        // The structure already includes unit names and the log inputs
        $habitsData = $this->logData;

        return view('present::livewire.logs.index', [
            'habitsData' => $habitsData,
            'currentDateFormatted' => Carbon::parse($this->log_date)->format('l, F jS, Y'),
            'isToday' => Carbon::parse($this->log_date)->isToday(),
        ]);
    }
}