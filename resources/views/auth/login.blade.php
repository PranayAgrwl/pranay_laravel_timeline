@extends('layouts.app')

@section('title', 'Login') 

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0">
                <div class="card-body p-4">
                    <h1 class="h4 text-center mb-4 pb-2 border-bottom">Login</h1>
                    
                    <form method="POST" action="{{ route('login') }}">
                        @csrf 

                        {{-- Email Field --}}
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" 
                                class="form-control form-control-lg" 
                                placeholder="Email Address"
                                required autofocus>

                            @error('email') 
                                <div class="text-danger small mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Password Field --}}
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input id="password" type="password" name="password" 
                                class="form-control form-control-lg" 
                                placeholder="Password"
                                required>

                            @error('password') 
                                <div class="text-danger small mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Submit Button --}}
                        <div class="d-grid">
                            <button type="submit" class="btn btn-dark btn-lg">
                                Log in
                            </button>
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection