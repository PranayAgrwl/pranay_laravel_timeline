@extends('layouts.app') 
@section('content')
    @include('project.partials.navbar') 
    
    <main class="container-fluid">
        @yield('project_content') 
    </main>
@endsection