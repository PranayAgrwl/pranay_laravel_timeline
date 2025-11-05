<style>
    .breadcrumb a {
        color: #495057 !important;
        text-decoration: none !important;
    }
</style>

<nav style="--bs-breadcrumb-divider: '|';" class="bg-body-tertiary px-3 py-1">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('present.index') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('present.units.index') }}">Units</a></li>
        <li class="breadcrumb-item"><a href="{{ route('present.habits.index') }}">Habits</a></li>
        <li class="breadcrumb-item"><a href="{{ route('present.logs.index') }}">Logs</a></li>
        <li class="breadcrumb-item"><a href="{{ route('present.report.index') }}">Report</a></li>
    </ol>
</nav>