<?php

namespace Modules\Present\Livewire\Logs;

use Livewire\Component;
use Modules\Present\Models\PresentHabits;
use Modules\Present\Models\PresentLogs;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * Module: Present  |  Page: Daily Logs
 *
 * RESPONSIBILITIES
 * ----------------
 *  1. Render every ACTIVE habit (in user-defined sort order) for a single date.
 *  2. Pre-fill any existing log a user has saved for that date.
 *  3. Let the user mark each habit with one of four outcomes:
 *        - not_done      (UI-only placeholder; absence of a DB row)
 *        - yes           (persisted)
 *        - no            (persisted)
 *        - not_possible  (persisted)
 *  4. Save all changes for the day in a single submit:
 *        - yes / no / not_possible  -> upsert log row
 *        - not_done                 -> soft-delete any existing log row
 *                                     (clean "undo" of a prior save)
 *  5. Split the visible habits into two ordered groups for the view:
 *        - PENDING   (no log exists yet for today)        -> shown on top
 *        - COMPLETED (any persisted outcome)              -> shown after a divider
 *
 * MOBILE-FIRST NOTE
 * -----------------
 * This page is designed for one-handed phone use. The view uses segmented
 * radio buttons rather than dropdowns so each outcome is one tap away.
 *
 * DATA SHAPE
 * ----------
 * $logData = [
 *     <habit_id> => [
 *         'habit_name'    => string,
 *         'unit_name'     => string,
 *         'habit_unit_id' => int,
 *         'log_id'        => ?int      // null when no row exists yet
 *         'outcome'       => 'not_done'|'yes'|'no'|'not_possible',
 *         'value'         => ?numeric,
 *         'log_time'      => ?string   // 'Y-m-d\TH:i' for <input type=datetime-local>
 *         'notes'         => ?string,
 *     ],
 *     ...
 * ]
 */
class Index extends Component
{
    /** Outcome that means "user has not interacted with this habit today" - never written to DB. */
    public const OUTCOME_NOT_DONE = 'not_done';

    /** All outcomes that DO get persisted (everything except the placeholder). */
    public const PERSISTED_OUTCOMES = ['yes', 'no', 'not_possible'];

    /** The date the user is viewing/logging for (Y-m-d). */
    public $log_date;

    /** Per-habit form state. See class docblock for shape. */
    public $logData = [];

    // -----------------------------------------------------------------
    // Lifecycle
    // -----------------------------------------------------------------

    public function mount()
    {
        $this->log_date = Carbon::today()->toDateString();
        $this->loadLogData();
    }

    // -----------------------------------------------------------------
    // Date navigation
    // -----------------------------------------------------------------

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

    // -----------------------------------------------------------------
    // Data loading
    // -----------------------------------------------------------------

    /**
     * Build $logData by joining the user's active habits (left) with any
     * already-persisted logs for the selected date (right).
     *
     * If a log row exists, its outcome is shown; otherwise the row starts in
     * the placeholder state OUTCOME_NOT_DONE.
     */
    protected function loadLogData(): void
    {
        $habits = PresentHabits::with('unit')
            ->where('status', 'active')
            ->orderBy('sort_order', 'asc')
            ->get();

        // Key existing logs by habit_id for O(1) lookup during the merge below.
        $existingLogs = PresentLogs::where('log_date', $this->log_date)
            ->where('created_by', Auth::id())
            ->get()
            ->keyBy('habit_id');

        $this->logData = [];

        foreach ($habits as $habit) {
            $log = $existingLogs->get($habit->habit_id);

            $this->logData[$habit->habit_id] = [
                // Display-only fields
                'habit_name'    => $habit->habit_name,
                'unit_name'     => $habit->unit->name ?? 'N/A',
                'habit_unit_id' => $habit->unit_id,

                // Editable fields (pre-filled if a log exists)
                'log_id'   => $log->log_id ?? null,
                'outcome'  => $log->outcome ?? self::OUTCOME_NOT_DONE,
                'value'    => $log->value ?? null,
                'log_time' => ! empty($log?->log_time)
                    ? Carbon::parse($log->log_time)->format('Y-m-d\TH:i')
                    : null,
                'notes'    => $log->notes ?? null,
            ];
        }
    }

    // -----------------------------------------------------------------
    // Validation
    // -----------------------------------------------------------------

    /**
     * Built per-habit because the row-keyed structure ($logData.<id>.field)
     * means the rule set is dynamic, dependent on what habits the user has.
     */
    protected function rules(): array
    {
        // The placeholder is allowed as input but never written to DB - see saveLogs().
        $allowedOutcomes = array_merge([self::OUTCOME_NOT_DONE], self::PERSISTED_OUTCOMES);

        $rules = [];
        foreach ($this->logData as $habitId => $_) {
            $rules["logData.$habitId.outcome"]  = 'required|in:' . implode(',', $allowedOutcomes);
            $rules["logData.$habitId.value"]    = 'nullable|numeric';
            $rules["logData.$habitId.log_time"] = 'nullable|date_format:Y-m-d\TH:i';
            $rules["logData.$habitId.notes"]    = 'nullable|string|max:1000';
        }
        return $rules;
    }

    // -----------------------------------------------------------------
    // Persistence
    // -----------------------------------------------------------------

    /**
     * Save the user's selections for every visible habit in one transaction-ish
     * pass. Three branches per row:
     *
     *   A) outcome === not_done  AND  no log row exists  -> NO-OP
     *   B) outcome === not_done  AND  a log row exists   -> SOFT DELETE
     *      (user is "undoing" a prior save; SoftDeletes trait preserves history)
     *   C) outcome is yes/no/not_possible                -> UPSERT
     */
    public function saveLogs(): void
    {
        $this->validate();

        $userId   = Auth::id();
        $upserts  = 0;
        $deletes  = 0;

        foreach ($this->logData as $habitId => $data) {
            $outcome      = $data['outcome'];
            $existingLogId = $data['log_id'] ?? null;

            // --- Branch B: revert-to-not-done removes the row ---
            if ($outcome === self::OUTCOME_NOT_DONE) {
                if ($existingLogId) {
                    $log = PresentLogs::find($existingLogId);
                    if ($log) {
                        $log->deleted_by = $userId;
                        $log->save();
                        $log->delete(); // soft delete via the SoftDeletes trait
                        $deletes++;
                    }
                }
                // Branch A: nothing existed, nothing to do.
                continue;
            }

            // --- Branch C: persist (upsert) ---
            $payload = [
                'habit_id'   => $habitId,
                'outcome'    => $outcome,
                'value'      => $data['value'] === '' ? null : $data['value'],
                'log_date'   => $this->log_date,
                'notes'      => $data['notes'] === '' ? null : $data['notes'],
                'log_time'   => ! empty($data['log_time'])
                    ? Carbon::createFromFormat('Y-m-d\TH:i', $data['log_time'])
                    : null,
                'created_by' => $userId,
            ];

            if ($existingLogId) {
                PresentLogs::where('log_id', $existingLogId)
                    ->update($payload + ['updated_by' => $userId]);
            } else {
                PresentLogs::create($payload);
            }
            $upserts++;
        }

        // Reload so the view reflects:
        //   - newly assigned log_ids on freshly created rows
        //   - cleared rows now lacking a log_id (re-grouped as Pending)
        $this->loadLogData();

        $human = trim(
            ($upserts ? "{$upserts} saved" : '')
            . ($upserts && $deletes ? ', ' : '')
            . ($deletes ? "{$deletes} cleared" : '')
        );
        $human = $human ?: 'No changes';

        session()->flash(
            'success',
            "{$human} for " . Carbon::parse($this->log_date)->format('M d, Y') . '.'
        );
    }

    // -----------------------------------------------------------------
    // Rendering
    // -----------------------------------------------------------------

    public function render()
    {
        // Defensive: if Livewire ever renders before mount() loaded the data.
        if (empty($this->logData)) {
            $this->loadLogData();
        }

        // Split into two ordered groups for the two-section view.
        // We iterate $logData (already in sort_order) so both groups preserve
        // habit sort order without re-sorting.
        $pendingHabitIds   = [];
        $completedHabitIds = [];

        foreach ($this->logData as $habitId => $row) {
            // A habit is "completed for the day" once it has a saved log row,
            // regardless of whether the outcome was yes / no / not_possible.
            if (! empty($row['log_id'])) {
                $completedHabitIds[] = $habitId;
            } else {
                $pendingHabitIds[] = $habitId;
            }
        }

        return view('present::livewire.logs.index', [
            'pendingHabitIds'      => $pendingHabitIds,
            'completedHabitIds'    => $completedHabitIds,
            'currentDateFormatted' => Carbon::parse($this->log_date)->format('l, F jS, Y'),
            'isToday'              => Carbon::parse($this->log_date)->isToday(),
        ]);
    }
}
