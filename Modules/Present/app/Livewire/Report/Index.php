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

        // Aggregate logs into the reportData structure used by the grid view.
        // We pre-compute the bits the view needs (positive flag, formatted
        // datetime) so the Blade stays declarative.
        foreach ($logs as $log) {
            $habitName = $log->habit->habit_name;
            $logDate   = $log->log_date;

            if (!isset($this->reportData[$habitName])) {
                $this->reportData[$habitName] = [
                    'unit'  => $log->habit->unit->name ?? 'N/A',
                    'dates' => [],
                ];
            }

            $this->reportData[$habitName]['dates'][$logDate] = [
                'outcome'  => $log->outcome,           // 'yes' | 'no' | 'not_possible'
                'value'    => $log->value,
                'log_time' => $log->log_time,
                'notes'    => $log->notes,

                // Guard against null log_time: previously Carbon::parse(null) silently
                // returned "now", which produced misleading tooltips for rows saved
                // without an explicit time.
                'formatted_datetime' => $log->log_time
                    ? Carbon::parse($log->log_time)->format('M d, Y \a\t g:i A')
                    : null,
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