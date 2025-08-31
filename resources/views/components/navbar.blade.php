<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand" href="/account">
            <i class="bi bi-cpu-fill"></i> FirmwareHub
        </a>
        
        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Navigation -->
        <div class="collapse navbar-collapse" id="navbarMain">
            <!-- Left Menu -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('account') ? 'active' : '' }}" href="/account">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('products*') ? 'active' : '' }}" href="/products">
                        <i class="bi bi-grid"></i> Browse
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('account/downloads*') ? 'active' : '' }}" href="/account/downloads">
                        <i class="bi bi-download"></i> Downloads
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('account/subscription*') ? 'active' : '' }}" href="/account/subscription">
                        <i class="bi bi-credit-card"></i> Subscription
                    </a>
                </li>
            </ul>
            
            <!-- Right Menu -->
            <div class="d-flex align-items-center">
                <!-- Search -->
                <form class="d-flex me-3" action="/search" method="GET">
                    <div class="input-group">
                        <input class="form-control form-control-sm" type="search" name="q" 
                               placeholder="Search firmware..." aria-label="Search">
                        <button class="btn btn-outline-secondary btn-sm" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
                
                <!-- Cart -->
                <a href="/cart" class="btn btn-link text-dark position-relative me-2">
                    <i class="bi bi-cart3 fs-5"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ session('cart_count', 0) }}
                    </span>
                </a>
                
                <!-- Notifications -->
                <div class="dropdown me-3">
                    <button class="btn btn-link text-dark position-relative" data-bs-toggle="dropdown">
                        <i class="bi bi-bell fs-5"></i>
                        @if(session('unread_notifications', 0) > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ session('unread_notifications') }}
                            </span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">Notifications</h6></li>
                        <li><a class="dropdown-item" href="#">No new notifications</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/account/notifications">View all</a></li>
                    </ul>
                </div>
                
                <!-- User Menu -->
                <div class="dropdown">
                    <button class="btn btn-link text-dark d-flex align-items-center" data-bs-toggle="dropdown">
                        @if(session('user_avatar'))
                            <img src="{{ session('user_avatar') }}" alt="Avatar" 
                                 class="rounded-circle me-2" width="32" height="32">
                        @else
                            <i class="bi bi-person-circle fs-5 me-2"></i>
                        @endif
                        <span class="d-none d-md-inline">{{ session('user_name', 'User') }}</span>
                        <i class="bi bi-chevron-down ms-2"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <div class="px-3 py-2">
                                <div class="fw-bold">{{ session('user_name', 'User') }}</div>
                                <div class="text-muted small">{{ session('user_email', '') }}</div>
                                @if(\App\Helpers\AuthHelper::hasSubscription())
                                    <span class="badge badge-subscription mt-1">PRO Member</span>
                                @endif
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="/account/profile">
                                <i class="bi bi-person"></i> My Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="/account/orders">
                                <i class="bi bi-bag"></i> My Orders
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="/account/wishlist">
                                <i class="bi bi-heart"></i> Wishlist
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="/account/settings">
                                <i class="bi bi-gear"></i> Settings
                            </a>
                        </li>
                        @if(\App\Helpers\AuthHelper::isAdmin())
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="/admin">
                                    <i class="bi bi-shield-lock"></i> Admin Panel
                                </a>
                            </li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="/logout">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>