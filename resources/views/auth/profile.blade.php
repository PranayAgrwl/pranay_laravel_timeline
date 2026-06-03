@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 text-center mb-4 pb-2 border-bottom">Edit Profile</h1>

                    {{-- Success flash from ProfileController::update() --}}
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PATCH')

                        {{-- Name --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input id="name" type="text" name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $user->name) }}"
                                required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Email --}}
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input id="email" type="email" name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $user->email) }}"
                                required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <hr class="my-4">

                        {{-- New password (optional) --}}
                        <p class="text-muted small mb-3">
                            Leave the password fields empty to keep your current password.
                        </p>

                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input id="password" type="password" name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                autocomplete="new-password">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                            <input id="password_confirmation" type="password" name="password_confirmation"
                                class="form-control"
                                autocomplete="new-password">
                        </div>

                        <hr class="my-4">

                        {{--
                            Current password gate: any change (name, email, or password)
                            requires re-confirming the existing password. Lives at the
                            bottom of the form to emphasise that it authorises the save.
                        --}}
                        <div class="mb-4">
                            <label for="current_password" class="form-label fw-semibold">
                                Current Password <span class="text-danger">*</span>
                            </label>
                            <input id="current_password" type="password" name="current_password"
                                class="form-control @error('current_password') is-invalid @enderror"
                                autocomplete="current-password"
                                required>
                            <div class="form-text">Required to confirm any change.</div>
                            @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-dark btn-lg">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
