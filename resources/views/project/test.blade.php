@extends('project.layouts.app') 

{{-- Use the @section directly in the file to define the title --}}
@section('title', 'Project')

@section('project_content')
    <div class="mt-3">
        <h1>Hello from Project Page</h1>
        <hr>
        {{-- Display current IST time --}}
        @php
            date_default_timezone_set('Asia/Kolkata');
            $currentTime = date('Y-m-d H:i:s');
        @endphp

        <p>Current Server Time (IST): {{ $currentTime }}</p>
    </div>
@endsection