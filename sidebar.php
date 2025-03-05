<?php
session_start();
$navItems = [
    ['title' => 'Dashboard', 'icon' => 'bi-speedometer2', 'url' => 'dashboard.php'],
    ['title' => 'Calendar', 'icon' => 'bi-calendar-week', 'url' => 'calendar.php'],
    ['title' => 'Invoices', 'icon' => 'bi-receipt', 'url' => 'invoices.php'],
    ['title' => 'Reports', 'icon' => 'bi-bar-chart', 'url' => 'reports.php'],
    ['title' => 'Help Center', 'icon' => 'bi-question-circle', 'url' => 'help-center.php'],
    ['title' => 'General Contract', 'icon' => 'bi-file-earmark-medical', 'url' => 'general-contract.php'],
    ['title' => 'Song Contract', 'icon' => 'bi-file-earmark-music', 'url' => 'song-contract.php'],
    
];
$currentPage = basename($_SERVER['PHP_SELF']);
$currentUserName = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "User";
$sidebarCollapsed = isset($_COOKIE['sidebar_collapsed']) ? $_COOKIE['sidebar_collapsed'] === 'true' : false;
$sidebarClass = $sidebarCollapsed ? 'sidebar-collapsed' : '';
?>
<div class="col-auto p-0 sidebar <?php echo $sidebarClass; ?>" id="sidebar">
    <div class="p-2">
        <div class="d-flex align-items-center mb-3 justify-content-between">
            <div class="d-flex align-items-center">
                <i class="bi bi-grid-3x3-gap-fill text-primary fs-5 me-1"></i>
                <span class="fw-bold text-primary sidebar-text brand-name">bareshaNetwork</span>
            </div>
            <button class="btn btn-sm toggle-sidebar p-1" id="sidebarCollapseBtn">
                <i class="bi bi-chevron-double-left"></i>
            </button>
        </div>
        <ul class="nav flex-column">
            <?php foreach ($navItems as $item): ?>
                <?php $active = ($currentPage === basename($item['url'])) ? 'active' : ''; ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $active; ?>" href="<?php echo $item['url']; ?>"
                        data-bs-toggle="<?php echo $sidebarCollapsed ? 'tooltip' : ''; ?>"
                        data-bs-placement="right"
                        title="<?php echo $sidebarCollapsed ? $item['title'] : ''; ?>">
                        <i class="bi <?php echo $item['icon']; ?> me-2"></i>
                        <span class="sidebar-text"><?php echo $item['title']; ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="p-2 user-panel">
        <hr class="dropdown-divider my-1">
        <a href="settings.php" class="user-link">
            <div class="d-flex align-items-center">
                <div class="avatar me-2">
                    <i class="bi bi-person-circle"></i>
                </div>
                <span class="sidebar-text user-name"><?php echo htmlspecialchars($currentUserName); ?></span>
            </div>
        </a>
    </div>
    <div class="p-2 logout-panel">
        <a href="logout.php" class="logout-link nav-link">
            <i class="bi bi-box-arrow-right me-2"></i>
            <span class="sidebar-text">Logout</span>
        </a>
    </div>
</div>

<style>
    /* Existing styles remain the same */
    .sidebar {
        min-height: 100vh;
        background-color: #f8f9fa;
        transition: all 0.25s ease-in-out;
        width: 220px;
        position: relative;
        box-shadow: 0.125rem 0 0.75rem rgba(0, 0, 0, 0.05);
        border-right: 1px solid #eee;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        z-index: 1030;
    }

    .sidebar-collapsed {
        width: 50px;
        overflow-x: hidden;
    }

    .sidebar-text {
        opacity: 1;
        transition: opacity 0.15s ease;
        white-space: nowrap;
    }

    .sidebar-collapsed .sidebar-text {
        opacity: 0;
        width: 0;
        display: none;
    }

    .sidebar-collapsed .p-2 {
        padding-left: 0.5rem !important;
        padding-right: 0.5rem !important;
    }

    .toggle-sidebar {
        background: transparent;
        border: none;
        border-radius: 0.25rem;
        cursor: pointer;
    }

    .toggle-sidebar:hover {
        background-color: #e9ecef;
    }

    .toggle-sidebar .bi-chevron-double-left {
        transition: transform 0.25s ease;
    }

    .sidebar-collapsed .toggle-sidebar .bi-chevron-double-left {
        transform: rotate(180deg);
    }

    .brand-name {
        font-size: 1.1rem;
        color: #007bff !important;
        transition: opacity 0.15s ease;
    }

    .sidebar-collapsed .brand-name {
        opacity: 0;
        width: 0;
        display: none;
    }

    .nav-link {
        display: flex;
        align-items: center;
        padding: 0.5rem 0.75rem;
        color: #495057;
        border-radius: 0.25rem;
        margin-bottom: 0.2rem;
        transition: background-color 0.15s ease;
        white-space: nowrap;
        overflow: hidden;
        text-decoration: none !important;
    }

    .nav-link:hover,
    .nav-link.active {
        background-color: #e0f7fa;
        color: #0b7285;
    }

    .nav-link.active {
        font-weight: 600;
    }

    .nav-link i {
        font-size: 1rem;
        min-width: 20px;
        text-align: center;
        transition: margin 0.2s ease;
    }

    .sidebar-collapsed .nav-link i {
        font-size: 1.2rem;
        margin-right: 0 !important;
        margin-left: 0.15rem;
    }

    .avatar {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background-color: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .sidebar-collapsed .avatar {
        width: 32px;
        height: 32px;
        margin-right: 0 !important;
    }

    .avatar i {
        font-size: 1rem;
        color: #6c757d;
    }

    .user-panel {
        background-color: #f8f9fa;
        border-top: 1px solid #eee;
        margin-top: auto;
        /* Push user panel to the bottom, but logout below it */
        border-bottom: 1px solid #eee;
        /* Added border to separate from logout */
    }

    .user-link {
        display: flex;
        align-items: center;
        padding: 0.5rem 0.75rem;
        color: #495057;
        text-decoration: none !important;
        transition: background-color 0.15s ease;
        border-radius: 0.25rem;
        overflow: hidden;
        white-space: nowrap;
    }

    .user-link:hover {
        background-color: #e9ecef;
    }

    .user-name {
        font-weight: 500;
        color: #343a40;
        transition: opacity 0.15s ease;
    }

    .sidebar-collapsed .user-name {
        opacity: 0;
        width: 0;
        display: none;
    }

    .logout-panel {
        background-color: #f8f9fa;
        /* Match sidebar background */
        margin-top: 0;
        /* Ensure it's directly after user panel */
    }

    .logout-link {
        display: flex;
        align-items: center;
        padding: 0.5rem 0.75rem;
        color: #dc3545;
        /* Example: Red color for logout */
        text-decoration: none !important;
        transition: background-color 0.15s ease;
        border-radius: 0.25rem;
        overflow: hidden;
        white-space: nowrap;
    }

    .logout-link:hover {
        background-color: #f8d7da;
        /* Light red on hover */
        color: #dc3545;
    }

    .logout-link i {
        color: #dc3545;
        /* Red icon for logout */
    }


    /* Add media query for mobile devices */
    @media (max-width: 767.98px) {
        .sidebar:not(.sidebar-collapsed) {
            transform: translateX(-100%);
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
        }

        .sidebar.sidebar-collapsed {
            transform: translateX(0);
            width: 50px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarCollapseBtn');

        // Initialize tooltips only if sidebar is collapsed
        function initTooltips() {
            if (sidebar.classList.contains('sidebar-collapsed')) {
                document.querySelectorAll('.nav-link').forEach(link => {
                    if (!link.classList.contains('logout-link')) { // Exclude logout link from tooltips
                        link.setAttribute('data-bs-toggle', 'tooltip');
                        link.setAttribute('title', link.querySelector('.sidebar-text').textContent);
                        new bootstrap.Tooltip(link);
                    }
                });
            }
        }

        // Destroy all tooltips
        function destroyTooltips() {
            document.querySelectorAll('.nav-link').forEach(link => {
                if (!link.classList.contains('logout-link')) { // Exclude logout link from tooltip destruction
                    const tooltip = bootstrap.Tooltip.getInstance(link);
                    if (tooltip) {
                        tooltip.dispose();
                    }
                    link.removeAttribute('data-bs-toggle');
                    link.removeAttribute('title');
                }
            });
        }

        // Initialize tooltips on page load if sidebar is collapsed
        initTooltips();

        // Toggle sidebar when button is clicked
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();

            // Destroy existing tooltips first
            destroyTooltips();

            // Toggle sidebar class
            sidebar.classList.toggle('sidebar-collapsed');

            // Set appropriate tooltip attributes based on sidebar state
            const isCollapsed = sidebar.classList.contains('sidebar-collapsed');

            // Initialize tooltips if sidebar is collapsed
            if (isCollapsed) {
                initTooltips();
            }

            // Save state to cookie
            document.cookie = `sidebar_collapsed=${isCollapsed}; path=/; max-age=31536000`;
        });

        // Make sure tooltips don't remain when hovering out
        document.querySelectorAll('.nav-link').forEach(link => {
            if (!link.classList.contains('logout-link')) { // Exclude logout link from tooltip hover out
                link.addEventListener('mouseleave', function() {
                    const tooltip = bootstrap.Tooltip.getInstance(this);
                    if (tooltip) {
                        tooltip.hide();
                    }
                });
            }
        });

        // Handle window resize - collapse sidebar on small screens
        window.addEventListener('resize', function() {
            if (window.innerWidth < 768 && !sidebar.classList.contains('sidebar-collapsed')) {
                destroyTooltips();
                sidebar.classList.add('sidebar-collapsed');
                initTooltips();
                document.cookie = 'sidebar_collapsed=true; path=/; max-age=31536000';
            }
        });
    });
</script>