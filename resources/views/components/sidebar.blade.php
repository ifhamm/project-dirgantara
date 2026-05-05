{{-- Sidebar Navigation dengan Toggle --}}
<aside id="sidebar" class="sidebar-container active">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <i class="fas fa-plane"></i>
            </div>
            <div class="brand-text">
                <div class="brand-title">AIRCRAFT</div>
                <div class="brand-subtitle">COMPONENT</div>
            </div>
        </div>
        <button id="sidebar-toggle" class="sidebar-toggle" title="Tutup sidebar">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-home nav-icon"></i>
            <span class="nav-label">Dashboard</span>
        </a>

        {{-- Tracking List --}}
        <a href="{{ route('mws.tracking') }}" class="nav-item {{ request()->routeIs('mws.tracking') ? 'active' : '' }}">
            <i class="fas fa-list-check nav-icon"></i>
            <span class="nav-label">Tracking List</span>
        </a>

        {{-- Kelola Pengguna --}}
        <a href="#" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <i class="fas fa-users nav-icon"></i>
            <span class="nav-label">Kelola Pengguna</span>
        </a>
    </nav>

    {{-- Logout --}}
    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST" class="w-100">
            @csrf
            <button type="submit" class="nav-item nav-logout">
                <i class="fas fa-sign-out-alt nav-icon"></i>
                <span class="nav-label">Logout</span>
            </button>
        </form>
    </div>
</aside>

{{-- Toggle Button untuk Mobile --}}
<button id="sidebar-open-btn" class="sidebar-open-btn" title="Buka sidebar">
    <i class="fas fa-bars"></i>
</button>

<style>
    :root {
        --sidebar-width: 280px;
        --sidebar-bg-dark: #0f2844;
        --sidebar-bg-light: #1a3a52;
        --sidebar-accent: #4299e1;
        --sidebar-accent-dark: #3182ce;
    }

    .sidebar-container {
        position: fixed;
        left: 0;
        top: 0;
        width: var(--sidebar-width);
        height: 100vh;
        background: linear-gradient(135deg, var(--sidebar-bg-dark) 0%, var(--sidebar-bg-light) 100%);
        color: white;
        display: flex;
        flex-direction: column;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.3);
        z-index: 1001;
        transition: transform 0.3s ease;
    }

    .sidebar-container.collapsed {
        transform: translateX(-100%);
    }

    .sidebar-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .sidebar-brand {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex: 1;
    }

    .brand-icon {
        font-size: 1.8rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .brand-text {
        flex: 1;
    }

    .brand-title {
        font-size: 0.95rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        line-height: 1.2;
    }

    .brand-subtitle {
        font-size: 0.85rem;
        font-weight: 700;
        letter-spacing: 0.05em;
    }

    .sidebar-toggle {
        background: none;
        border: none;
        color: rgba(255, 255, 255, 0.7);
        cursor: pointer;
        font-size: 1.2rem;
        padding: 0.5rem;
        display: none;
        transition: color 0.2s;
    }

    .sidebar-toggle:hover {
        color: white;
    }

    .sidebar-nav {
        flex: 1;
        padding: 1.5rem 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        overflow-y: auto;
    }

    .nav-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.25rem;
        color: rgba(255, 255, 255, 0.7);
        text-decoration: none;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
        cursor: pointer;
        font-size: 0.95rem;
        font-weight: 500;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
    }

    .nav-item:hover {
        background-color: rgba(255, 255, 255, 0.1);
        color: white;
    }

    .nav-item.active {
        background: linear-gradient(135deg, var(--sidebar-accent) 0%, var(--sidebar-accent-dark) 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(66, 153, 225, 0.3);
    }

    .nav-icon {
        width: 1.25rem;
        text-align: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .nav-label {
        flex: 1;
        white-space: nowrap;
    }

    .sidebar-footer {
        padding: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .nav-logout {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.25rem;
        color: #fc8181;
        text-decoration: none;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
        cursor: pointer;
        font-size: 0.95rem;
        font-weight: 500;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
    }

    .nav-logout:hover {
        background-color: rgba(252, 129, 129, 0.1);
        color: #f56565;
    }

    /* Sidebar Open Button untuk Mobile */
    .sidebar-open-btn {
        display: none;
        position: fixed;
        top: 1rem;
        left: 1rem;
        background: var(--sidebar-accent);
        color: white;
        border: none;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        cursor: pointer;
        font-size: 1.1rem;
        z-index: 999;
        transition: all 0.3s ease;
    }

    .sidebar-open-btn:hover {
        background: var(--sidebar-accent-dark);
    }

    /* Main content adjustment */
    body.sidebar-active {
        margin-left: var(--sidebar-width);
    }

    main.main-with-sidebar {
        margin-left: var(--sidebar-width);
    }

    /* Responsive design */
    @media (max-width: 768px) {
        :root {
            --sidebar-width: 250px;
        }

        .sidebar-container {
            width: var(--sidebar-width);
            transform: translateX(-100%);
        }

        .sidebar-container.active {
            transform: translateX(0);
        }

        .sidebar-toggle {
            display: block;
        }

        .sidebar-open-btn {
            display: block;
        }

        main.main-with-sidebar {
            margin-left: 0;
        }
    }

    /* Scrollbar styling untuk sidebar nav */
    .sidebar-nav::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar-nav::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 3px;
    }

    .sidebar-nav::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 3px;
    }

    .sidebar-nav::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.3);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebar-toggle');
        const openBtn = document.getElementById('sidebar-open-btn');
        const isMobile = window.innerWidth <= 768;

        // Load sidebar state from localStorage
        const sidebarState = localStorage.getItem('sidebarState');
        
        if (isMobile) {
            // Mobile: default to closed
            if (sidebarState !== 'open') {
                sidebar.classList.remove('active');
            }
        } else {
            // Desktop: default to open
            if (sidebarState === 'collapsed') {
                sidebar.classList.remove('active');
            }
        }

        // Toggle button click
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                sidebar.classList.toggle('active');
                const isActive = sidebar.classList.contains('active');
                localStorage.setItem('sidebarState', isActive ? 'open' : 'collapsed');
            });
        }

        // Open button click (mobile)
        if (openBtn) {
            openBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                sidebar.classList.add('active');
                localStorage.setItem('sidebarState', 'open');
            });
        }

        // Close sidebar when clicking nav links on mobile
        if (isMobile) {
            document.querySelectorAll('.sidebar-nav .nav-item, .sidebar-footer form').forEach(element => {
                element.addEventListener('click', function(e) {
                    // Don't close on form submit
                    if (this.tagName !== 'FORM') {
                        sidebar.classList.remove('active');
                        localStorage.setItem('sidebarState', 'collapsed');
                    }
                });
            });
        }

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                sidebar.classList.add('active');
                localStorage.setItem('sidebarState', 'open');
            }
        });

        // Close sidebar when clicking outside (mobile)
        if (isMobile) {
            document.addEventListener('click', function(e) {
                if (!sidebar.contains(e.target) && !openBtn?.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
            });
        }
    });
</script>
