<style>
    .breadcrumb a {
        color: #495057 !important;
        text-decoration: none !important;
    }
</style>

<nav style="--bs-breadcrumb-divider: '|';" class="bg-body-tertiary px-3 py-1">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('test') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('test') }}">Products</a></li> 
    </ol>
</nav>