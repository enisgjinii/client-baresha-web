<?php
$navItems = [
    ['title' => 'Paneli Kryesor', 'icon' => 'bi-speedometer2', 'url' => 'dashboard.php'],
    ['title' => 'Faturat', 'icon' => 'bi-receipt', 'url' => 'invoices.php'],
    ['title' => 'Raportet', 'icon' => 'bi-bar-chart', 'url' => 'reports.php'],
    // ['title' => 'Qendra e Ndihmës', 'icon' => 'bi-question-circle', 'url' => 'help-center.php'],
    ['title' => 'Kontrata e Përgjithshme', 'icon' => 'bi-file-earmark-medical', 'url' => 'general-contract.php'],
    ['title' => 'Kontrata e Këngës', 'icon' => 'bi-file-earmark-music', 'url' => 'song-contract.php'],
    ['title' => 'Platformat', 'icon' => 'bi-file-earmark-spreadsheet', 'url' => 'csv-income.php'],
];
$currentPage = basename($_SERVER['PHP_SELF']);
// Fetch user name from session, default to "User" if not set
$currentUserName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : "User"; 
$sidebarCollapsed = isset($_COOKIE['sidebar_collapsed']) ? $_COOKIE['sidebar_collapsed'] === 'true' : false;
$sidebarClass = $sidebarCollapsed ? 'sidebar-collapsed' : '';
?>
<div class="col-auto p-0 sidebar <?php echo $sidebarClass; ?>" id="sidebar">
    <div class="sidebar-header p-2">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center logo-container">
                <i class="bi bi-grid-3x3-gap-fill text-primary fs-5"></i>
                <span class="fw-bold text-primary sidebar-text ms-1">Baresha</span>
            </div>
            <button class="btn btn-sm toggle-sidebar" id="sidebarCollapseBtn" aria-label="Toggle Sidebar">
                <i class="bi bi-chevron-double-left"></i>
            </button>
        </div>
    </div>
    <div class="sidebar-content p-2">
        <ul class="nav flex-column">
            <?php foreach ($navItems as $item): ?>
                <?php $active = ($currentPage === basename($item['url'])) ? 'active' : ''; ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $active; ?>" href="<?php echo $item['url']; ?>"
                        data-bs-toggle="<?php echo $sidebarCollapsed ? 'tooltip' : ''; ?>"
                        data-bs-placement="right"
                        title="<?php echo $sidebarCollapsed ? $item['title'] : ''; ?>">
                        <i class="bi <?php echo $item['icon']; ?>"></i>
                        <span class="sidebar-text"><?php echo $item['title']; ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="user-panel">
        <a href="settings.php" class="user-link">
            <div class="d-flex align-items-center">
                <div class="avatar">
                    <i class="bi bi-person"></i>
                </div>
                <span class="sidebar-text user-name"><?php echo htmlspecialchars($currentUserName); ?></span>
            </div>
        </a>        
        <!-- Qendra e ndihmes put here -->
        <a href="help-center.php" class="user-link">
            <i class="bi bi-question-circle"></i>
            <span class="sidebar-text">Qendra e Ndihmës</span>
        </a>
        <a href="logout.php" class="logout-link">
            <i class="bi bi-box-arrow-right"></i>
            <span class="sidebar-text">Dilni</span>
        </a>        <div class="theme-toggle">
            <button class="theme-option" data-theme="light" title="Ndriçim">
                <i class="bi bi-sun"></i>
            </button>
            <button class="theme-option" data-theme="dark" title="Errët">
                <i class="bi bi-moon"></i>
            </button>
            <button class="theme-option" data-theme="system" title="Automatik">
                <i class="bi bi-laptop"></i>
            </button>
        </div>
    </div>
</div>

<style>
    .sidebar {
        min-height: 100vh;
        background-color: #f8f9fa;
        transition: width 0.25s ease-in-out;
        width: 220px;
        position: relative;
        border-right: 1px solid #eee;
        display: flex;
        flex-direction: column;
        z-index: 1030;
        overflow-x: hidden;
    }

    .sidebar-collapsed {
        width: 50px;
    }

    .sidebar-header {
        border-bottom: 1px solid #eee;
        padding-bottom: 0.5rem !important;
    }

    .sidebar-content {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .sidebar-text {
        opacity: 1;
        transition: opacity 0.2s ease;
        white-space: nowrap;
        margin-left: 8px;
        font-size: 0.9rem;
    }

    .sidebar-collapsed .sidebar-text {
        opacity: 0;
        width: 0;
        display: none;
    }

    .logo-container {
        transition: transform 0.25s ease;
        transform-origin: left;
    }

    .sidebar-collapsed .logo-container {
        transform: scale(0.8);
    }

    .toggle-sidebar {
        background: transparent;
        border: none;
        padding: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.85rem;
        border-radius: 50%;
        transition: background-color 0.15s ease;
    }

    .toggle-sidebar:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }

    .toggle-sidebar .bi-chevron-double-left {
        transition: transform 0.25s ease;
    }

    .sidebar-collapsed .toggle-sidebar .bi-chevron-double-left {
        transform: rotate(180deg);
    }

    .nav-item {
        margin-bottom: 3px;
    }

    .nav-link {
        display: flex;
        align-items: center;
        padding: 8px;
        color: #495057;
        border-radius: 6px;
        transition: all 0.15s ease;
        font-size: 0.85rem;
        white-space: nowrap;
        overflow: hidden;
        text-decoration: none !important;
    }

    .nav-link:hover {
        background-color: rgba(0, 0, 0, 0.04);
        color: #0b7285;
    }

    .nav-link.active {
        background-color: #e0f7fa;
        color: #0b7285;
        font-weight: 500;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    }

    .nav-link i {
        font-size: 1rem;
        min-width: 20px;
        text-align: center;
        transition: margin 0.25s ease, font-size 0.25s ease;
    }

    .sidebar-collapsed .nav-link i {
        margin-left: 4px;
        font-size: 1.1rem;
    }

    .user-panel {
        margin-top: auto;
        padding: 8px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        border-top: 1px solid #eee;
    }

    .user-link, .logout-link {
        display: flex;
        align-items: center;
        padding: 8px;
        color: #495057;
        border-radius: 6px;
        transition: all 0.15s ease;
        white-space: nowrap;
        overflow: hidden;
        text-decoration: none !important;
        font-size: 0.85rem;
    }

    .user-link:hover {
        background-color: rgba(0, 0, 0, 0.04);
    }

    .logout-link {
        color: #dc3545;
    }

    .logout-link:hover {
        background-color: rgba(220, 53, 69, 0.1);
    }

    .avatar {
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 0.95rem;
        transition: transform 0.25s ease;
    }
    
    .sidebar-collapsed .avatar {
        transform: translateX(3px) scale(1.1);
    }

    .theme-toggle {
        display: flex;
        gap: 4px;
        padding: 4px;
        border-radius: 6px;
        background-color: rgba(0, 0, 0, 0.02);
        justify-content: center;
    }

    .sidebar-collapsed .theme-toggle {
        flex-direction: column;
    }

    .theme-option {
        background: transparent;
        border: none;
        border-radius: 4px;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #6c757d;
        padding: 0;
        font-size: 0.9rem;
        transition: all 0.15s ease;
    }

    .theme-option:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }

    .theme-option.active {
        background-color: rgba(0, 0, 0, 0.08);
        color: #0d6efd;
    }

    /* Animation for collapsing and expanding */
    @keyframes slideIn {
        from { width: 50px; }
        to { width: 220px; }
    }
    
    @keyframes slideOut {
        from { width: 220px; }
        to { width: 50px; }
    }
    
    .sidebar-expanding {
        animation: slideIn 0.25s forwards;
    }
    
    .sidebar-collapsing {
        animation: slideOut 0.25s forwards;
    }

    /* Dark mode overrides */
    .dark-mode .sidebar {
        background-color: #1a1a1a;
        border-right: 1px solid #2a2a2a;
    }
    
    .dark-mode .sidebar-header {
        border-bottom-color: #2a2a2a;
    }

    .dark-mode .nav-link {
        color: #adb5bd;
    }

    .dark-mode .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.05);
        color: #6ea8fe;
    }

    .dark-mode .nav-link.active {
        background-color: rgba(110, 168, 254, 0.15);
        color: #6ea8fe;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .dark-mode .user-link, .dark-mode .theme-option {
        color: #adb5bd;
    }
    
    .dark-mode .user-link:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }
    
    .dark-mode .theme-option:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }
    
    .dark-mode .theme-option.active {
        background-color: rgba(255, 255, 255, 0.1);
        color: #6ea8fe;
    }

    .dark-mode .user-panel {
        border-color: #2a2a2a;
    }
    
    .dark-mode .theme-toggle {
        background-color: rgba(255, 255, 255, 0.03);
    }

    .dark-mode .logout-link {
        color: #f77;
    }
    
    .dark-mode .logout-link:hover {
        background-color: rgba(255, 99, 99, 0.1);
    }
    
    .dark-mode .toggle-sidebar:hover {
        background-color: rgba(255, 255, 255, 0.05);
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
        
        .toggle-sidebar {
            margin-left: auto;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarCollapseBtn');
        const themeOptions = document.querySelectorAll('.theme-option');
        const body = document.body;
        
        // Initialize tooltips only if sidebar is collapsed
        function initTooltips() {
            if (sidebar.classList.contains('sidebar-collapsed')) {
                document.querySelectorAll('.nav-link, .theme-option, .user-link, .logout-link').forEach(element => {
                    element.setAttribute('data-bs-toggle', 'tooltip');
                    const text = element.querySelector('.sidebar-text');
                    if (text) {
                        element.setAttribute('title', text.textContent);
                    }
                    new bootstrap.Tooltip(element);
                });
            }
        }

        // Destroy all tooltips
        function destroyTooltips() {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(element => {
                const tooltip = bootstrap.Tooltip.getInstance(element);
                if (tooltip) {
                    tooltip.dispose();
                }
                if (!element.dataset.theme) {
                    element.removeAttribute('title');
                }
                element.removeAttribute('data-bs-toggle');
            });
        }

        // Initialize tooltips on page load if sidebar is collapsed
        initTooltips();

        // Toggle sidebar with animation
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            destroyTooltips();
            
            if (sidebar.classList.contains('sidebar-collapsed')) {
                // Expanding
                sidebar.classList.add('sidebar-expanding');
                sidebar.classList.remove('sidebar-collapsed');
                
                // Wait for animation to complete
                setTimeout(() => {
                    sidebar.classList.remove('sidebar-expanding');
                    // Dispatch resize event for any components that need to adjust
                    window.dispatchEvent(new Event('resize'));
                }, 250);
            } else {
                // Collapsing
                sidebar.classList.add('sidebar-collapsing');
                
                // Wait for animation to complete
                setTimeout(() => {
                    sidebar.classList.remove('sidebar-collapsing');
                    sidebar.classList.add('sidebar-collapsed');
                    initTooltips();
                    // Dispatch resize event for any components that need to adjust
                    window.dispatchEvent(new Event('resize'));
                }, 250);
            }

            // Store preference with secure cookie
            document.cookie = `sidebar_collapsed=${sidebar.classList.contains('sidebar-collapsed') || sidebar.classList.contains('sidebar-collapsing')}; path=/; max-age=31536000; SameSite=Lax; Secure`;
        });

        // Add hover effect for collapsed sidebar (preview expand)
        let hoverTimeout;
        sidebar.addEventListener('mouseenter', function() {
            if (sidebar.classList.contains('sidebar-collapsed') && window.innerWidth > 768) {
                clearTimeout(hoverTimeout);
                sidebar.style.width = '180px';
                
                document.querySelectorAll('.sidebar-text').forEach(el => {
                    el.style.opacity = '1';
                    el.style.display = 'block';
                });
            }
        });
        
        sidebar.addEventListener('mouseleave', function() {
            if (sidebar.classList.contains('sidebar-collapsed') && window.innerWidth > 768) {
                clearTimeout(hoverTimeout);
                hoverTimeout = setTimeout(() => {
                    sidebar.style.width = '50px';
                    
                    document.querySelectorAll('.sidebar-text').forEach(el => {
                        el.style.opacity = '0';
                        el.style.display = 'none';
                    });
                }, 200);
            }
        });

        // Theme functionality
        const savedTheme = localStorage.getItem('theme-mode') || 'system';
        
        function updateActiveThemeButton(theme) {
            themeOptions.forEach(button => {
                if (button.dataset.theme === theme) {
                    button.classList.add('active');
                } else {
                    button.classList.remove('active');
                }
            });
        }
        
        updateActiveThemeButton(savedTheme);
        
        // Theme option click handler
        themeOptions.forEach(option => {
            option.addEventListener('click', function() {
                const selectedTheme = this.dataset.theme;
                localStorage.setItem('theme-mode', selectedTheme);
                updateActiveThemeButton(selectedTheme);
                applyTheme(selectedTheme);
            });
        });

        function applyTheme(theme) {
            body.classList.add('theme-transition');
            
            if (theme === 'system') {
                if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    body.classList.add('dark-mode');
                    body.classList.remove('light-mode');
                    document.documentElement.setAttribute('data-theme', 'dark');
                } else {
                    body.classList.add('light-mode');
                    body.classList.remove('dark-mode');
                    document.documentElement.setAttribute('data-theme', 'light');
                }
            } else if (theme === 'dark') {
                body.classList.add('dark-mode');
                body.classList.remove('light-mode');
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                body.classList.add('light-mode');
                body.classList.remove('dark-mode');
                document.documentElement.setAttribute('data-theme', 'light');
            }
            
            setTimeout(() => {
                body.classList.remove('theme-transition');
            }, 300);
        }
        
        // Initialize theme
        applyTheme(savedTheme);

        // Listen for changes in system color scheme
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            if (localStorage.getItem('theme-mode') === 'system') {
                applyTheme('system');
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth < 768) {
                if (!sidebar.classList.contains('sidebar-collapsed') && 
                    !sidebar.classList.contains('sidebar-collapsing') && 
                    !sidebar.classList.contains('sidebar-expanding')) {
                    sidebar.classList.add('sidebar-collapsed');
                    initTooltips();
                }
            }
        });
        
        // Session security monitoring
        let inactivityTime = function() {
            let time;
            const maxInactivityTime = 24 * 60 * 60 * 1000; // Changed from 30 minutes to 24 hours
            
            const resetTimer = function() {
                clearTimeout(time);
                time = setTimeout(logout, maxInactivityTime);
            };
            
            const logout = function() {
                if (confirm("Your session is about to expire due to inactivity. Click OK to stay logged in or Cancel to log out.")) {
                    resetTimer();
                } else {
                    window.location.href = 'logout.php';
                }
            };
            
            window.onload = resetTimer;
            document.onmousemove = resetTimer;
            document.onkeydown = resetTimer;
            document.onclick = resetTimer;
            document.ontouchstart = resetTimer;
            document.onscroll = resetTimer;
        };
        
        inactivityTime();
    });
</script>

<style>
    .theme-transition {
        transition: background-color 0.3s ease, color 0.3s ease;
    }
    
    .theme-transition * {
        transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
    }
</style>