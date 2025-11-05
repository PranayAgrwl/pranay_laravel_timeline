{{-- Modules/Present/resources/views/livewire/unit/index.blade.php (Bootstrap 5) --}}

{{-- Card container and Alpine.js for form toggle --}}
<div class="card p-3 shadow-sm" x-data="{ open: @entangle('showCreateForm') }">
    <h2 class="card-title h3 mb-4">Unit Management</h2>

    {{-- Notification Message (Tailwind bg-green-100/400/text-700 converts to Bootstrap alert-success) --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    {{-- Button to Toggle Form (Tailwind bg-green-500 converts to Bootstrap btn-success) --}}
    <button wire:click="toggleCreateForm" class="btn btn-success mb-4 fw-semibold">
        {{ $showCreateForm ? 'Cancel Creation' : '+ Create New Unit' }}
    </button>

    {{-- The Create Form (Conditional Display) --}}
    @if ($showCreateForm)
        <div class="p-4 border border-info rounded mb-4">
            <h3 class="h5 mb-3">Add New Unit</h3>
            
            {{-- wire:submit.prevent ensures no full page reload --}}
            <form wire:submit.prevent="save"> 
                
                {{-- Name Field (Bootstrap Form Control) --}}
                <div class="mb-3">
                    <label for="name" class="form-label">Unit Name</label>
                    {{-- The .form-control class handles w-full, padding, borders, and shadows --}}
                    <input type="text" id="name" wire:model.live="name" 
                           class="form-control @error('name') is-invalid @enderror">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Status Field (Bootstrap Form Control) --}}
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" wire:model="status" 
                            class="form-select @error('status') is-invalid @enderror">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Notes0 Field (Bootstrap Form Control) --}}
                <div class="mb-3">
                    <label for="notes0" class="form-label">Notes 0</label>
                    <textarea id="notes0" wire:model="notes0" rows="2" 
                              class="form-control @error('notes0') is-invalid @enderror"></textarea>
                    @error('notes0') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                {{-- Notes1 Field (Bootstrap Form Control) --}}
                <div class="mb-3">
                    <label for="notes1" class="form-label">Notes 1</label>
                    <textarea id="notes1" wire:model="notes1" rows="2" 
                              class="form-control @error('notes1') is-invalid @enderror"></textarea>
                    @error('notes1') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Submit Button (Tailwind bg-blue-500 converts to Bootstrap btn-primary) --}}
                <button type="submit" class="btn btn-primary fw-semibold"
                    wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">Save Unit</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </form>
        </div>
    @endif

    {{-- The Edit Form (Conditional Display) --}}
    {{-- This form appears ONLY if editUnitId is set --}}
    @if ($editUnitId)
        <div class="p-4 border border-warning rounded mb-4">
            <h3 class="h5 mb-3">Edit Unit #{{ $editUnitId }}</h3>
            
            <form wire:submit.prevent="updateUnit"> 
                
                {{-- Name Field --}}
                <div class="mb-3">
                    <label for="edit-name" class="form-label">Unit Name</label>
                    <input type="text" id="edit-name" wire:model.live="name" 
                           class="form-control @error('name') is-invalid @enderror">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Status Field --}}
                <div class="mb-3">
                    <label for="edit-status" class="form-label">Status</label>
                    <select id="edit-status" wire:model="status" 
                            class="form-select @error('status') is-invalid @enderror">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Notes0 Field --}}
                <div class="mb-3">
                    <label for="edit-notes0" class="form-label">Notes 0</label>
                    <textarea id="edit-notes0" wire:model="notes0" rows="2" 
                              class="form-control @error('notes0') is-invalid @enderror"></textarea>
                    @error('notes0') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Notes1 Field --}}
                <div class="mb-3">
                    <label for="edit-notes1" class="form-label">Notes 1</label>
                    <textarea id="edit-notes1" wire:model="notes1" rows="2" 
                              class="form-control @error('notes1') is-invalid @enderror"></textarea>
                    @error('notes1') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                {{-- Update and Cancel Buttons --}}
                <button type="submit" class="btn btn-warning fw-semibold"
                    wire:loading.attr="disabled" wire:target="updateUnit">
                    <span wire:loading.remove wire:target="updateUnit">Save Changes</span>
                    <span wire:loading wire:target="updateUnit">Saving...</span>
                </button>
                <button type="button" wire:click="cancelEdit" class="btn btn-secondary ms-2">
                    Cancel
                </button>
            </form>
        </div>
    @endif
    
    <hr class="my-4">

    {{-- Unit List Table (Bootstrap Table) --}}
    {{-- w-full text-left border-collapse converts to table table-hover --}}
    <table class="table table-hover table-striped"> 
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Name</th>
                <th scope="col">Status</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($units as $unit)
                <tr>
                    <td>{{ $unit->unit_id }}</td>
                    <td>{{ $unit->name }}</td>
                    <!-- <td>{{ $unit->status }}</td> -->
                    <td>
                        @if ($unit->status === 'active')
                            <span class="badge text-bg-success">Active</span>
                        @else
                            <span class="badge text-bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>
                        {{-- Edit/Delete Buttons --}}
                        <button class="btn btn-sm btn-outline-primary"
                            wire:click="editUnit({{ $unit->unit_id }})">
                            Edit
                        </button>
                        <button 
                            class="btn btn-sm btn-outline-danger ms-2"
                            wire:click="deleteUnit({{ $unit->unit_id }})"
                            wire:confirm="Are you sure you want to delete the unit: {{ $unit->name }}?">
                            Delete
                        </button>                        
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>