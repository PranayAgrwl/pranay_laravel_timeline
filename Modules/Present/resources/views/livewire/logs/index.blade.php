<div class="container mt-4">
    <div class="p-4">

        <h2 class="card-title h3 mb-4">Daily Habit Log</h2>

        {{-- Notification Message --}}
        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        {{-- 1. Date Navigation Section --}}
        <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-light rounded border">
            
            <button class="btn btn-secondary me-2" wire:click="previousDay" wire:loading.attr="disabled">
                <i class="bi bi-caret-left-fill"></i>
            </button>

            <div class="text-center">
                <h4 class="h5 mb-0">
                    {{ $currentDateFormatted }}
                    @if ($isToday)
                        <span class="badge text-bg-warning text-white ms-2">Today</span>
                    @endif
                </h4>
            </div>

            {{-- Disable "Next Day" button if viewing today or the future --}}
            <button class="btn btn-secondary ms-2" wire:click="nextDay" wire:loading.attr="disabled"
                    @if ($isToday) disabled @endif>
                <i class="bi bi-caret-right-fill"></i>
            </button>
        </div>

        {{-- 2. Log Submission Form (The entire table is the form) --}}
        <form wire:submit.prevent="saveLogs">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark sticky-top">
                        <tr>
                            <th style="width: 25%;">Habit</th>
                            <th style="width: 10%;">Outcome</th>
                            <th style="width: 15%;">Value</th>
                            <th style="width: 25%;">Date-Time</th>
                            <th style="width: 25%;">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($habitsData as $habitId => $data)
                            <tr>
                                {{-- Habit Name & Unit --}}
                                <td>
                                    <strong>{{ $data['habit_name'] }}</strong><br>
                                    <span class="badge text-bg-info text-white">{{ $data['unit_name'] }}</span>
                                </td>
                                
                                {{-- Outcome Column (Yes/No) --}}
                                <td>
                                    <select wire:model.live="logData.{{ $habitId }}.outcome" 
                                            class="form-select form-select-sm">
                                        <option value="no">No</option>
                                        <option value="yes">Yes</option>
                                    </select>
                                    @error("logData.$habitId.outcome") <small class="text-danger">{{ $message }}</small> @enderror
                                </td>
                                
                                {{-- Value Column (based on Unit) --}}
                                <td>
                                    <input type="number" step="any"
                                           wire:model.live="logData.{{ $habitId }}.value" 
                                           class="form-control form-control-sm" 
                                           placeholder="Amount">
                                    @error("logData.$habitId.value") <small class="text-danger">{{ $message }}</small> @enderror
                                </td>
                                
                                {{-- Date-Time Column (Technical Timestamp) --}}
                                <td>
                                    <input type="datetime-local" 
                                           wire:model.live="logData.{{ $habitId }}.log_time" 
                                           class="form-control form-control-sm">
                                    @error("logData.$habitId.log_time") <small class="text-danger">{{ $message }}</small> @enderror
                                </td>
                                
                                {{-- Notes Column --}}
                                <td>
                                    <textarea wire:model.live="logData.{{ $habitId }}.notes" 
                                              rows="1" 
                                              class="form-control form-control-sm" 
                                              placeholder="Notes..."></textarea>
                                    @error("logData.$habitId.notes") <small class="text-danger">{{ $message }}</small> @enderror
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center p-4">No active habits found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Submit Button --}}
            <div class="mt-4 text-center">
                <button type="submit" class="btn btn-warning btn-lg fw-bold text-white" 
                    wire:loading.attr="disabled" wire:target="saveLogs, log_date">
                    <span wire:loading.remove wire:target="saveLogs">Save Logs for Today</span>
                    <span wire:loading wire:target="saveLogs">Saving Logs...</span>
                </button>
                <div wire:loading.block wire:target="previousDay, nextDay, today" class="text-center mt-3">
                    <span class="text-info">Loading date...</span>
                </div>
            </div>
        </form>

    </div>
</div>