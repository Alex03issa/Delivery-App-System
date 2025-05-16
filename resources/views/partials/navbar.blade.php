<nav class="navbar navbar-dark navbar-expand-lg fixed-top bg-dark" id="mainNav">
    <div class="container">
        <a class="navbar-brand" href="/"><i class="fa fa-box"></i>&nbsp;Cabs Online</a>
        <button data-bs-toggle="collapse" data-bs-target="#navbarResponsive"
                class="navbar-toggler navbar-toggler-right" type="button"
                aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fa fa-bars"></i>
        </button>

        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ms-auto text-uppercase">
                <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="/about">About</a></li>
                <li class="nav-item"><a class="nav-link" href="/#about">Services</a></li>

                @auth
                    @if(Auth::user()->role === 'client')
                        <li class="nav-item" style="margin-top: 10px;">
                            <a class="btn btn-primary" role="button"
                               style="background: rgba(10,9,8,0.27);" href="/booking">
                                Request Delivery
                            </a>
                        </li>
                    @endif

                    <!-- Profile dropdown for logged-in users -->
                    <li class="nav-item dropdown" style="margin-left: 15px;">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown"
                           role="button" data-bs-toggle="dropdown" aria-expanded="false">
                           <img src="{{ Auth::user()->profile_picture ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=0D8ABC&color=fff&size=128' }}"
                                alt="Profile" class="rounded-circle" width="35" height="35"
                                style="object-fit: cover; margin-right: 10px;">

                            {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end bg-dark" aria-labelledby="navbarDropdown">
                            @php
                                $role = Auth::user()->role;
                                $dashboardRoute = $role === 'admin' ? 'admin.dashboard' :
                                                  ($role === 'client' ? 'client.dashboard' : 'driver.dashboard');
                            @endphp
                            <li>
                                <a class="dropdown-item text-light" href="{{ route($dashboardRoute) }}">Dashboard</a>
                            </li>
                            <li><hr class="dropdown-divider bg-secondary"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-light">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <!-- These show ONLY when not logged in -->
                    <li class="nav-item" style="margin-top: 10px;">
                        <a class="btn btn-primary btn-book" role="button" href="{{ route('register') }}?role=driver">Become A Courier</a>
                    </li>
                    <li class="nav-item" style="margin-top: 10px;">
                        <a class="btn btn-primary btn-login" role="button" href="/login"
                           style="background: rgb(99,168,231);">Login</a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
