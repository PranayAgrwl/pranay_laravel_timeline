{{--
    Module: Present  |  View: livewire/logs/index.blade.php

    PURPOSE
    -------
    Daily habit logger. Designed phone-first - everything is reachable with a
    thumb in portrait orientation.

    LAYOUT
    ------
        [ Date navigator ]
        [ Pending section   <- habits with no log row for the day ]
        [ Divider                                                 ]
        [ Completed section <- habits with a saved log row        ]
        [ Save button (sticky on mobile) ]

    OUTCOME PILLS
    -------------
    Each habit row offers a 4-button segmented radio:
        Not Done | Yes | No | Not Possible
    The "Not Done" choice is a UI placeholder; on save, choosing it for a
    habit that already had a log row soft-deletes that row (clean undo).

    KEYING
    ------
    The Livewire component passes ordered ID lists ($pendingHabitIds,
    $completedHabitIds) plus a single $logData keyed by habit_id. We iterate
    the lists and look up row data on the fly - this keeps the two sections
    in sync with the same source of truth.
--}}

<div class="container-fluid p-0">
    <div class="p-2 p-sm-3 p-md-4">

        <h2 class="h4 mb-3 text-center text-md-start">Daily Habit Log</h2>

        {{-- Flash messages from saveLogs() --}}
        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- 1. Date navigator (sticky-ish, easy to thumb) --}}
        <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded border">
            <button type="button" class="btn btn-outline-secondary"
                    wire:click="previousDay" wire:loading.attr="disabled">
                <i class="bi bi-caret-left-fill"></i>
            </button>

            <div class="text-center px-2 flex-grow-1">
                <div class="fw-semibold small">
                    {{ $currentDateFormatted }}
                    @if ($isToday)
                        <span class="badge text-bg-warning ms-1">Today</span>
                    @endif
                </div>
            </div>

            {{-- Next-day is meaningless when already on today; disable to prevent confusion. --}}
            <button type="button" class="btn btn-outline-secondary"
                    wire:click="nextDay" wire:loading.attr="disabled"
                    @if ($isToday) disabled @endif>
                <i class="bi bi-caret-right-fill"></i>
            </button>
        </div>

        {{-- The entire two-section table is a single form so one Save persists everything. --}}
        <form wire:submit.prevent="saveLogs">

            {{-- =========================================================
                 SECTION A : PENDING
                 Habits with no saved log for this date yet.
                 ========================================================= --}}
            <div class="mb-2 d-flex justify-content-between align-items-center">
                <h3 class="h6 mb-0 text-uppercase text-muted">
                    <i class="bi bi-hourglass-split me-1"></i>
                    Pending
                    <span class="badge text-bg-secondary ms-1">{{ count($pendingHabitIds) }}</span>
                </h3>
            </div>

            @if (count($pendingHabitIds) === 0)
                <div class="alert alert-light border text-center py-2 mb-3">
                    Nothing pending - every habit has been logged for this day.
                </div>
            @else
                <div class="d-flex flex-column gap-2 mb-3">
                    @foreach ($pendingHabitIds as $habitId)
                        @include('present::livewire.logs._row', [
                            'habitId' => $habitId,
                            'data'    => $logData[$habitId],
                            'state'   => 'pending',
                        ])
                    @endforeach
                </div>
            @endif

            {{-- Visual divider between the two groups --}}
            <hr class="my-4 border-2">

            {{-- =========================================================
                 SECTION B : COMPLETED
                 Habits with a saved log (yes / no / not_possible).
                 ========================================================= --}}
            <div class="mb-2 d-flex justify-content-between align-items-center">
                <h3 class="h6 mb-0 text-uppercase text-muted">
                    <i class="bi bi-check2-square me-1"></i>
                    Completed
                    <span class="badge text-bg-secondary ms-1">{{ count($completedHabitIds) }}</span>
                </h3>
            </div>

            @if (count($completedHabitIds) === 0)
                <div class="alert alert-light border text-center py-2 mb-3">
                    Nothing logged yet for this day.
                </div>
            @else
                <div class="d-flex flex-column gap-2 mb-4">
                    @foreach ($completedHabitIds as $habitId)
                        @include('present::livewire.logs._row', [
                            'habitId' => $habitId,
                            'data'    => $logData[$habitId],
                            'state'   => 'completed',
                        ])
                    @endforeach
                </div>
            @endif

            {{-- =========================================================
                 SAVE
                 Single submit persists all changes in both sections.
                 ========================================================= --}}
            <div class="d-grid">
                <button type="submit" class="btn btn-warning btn-lg fw-bold text-white"
                        wire:loading.attr="disabled" wire:target="saveLogs">
                    <span wire:loading.remove wire:target="saveLogs">
                        <i class="bi bi-save2 me-1"></i> Save Day
                    </span>
                    <span wire:loading wire:target="saveLogs">Saving...</span>
                </button>
            </div>

            {{-- Lightweight visual cue while paging between days --}}
            <div wire:loading.block wire:target="previousDay, nextDay" class="text-center mt-3 small text-info">
                Loading date...
            </div>
        </form>
    </div>
</div>
