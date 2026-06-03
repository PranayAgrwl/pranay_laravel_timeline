<style>
    /* Custom CSS for Hover Dropdown (Desktop Only) */
@media (min-width: 992px) { /* Applies to 'lg' breakpoint and above */
    .dropdown-hover-only:hover > .dropdown-menu {
        display: block;
        margin-top: 0; /* Keep the dropdown right under the nav link */
    }
}
</style>

<nav class="navbar navbar-expand-lg bg-dark sticky-top" data-bs-theme="dark">
    <div class="container-fluid">
        <a class="navbar-brand text-light fw-bold" href="/">Timeline</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbarCollapse">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{ route('home') }}">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{ route('present.index') }}">Present</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{ route('test') }}">Project</a>
                </li>

                <li class="nav-item dropdown dropdown-hover-only">
                    <a class="nav-link dropdown-toggle text-white" href="{{ route('home') }}" id="otherLinksDropdown" role="button" data-bs-toggle="dropdown">
                        Other Links
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark">
                        <li><a class="dropdown-item" href="{{ route('home') }}">Settings</a></li>
                        <li><a class="dropdown-item" href="{{ route('home') }}">Contact</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('home') }}">FAQ</a></li>
                    </ul>
                </li>
            </ul>

            @auth
            <div class="ms-lg-auto d-flex align-items-center gap-2">
                <span class="navbar-text text-light me-2">
                    Welcome, {{ auth()->user()->name }}
                </span>

                {{-- Edit Profile - placed BEFORE Logout per spec --}}
                <a href="{{ route('profile.edit') }}" class="btn btn-outline-light">
                    <!-- <i class="bi bi-person-gear me-1"></i> -->
                    Edit Profile
                </a>

                {{-- Logout uses POST so it isn't triggerable via GET (CSRF safety) --}}
                <form action="{{ route('logout') }}" method="POST" class="d-flex m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-light">
                        <!-- <i class="bi bi-box-arrow-right me-1"></i> -->
                        Logout
                    </button>
                </form>
            </div>
            @endauth
        </div>
    </div>
</nav>