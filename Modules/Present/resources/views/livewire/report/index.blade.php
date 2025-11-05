<div class="container-fluid mt-4">
    <div class="p-4">
        <h2 class="card-title h3 mb-4">Monthly Habit Report: {{ $monthTitle }}</h2>

        {{-- Month Selector and Loading Indicator --}}
        <div class="mb-4 d-flex align-items-center">
            <label for="month-select" class="form-label mb-0 me-3 fw-bold">Select Month:</label>
            <select id="month-select" class="form-select w-auto" wire:model.live="month">
                @foreach ($availableMonths as $m)
                    <option value="{{ $m }}">{{ Carbon\Carbon::createFromFormat('Y-m', $m)->format('F Y') }}</option>
                @endforeach
            </select>
            
            <div wire:loading.block wire:target="month" class="ms-3 text-info">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Loading data...
            </div>
        </div>
        
        <hr>
        
        {{-- Report Table (Calendar Grid) --}}
        @if (empty($reportData))
            <div class="alert alert-warning text-center mt-3">
                No habit logs found for {{ $monthTitle }}.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle text-center table-fixed-header" style="min-width: 100%;">
                    
                    {{-- Table Header: Days of the Month --}}
                    <thead>
                        <tr class="table-dark">
                            <th style="min-width: 180px; position: sticky; left: 0; background-color: #212529; z-index: 2;">Habit</th>
                            @foreach ($daysArray as $day)
                                <th style="min-width: 30px;">{{ $day }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    
                    {{-- Table Body: Habit Rows --}}
                    <tbody>
                        @foreach ($reportData as $habitName => $data)
                            <tr>
                                {{-- Habit Name Column (Fixed on the left) --}}
                                <th class="text-start" style="position: sticky; left: 0; background-color: #f8f9fa; z-index: 1;">
                                    <strong>{{ $habitName }}</strong><br>
                                    <small class="text-muted">{{ $data['unit'] }}</small>
                                </th>

                                {{-- Daily Log Cells --}}
                                @foreach ($daysArray as $day)
                                    @php
                                        // 1. Construct the full date string for lookup (e.g., '2025-11-04')
                                        $paddedDay = str_pad($day, 2, '0', STR_PAD_LEFT);
                                        $fullDate = $this->month . '-' . $paddedDay;
                                        
                                        // 2. Check if a log exists for this habit on this day
                                        $log = $data['dates'][$fullDate] ?? null;
                                        
                                        // 3. Determine the cell content and style
                                        $cellClass = 'bg-light'; // Default blank
                                        $cellStyle = '';
                                        $cellContent = '-'; // Default content
                                        $tooltipText = '';

                                        if ($log) {
                                            if ($log['positive']) {
                                                // Positive Outcome (YES)
                                                $cellClass = '';
                                                $cellStyle = 'background-color: #e0f7e0; color: #1e7040;';
                                                $cellContent = !is_null($log['value']) ? $log['value'] : 'Y';
                                            } else {
                                                // Negative Outcome (NO)
                                                $cellClass = '';
                                                $cellStyle = 'background-color: #fcebeb; color: #cc0000;';
                                                $cellContent = 'N'; 
                                            }

                                            $tooltipText = $log['formatted_datetime'];
                                            if (!empty($log['notes'])) {
                                                // Add a separator and the notes if notes exist
                                                $tooltipText .= ' | Notes: ' . $log['notes'];
                                            }

                                        }
                                    @endphp

                                    <td class="{{ $cellClass }}" style="{{ $cellStyle }}">
                                        {{ $cellContent }}
                                        @if ($log && (!empty($log['notes']) || !empty($log['formatted_datetime'])))
                                            <i class="bi bi-info-circle-fill small" 
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                title="{{ $tooltipText }}"></i>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        
    </div>
</div>