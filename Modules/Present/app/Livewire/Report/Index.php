<?php

namespace Modules\Present\Livewire\Report;

use Livewire\Component;
use Modules\Present\Models\PresentLogs;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Index extends Component
{
    public $month;
    public $reportData = [];
    public $availableMonths = [];

    public $daysArray = [];

    public function mount()
    {
        // 1. Find the distinct months with logs and store them
        $this->availableMonths = $this->getAvailableMonths();
        
        // 2. Default to the latest month, or today's month if no logs exist
        $this->month = $this->availableMonths[0] ?? Carbon::now()->format('Y-m');
        
        // 3. Load the data
        $this->loadReportData();
    }
    
    // Retrieves a unique, sorted list of months (YYYY-MM) the user has logs for
    protected function getAvailableMonths()
    {
        return PresentLogs::where('created_by', Auth::id())
            ->selectRaw('DATE_FORMAT(log_date, "%Y-%m") as month_year')
            ->distinct()
            ->orderByDesc('month_year')
            ->pluck('month_year')
            ->toArray();
    }

    // Called when the user selects a new month
    public function updatedMonth()
    {
        $this->loadReportData();
    }

    protected function loadReportData()
    {
        // Define the start and end dates for the selected month
        $startOfMonth = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth()->toDateString();
        $endOfMonth = Carbon::createFromFormat('Y-m', $this->month)->endOfMonth()->toDateString();

        $monthCarbon = Carbon::createFromFormat('Y-m', $this->month);
        $startDay = 1;
        $endDay = $monthCarbon->daysInMonth;
        $this->daysArray = range($startDay, $endDay);
        
        // 1. Fetch all logs for the month
        $logs = PresentLogs::with('habit.unit')
            ->where('created_by', Auth::id())
            ->whereBetween('log_date', [$startOfMonth, $endOfMonth])
            ->get()
            ->sortBy(fn($log) =>
            $log->habit->sort_order)
            ->values();

        $this->reportData = [];

        // 2. Aggregate logs into the reportData structure
        foreach ($logs as $log) {
            $habitName = $log->habit->habit_name;
            $logDate = $log->log_date; 
            
            // Initialize habit structure if not exists
            if (!isset($this->reportData[$habitName])) {
                $this->reportData[$habitName] = [
                    'unit' => $log->habit->unit->name ?? 'N/A',
                    'dates' => [],
                ];
            }
            
            // Store log data for the specific date
            $this->reportData[$habitName]['dates'][$logDate] = [
                'outcome' => $log->outcome,
                'value' => $log->value,
                'log_time' => $log->log_time,
                'notes' => $log->notes,
                // Assign a boolean for easy styling/display
                'positive' => $log->outcome === 'yes', 
                'formatted_datetime' => Carbon::parse($log->log_time)->format('M d, Y \a\t g:i A'),
            ];
        }
    }

    public function render()
    {
        return view('present::livewire.report.index', [
            'monthTitle' => Carbon::createFromFormat('Y-m', $this->month)->format('F Y'),
        ]);
    }
}