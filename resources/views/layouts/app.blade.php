<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    @vite(['resources/js/app.js', 'resources/css/app.css'])
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    {{-- This is where the page title from the child view will go --}}
    <title>Timeline - @yield('title', 'Welcome')</title> 
</head>
<body>

    <header>
        @include('partials.navbar')
    </header>

    <main>
        @yield('content') 
    </main>

    <footer>
        <!-- <p>&copy; {{ date('Y') }} Pranay Agrawal. All rights reserved.</p> -->
    </footer>

    {{-- This is where custom JavaScript for the page goes --}}
    @yield('scripts')

</body>
</html>