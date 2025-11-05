{{-- Modules/Present/resources/views/units/index.blade.php --}}

@extends('present::components.layouts.master')

@section('title', 'Present - Unit')

@section('present_content')
<div class="mt-3">
    <livewire:present::unit.index />
</div>
@endsection