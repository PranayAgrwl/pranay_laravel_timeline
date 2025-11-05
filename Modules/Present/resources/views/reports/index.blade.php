{{-- Modules/Present/resources/views/reports/index.blade.php --}}

@extends('present::components.layouts.master')

@section('title', 'Present - Report')

@section('present_content')
<div class="mt-3">
    <livewire:present::report.index />
</div>
@endsection