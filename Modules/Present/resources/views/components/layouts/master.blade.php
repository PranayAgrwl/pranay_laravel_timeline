@extends('layouts.app')

{{-- This section passes the title block from the module's view (child) directly to the main layout --}}
@section('title')
    @yield('title') 
@endsection

{{-- This section passes the main content block from the module's view (child) directly to the main layout --}}
@section('content')
    @include('present::partials.navbar') 
    @livewireStyles
    <main class="container-fluid">
        @yield('present_content')
    </main>
@endsection

{{-- This section passes the scripts block from the module's view (child) directly to the main layout --}}
@section('scripts')
    @livewireScripts
    @yield('scripts')
@endsection