{{-- Modules/Present/resources/views/livewire/habits/index.blade.php (Bootstrap 5) --}}

<div class="card p-3 shadow-sm" x-data="{ open: @entangle('showCreateForm') }">
    <h2 class="card-title h3 mb-4">Habit List Management</h2>

    {{-- Notification Message --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    {{-- Button to Toggle Form --}}
    <button wire:click="toggleCreateForm" class="btn btn-success mb-4 fw-semibold">
        {{ $showCreateForm ? 'Cancel Creation' : '+ Create New Habit' }}
    </button>

    {{-- The Create Form (Conditional Display) --}}
    @if ($showCreateForm)
        <div class="p-4 border border-info rounded mb-4">
            <h3 class="h5 mb-3">Add New Habit</h3>
            
            <form wire:submit.prevent="save"> 
                
                {{-- Habit Name Field --}}
                <div class="mb-3">
                    <label for="habit_name" class="form-label">Habit Name</label>
                    <input type="text" id="habit_name" wire:model.live="habit_name" 
                           class="form-control @error('habit_name') is-invalid @enderror">
                    @error('habit_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Unit ID Field (Select Dropdown) --}}
                <div class="mb-3">
                    <label for="unit_id" class="form-label">Associated Unit</label>
                    <select id="unit_id" wire:model="unit_id" 
                            class="form-select @error('unit_id') is-invalid @enderror">
                        <option value="">-- Select Unit --</option>
                        {{-- $units is passed from the Livewire component render method --}}
                        @foreach($units as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Status Field --}}
                <div class="mb-3 row">
                    <div class="col-md-6">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" wire:model="status" 
                                class="form-select @error('status') is-invalid @enderror">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    {{-- Private Field --}}
                    <div class="col-md-6">
                        <label for="private" class="form-label">Visibility</label>
                        <select id="private" wire:model="private" 
                                class="form-select @error('private') is-invalid @enderror">
                            <option value="open">Open</option>
                            <option value="private">Private</option>
                        </select>
                        @error('private') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Sort Order Field 
                <div class="mb-3">
                    <label for="sort_order" class="form-label">Sort Order (Optional)</label>
                    <input type="number" id="sort_order" wire:model="sort_order" 
                           class="form-control @error('sort_order') is-invalid @enderror">
                    @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div> --}}

                {{-- Notes Field --}}
                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea id="notes" wire:model="notes" rows="2" 
                              class="form-control @error('notes') is-invalid @enderror"></textarea>
                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                {{-- Submit Button --}}
                <button type="submit" class="btn btn-primary fw-semibold"
                    wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">Save Habit</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </form>
        </div>
    @endif

    {{-- The Edit Form (Conditional Display) --}}
    @if ($editHabitId)
        <div class="p-4 border border-warning rounded mb-4">
            <h3 class="h5 mb-3">Edit Habit #{{ $editHabitId }}</h3>
            
            <form wire:submit.prevent="updateHabit"> 
                
                {{-- Edit Habit Name Field (and other fields, repeating the structure above) --}}
                <div class="mb-3">
                    <label for="edit-habit_name" class="form-label">Habit Name</label>
                    <input type="text" id="edit-habit_name" wire:model.live="habit_name" 
                           class="form-control @error('habit_name') is-invalid @enderror">
                    @error('habit_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Edit Unit ID Field --}}
                <div class="mb-3">
                    <label for="edit-unit_id" class="form-label">Associated Unit</label>
                    <select id="edit-unit_id" wire:model="unit_id" 
                            class="form-select @error('unit_id') is-invalid @enderror">
                        <option value="">-- Select Unit --</option>
                        @foreach($units as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Edit Status/Private Fields --}}
                <div class="mb-3 row">
                    <div class="col-md-6">
                        <label for="edit-status" class="form-label">Status</label>
                        <select id="edit-status" wire:model="status" 
                                class="form-select @error('status') is-invalid @enderror">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label for="edit-private" class="form-label">Visibility</label>
                        <select id="edit-private" wire:model="private" 
                                class="form-select @error('private') is-invalid @enderror">
                            <option value="open">Open</option>
                            <option value="private">Private</option>
                        </select>
                        @error('private') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Edit Sort Order Field
                <div class="mb-3">
                    <label for="edit-sort_order" class="form-label">Sort Order (Optional)</label>
                    <input type="number" id="edit-sort_order" wire:model="sort_order" 
                           class="form-control @error('sort_order') is-invalid @enderror">
                    @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div> --}}

                {{-- Edit Notes Field --}}
                <div class="mb-3">
                    <label for="edit-notes" class="form-label">Notes</label>
                    <textarea id="edit-notes" wire:model="notes" rows="2" 
                              class="form-control @error('notes') is-invalid @enderror"></textarea>
                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                {{-- Update and Cancel Buttons --}}
                <button type="submit" class="btn btn-warning fw-semibold"
                    wire:loading.attr="disabled" wire:target="updateHabit">
                    <span wire:loading.remove wire:target="updateHabit">Save Changes</span>
                    <span wire:loading wire:target="updateHabit">Saving...</span>
                </button>
                <button type="button" wire:click="cancelEdit" class="btn btn-secondary ms-2">
                    Cancel
                </button>
            </form>
        </div>
    @endif
    
    <hr class="my-4">

    {{-- Habit List Table --}}
    <table class="table table-hover table-striped"> 
        <thead>
            <tr>
                <th scope="col">Order</th>
                <th scope="col">Habit Name</th>
                <th scope="col">Unit</th>
                <!-- <th scope="col">Visibility</th> -->
                <th scope="col">Status</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            {{-- Loop over $habits, which is passed from the render method --}}
            @foreach ($habits as $habit)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $habit->habit_name }}</td>
                    <td>{{ $habit->unit->name ?? 'N/A' }}</td>
                    <!-- <td>{{ $habit->private }}</td> -->
                    <!-- <td>{{ $habit->status }}</td> -->
                    <td>
                        @if ($habit->status === 'active')
                            <span class="badge text-bg-success">Active</span>
                        @else
                            <span class="badge text-bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td class="text-nowrap">

                        {{-- Edit Button --}}
                        <button class="btn btn-sm btn-outline-primary ms-2"
                            wire:click="editHabit({{ $habit->habit_id }})">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        
                        {{-- Delete Button --}}
                        <button 
                            class="btn btn-sm btn-outline-danger ms-2"
                            wire:click="deleteHabit({{ $habit->habit_id }})"
                            wire:confirm="Are you sure you want to delete the habit: {{ $habit->habit_name }}?">
                            <i class="bi bi-trash"></i>
                        </button>

                        <span class="ms-3"></span>
                        
                        {{-- MOVE UP Button: Hidden if it's the first item --}}
                        @if ($habit->sort_order != $minSortOrder)
                            <button class="btn btn-sm btn-outline-secondary"
                                wire:click="moveUp({{ $habit->habit_id }})"
                                title="Move Up (Sort Order: {{ $habit->sort_order }})">
                                <i class="bi bi-caret-up-fill"></i>
                            </button>
                        @endif

                        {{-- MOVE DOWN Button: Hidden if it's the last item --}}
                        @if ($habit->sort_order != $maxSortOrder)
                            <button 
                                class="btn btn-sm btn-outline-secondary"
                                wire:click="moveDown({{ $habit->habit_id }})"
                                title="Move Down (Sort Order: {{ $habit->sort_order }})">
                                <i class="bi bi-caret-down-fill"></i>
                            </button>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>