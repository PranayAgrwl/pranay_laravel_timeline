{{-- Modules/Present/resources/views/habits/index.blade.php --}}

@extends('present::components.layouts.master')

@section('title', 'Present - Habits')

@section('present_content')
<div class="mt-3">
    <livewire:present::habits.index /> 
</div>
@endsection