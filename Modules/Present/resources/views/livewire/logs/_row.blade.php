{{--
    Module: Present  |  Partial: livewire/logs/_row.blade.php

    A single habit row used by BOTH the Pending and Completed sections.

    Inputs (from the parent include):
        $habitId  : int    - habit's primary key, used to namespace radio IDs
        $data     : array  - one entry from $logData (see Livewire class docblock)
        $state    : string - 'pending' | 'completed' (purely cosmetic accents)

    DESIGN NOTES
    ------------
    - Card-style row (instead of a table cell) reads better on phones and
      gives the four-button outcome pill room to breathe.
    - Outcome IDs are namespaced by habit so multiple <input type=radio>
      groups on the same page don't collide.
    - Outcome pills use Bootstrap's `btn-check` pattern - one tap, no
      JavaScript - and live-bind via wire:model.live so the user sees
      their selection immediately (re-grouping happens on Save, not on tap,
      which keeps the UI from jumping while the user is still deciding).
--}}

@php
    /** Outcome metadata in display order. Driven from one place so colours and
     *  labels stay consistent between this partial and any future ones. */
    $outcomeOptions = [
        ['value' => 'not_done',     'label' => 'Not Done',     'btn' => 'btn-outline-secondary'],
        ['value' => 'yes',          'label' => 'Yes',          'btn' => 'btn-outline-success'],
        ['value' => 'no',           'label' => 'No',           'btn' => 'btn-outline-danger'],
        ['value' => 'not_possible', 'label' => 'Not Possible', 'btn' => 'btn-outline-dark'],
    ];

    $borderClass = $state === 'completed' ? 'border-success-subtle' : 'border-secondary-subtle';
@endphp

<div class="card shadow-sm {{ $borderClass }}" wire:key="habit-row-{{ $habitId }}">
    <div class="card-body p-2 p-sm-3">

        {{-- Header: habit name + unit --}}
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="fw-semibold">{{ $data['habit_name'] }}</div>
            <span class="badge text-bg-info">{{ $data['unit_name'] }}</span>
        </div>

        {{-- Outcome pills (4-segment radio, full width, equal columns) --}}
        <div class="btn-group w-100 mb-2" role="group"
             aria-label="Outcome for {{ $data['habit_name'] }}">
            @foreach ($outcomeOptions as $opt)
                @php
                    $inputId = "outcome-{$habitId}-{$opt['value']}";
                @endphp
                <input type="radio"
                       class="btn-check"
                       id="{{ $inputId }}"
                       value="{{ $opt['value'] }}"
                       wire:model.live="logData.{{ $habitId }}.outcome"
                       autocomplete="off">
                <label for="{{ $inputId }}" class="btn btn-sm {{ $opt['btn'] }}">
                    {{ $opt['label'] }}
                </label>
            @endforeach
        </div>
        @error("logData.$habitId.outcome")
            <small class="text-danger d-block mb-2">{{ $message }}</small>
        @enderror

        {{-- Value + Date-Time in one responsive row (stacks on phones) --}}
        <div class="row g-2 mb-2">
            <div class="col-12 col-sm-5">
                <label class="form-label small mb-1 text-muted">Value</label>
                <input type="number" step="any" inputmode="decimal"
                       wire:model.live="logData.{{ $habitId }}.value"
                       class="form-control form-control-sm"
                       placeholder="Amount ({{ $data['unit_name'] }})">
                @error("logData.$habitId.value")
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            <div class="col-12 col-sm-7">
                <label class="form-label small mb-1 text-muted">Date / Time</label>
                <input type="datetime-local"
                       wire:model.live="logData.{{ $habitId }}.log_time"
                       class="form-control form-control-sm">
                @error("logData.$habitId.log_time")
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>

        {{-- Notes (auto-growing textarea via Alpine; resizes on input and after Livewire patches) --}}
        <div>
            <label class="form-label small mb-1 text-muted">Notes</label>
            <textarea
                x-data="{
                    resize() {
                        $el.style.height = 'auto';
                        $el.style.height = $el.scrollHeight + 'px';
                    }
                }"
                x-init="resize()"
                @input="resize()"
                x-on:livewire:updated="resize()"
                wire:model.live="logData.{{ $habitId }}.notes"
                wire:ignore.self
                rows="1"
                class="form-control form-control-sm"
                placeholder="Notes..."
                style="resize: none; overflow-y: hidden;"></textarea>
            @error("logData.$habitId.notes")
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

    </div>
</div>
