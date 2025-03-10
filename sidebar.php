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
    ['title' => 'Link YouTube', 'icon' => 'bi-youtube', 'url' => 'link-youtube.php'],
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
    <!-- Theme Mode Selector -->
    <div class="p-2 theme-toggle">
        <select id="themeSelector" class="form-select form-select-sm">
            <option value="light">Light Mode</option>
            <option value="dark">Dark Mode</option>
            <option value="system">System Mode</option>
        </select>
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
        border-bottom: 1px solid #eee;
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
        margin-top: 0;
    }

    .logout-link {
        display: flex;
        align-items: center;
        padding: 0.5rem 0.75rem;
        color: #dc3545;
        text-decoration: none !important;
        transition: background-color 0.15s ease;
        border-radius: 0.25rem;
        overflow: hidden;
        white-space: nowrap;
    }

    .logout-link:hover {
        background-color: #f8d7da;
        color: #dc3545;
    }

    .logout-link i {
        color: #dc3545;
    }

    /* Theme selector styling */
    .theme-toggle {
        padding: 0.5rem 0.75rem;
    }

    /* Dark mode overrides */
    .dark-mode {
        background-color: #121212;
        color: #e0e0e0;
    }

    .dark-mode .sidebar {
        background-color: #1e1e1e;
        border-right: 1px solid #333;
    }

    .dark-mode .brand-name {
        color: #66b0ff !important;
    }

    .dark-mode .nav-link {
        color: #ccc;
    }

    .dark-mode .nav-link:hover,
    .dark-mode .nav-link.active {
        background-color: #333;
        color: #fff;
    }

    .dark-mode .user-panel,
    .dark-mode .logout-panel {
        background-color: #1e1e1e;
        border-color: #333;
    }

    .dark-mode .user-link:hover {
        background-color: #2c2c2c;
    }

    /* Media query for mobile devices */
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
        const themeSelector = document.getElementById('themeSelector');

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
                if (!link.classList.contains('logout-link')) {
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
            destroyTooltips();
            sidebar.classList.toggle('sidebar-collapsed');
            if (sidebar.classList.contains('sidebar-collapsed')) {
                initTooltips();
            }
            document.cookie = `sidebar_collapsed=${sidebar.classList.contains('sidebar-collapsed')}; path=/; max-age=31536000`;
        });

        // Ensure tooltips hide when mouse leaves
        document.querySelectorAll('.nav-link').forEach(link => {
            if (!link.classList.contains('logout-link')) {
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

        // Theme functionality
        // Retrieve saved theme from localStorage or default to "system"
        const savedTheme = localStorage.getItem('theme-mode') || 'system';
        themeSelector.value = savedTheme;
        applyTheme(savedTheme);

        themeSelector.addEventListener('change', function() {
            const selectedTheme = themeSelector.value;
            localStorage.setItem('theme-mode', selectedTheme);
            applyTheme(selectedTheme);
        });

        function applyTheme(theme) {
            if (theme === 'system') {
                // Apply based on system preference
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.body.classList.add('dark-mode');
                    document.body.classList.remove('light-mode');
                } else {
                    document.body.classList.add('light-mode');
                    document.body.classList.remove('dark-mode');
                }
            } else if (theme === 'dark') {
                document.body.classList.add('dark-mode');
                document.body.classList.remove('light-mode');
            } else {
                document.body.classList.add('light-mode');
                document.body.classList.remove('dark-mode');
            }
        }

        // Listen for changes in system color scheme if "system" mode is active
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            if (themeSelector.value === 'system') {
                applyTheme('system');
            }
        });
    });
</script>