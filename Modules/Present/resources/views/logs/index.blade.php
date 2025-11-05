{{-- Modules/Present/resources/views/logs/index.blade.php --}}

@extends('present::components.layouts.master')

@section('title', 'Present - Logs')

@section('present_content')
<div class="mt-3">
    <livewire:present::logs.index /> 
</div>
@endsection