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

                                {{--
                                    Daily Log Cells
                                    Three persisted outcomes, each with its own colour token:
                                        yes          -> green   (shows numeric value if any, else 'Y')
                                        no           -> red     ('N')
                                        not_possible -> grey    ('NP')
                                    Absent log row -> blank '-'.
                                --}}
                                @foreach ($daysArray as $day)
                                    @php
                                        $paddedDay = str_pad($day, 2, '0', STR_PAD_LEFT);
                                        $fullDate  = $this->month . '-' . $paddedDay;
                                        $log       = $data['dates'][$fullDate] ?? null;

                                        $cellClass   = 'bg-light';
                                        $cellStyle   = '';
                                        $cellContent = '-';
                                        $tooltipText = '';

                                        if ($log) {
                                            switch ($log['outcome']) {
                                                case 'yes':
                                                    $cellStyle   = 'background-color: #e0f7e0; color: #1e7040;';
                                                    $cellContent = !is_null($log['value']) ? $log['value'] : 'Y';
                                                    break;
                                                case 'no':
                                                    $cellStyle   = 'background-color: #fcebeb; color: #cc0000;';
                                                    $cellContent = 'N';
                                                    break;
                                                case 'not_possible':
                                                    // Neutral grey so it reads as "skipped for a reason"
                                                    // rather than "failed" - clearly distinct from the red 'no'.
                                                    $cellStyle   = 'background-color: #e9ecef; color: #495057;';
                                                    $cellContent = 'NP';
                                                    break;
                                            }

                                            $cellClass = ''; // any persisted outcome overrides the default bg

                                            // Build tooltip from whichever pieces actually exist.
                                            $parts = [];
                                            if (!empty($log['formatted_datetime'])) {
                                                $parts[] = $log['formatted_datetime'];
                                            }
                                            if (!empty($log['notes'])) {
                                                $parts[] = 'Notes: ' . $log['notes'];
                                            }
                                            $tooltipText = implode(' | ', $parts);
                                        }
                                    @endphp

                                    <td class="{{ $cellClass }}" style="{{ $cellStyle }}">
                                        {{ $cellContent }}
                                        @if ($log && $tooltipText !== '')
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