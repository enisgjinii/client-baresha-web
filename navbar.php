<?php
// Get user information from session if available
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'User';
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container-fluid">
        <!-- Sidebar Toggle Button -->
        <button class="navbar-toggler d-md-none collapsed me-2" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Brand Logo -->
        <a class="navbar-brand me-0 me-md-2 d-flex align-items-center" href="dashboard.php">
            <span class="text-primary fw-bold ms-2">CRM System</span>
        </a>

        <!-- Search Form -->
        <form class="d-none d-md-flex me-auto ms-3">
            <div class="input-group">
                <input class="form-control form-control-sm" type="search" placeholder="Search..." aria-label="Search">
                <button class="btn btn-outline-primary btn-sm" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>

        <!-- Navbar Right Side -->
        <ul class="navbar-nav">
            <!-- Notifications Dropdown -->
            <li class="nav-item dropdown">
                <a class="nav-link position-relative" href="#" id="navbarDropdownNotifications" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        3
                        <span class="visually-hidden">unread notifications</span>
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownNotifications">
                    <li>
                        <h6 class="dropdown-header">Notifications Center</h6>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="#">
                            <div class="me-3">
                                <i class="bi bi-person-plus text-primary"></i>
                            </div>
                            <div>
                                <p class="mb-0">New client registered</p>
                                <small class="text-muted">15 minutes ago</small>
                            </div>
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="#">
                            <div class="me-3">
                                <i class="bi bi-cash text-success"></i>
                            </div>
                            <div>
                                <p class="mb-0">New payment received</p>
                                <small class="text-muted">2 hours ago</small>
                            </div>
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="#">
                            <div class="me-3">
                                <i class="bi bi-exclamation-triangle text-warning"></i>
                            </div>
                            <div>
                                <p class="mb-0">Task deadline approaching</p>
                                <small class="text-muted">1 day ago</small>
                            </div>
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item text-center small text-gray-500" href="#">Show All Notifications</a>
                    </li>
                </ul>            </li>

            <!-- Dark Mode Toggle -->
            <li class="nav-item">
                <button id="theme-toggle" class="btn nav-link px-2" title="Toggle Dark Mode">
                    <i id="theme-icon" class="bi bi-moon"></i>
                </button>
            </li>

            <!-- Messages Dropdown -->
            <li class="nav-item dropdown mx-1">
                <a class="nav-link position-relative" href="#" id="navbarDropdownMessages" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-envelope"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        7
                        <span class="visually-hidden">unread messages</span>
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMessages">
                    <li>
                        <h6 class="dropdown-header">Message Center</h6>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="#">
                            <div class="dropdown-list-image me-3">
                                <img class="rounded-circle" src="https://via.placeholder.com/60" alt="User Avatar" width="40" height="40">
                                <div class="status-indicator bg-success"></div>
                            </div>
                            <div>
                                <p class="mb-0">Hi there! I'm wondering about...</p>
                                <small class="text-muted">Jane Doe · 58m</small>
                            </div>
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="#">
                            <div class="dropdown-list-image me-3">
                                <img class="rounded-circle" src="https://via.placeholder.com/60" alt="User Avatar" width="40" height="40">
                                <div class="status-indicator"></div>
                            </div>
                            <div>
                                <p class="mb-0">I need an update on the project...</p>
                                <small class="text-muted">John Smith · 1d</small>
                            </div>
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="#">
                            <div class="dropdown-list-image me-3">
                                <img class="rounded-circle" src="https://via.placeholder.com/60" alt="User Avatar" width="40" height="40">
                                <div class="status-indicator bg-warning"></div>
                            </div>
                            <div>
                                <p class="mb-0">The invoices for last month are ready...</p>
                                <small class="text-muted">Sarah Johnson · 2d</small>
                            </div>
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item text-center small text-gray-500" href="#">Read More Messages</a>
                    </li>
                </ul>
            </li>

            <!-- User Profile Dropdown -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUser" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="d-none d-md-inline me-1"><?php echo htmlspecialchars($user_email); ?></span>
                    <img class="rounded-circle" src="https://via.placeholder.com/40" alt="User Avatar" width="32" height="32">
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUser">
                    <li>
                        <a class="dropdown-item" href="profile.php">
                            <i class="bi bi-person me-2"></i>
                            Profile
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="company.php">
                            <i class="bi bi-building me-2"></i>
                            Company
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item" href="logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>
                            Sign Out
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
