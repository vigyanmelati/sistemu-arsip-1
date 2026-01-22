<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SITEMU ARSIP - KPU Provinsi Bali</title>

    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #8B0000; /* Dark red/maroon */
            --secondary-color: #A52A2A;
            --accent-color: #CD5C5C;
            --sidebar-width: 260px;
            --sidebar-collapsed: 80px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            color: #333;
            overflow-x: hidden;
        }
        
        /* Sidebar Styling */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #800000 0%, #8B0000 100%); /* Dark red gradient */
            color: white;
            padding-top: 20px;
            box-shadow: 3px 0 15px rgba(0,0,0,0.08);
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-x: hidden;
        }
        
        .sidebar.collapsed {
            width: var(--sidebar-collapsed);
        }
        
        .sidebar.collapsed .nav-link span,
        .sidebar.collapsed .sidebar-brand .brand-text,
        .sidebar.collapsed .sidebar-brand small,
        .sidebar.collapsed .logout-btn span,
        .sidebar.collapsed .badge {
            display: none !important;
        }
        
        .sidebar.collapsed .sidebar-brand {
            padding: 20px 10px;
        }
        
        .sidebar.collapsed .sidebar-brand .logo-container {
            margin: 0 auto;
        }
        
        .sidebar.collapsed .nav-link {
            padding: 12px;
            justify-content: center;
            margin: 5px 10px;
            border-radius: 8px;
        }
        
        .sidebar.collapsed .nav-link i {
            margin-right: 0;
            font-size: 1.3rem;
        }
        
        .sidebar.collapsed .logout-btn {
            width: calc(100% - 20px);
            left: 10px;
            padding: 0;
        }
        
        .sidebar-brand {
            text-align: center;
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .logo-container {
            width: 100px;
            height: 100px;
            margin: 0 auto 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-radius: 8px;
        }
        
        .sidebar-logo {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .sidebar-brand h4 {
            margin: 0;
            font-weight: 700;
            color: white;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .sidebar-brand small {
            font-size: 0.75rem;
            opacity: 0.9;
            margin-top: 3px;
            display: block;
            color: rgba(255,255,255,0.8);
        }
        
        .nav-item {
            margin-bottom: 5px;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.85);
            padding: 14px 20px;
            border-radius: 8px;
            margin: 0 15px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            text-decoration: none;
            position: relative;
        }
        
        .nav-link:hover {
            color: white;
            background-color: rgba(255,255,255,0.1);
            transform: translateX(3px);
        }
        
        .nav-link.active {
            color: white;
            background: linear-gradient(90deg, rgba(205, 92, 92, 0.3) 0%, rgba(205, 92, 92, 0.15) 100%);
            border-left: 4px solid #CD5C5C;
            box-shadow: 0 4px 12px rgba(205, 92, 92, 0.15);
        }
        
        .nav-link i {
            width: 24px;
            font-size: 1.2rem;
            margin-right: 12px;
            transition: all 0.3s ease;
        }
        
        .nav-link .badge {
            margin-left: auto;
            font-size: 0.75rem;
            padding: 4px 8px;
            transition: all 0.3s ease;
        }
        
        .logout-btn {
            position: absolute;
            bottom: 30px;
            width: calc(100% - 30px);
            left: 15px;
            transition: all 0.3s ease;
        }
        
        /* Main Content Area */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 25px;
            min-height: 100vh;
            transition: all 0.3s ease;
        }
        
        .main-content.expanded {
            margin-left: var(--sidebar-collapsed);
        }
        
        /* Top Header */
        .top-header {
            background: white;
            padding: 20px 30px;
            margin: -25px -25px 30px -25px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eef2f7;
        }
        
        .page-title h3 {
            margin: 0;
            font-weight: 700;
            color: #8B0000; /* Match sidebar color */
            font-size: 1.4rem;
            padding-left: 50px; /* Space for toggle button */
        }
        
        .page-title p {
            margin: 8px 0 0 0;
            color: #666;
            font-size: 0.9rem;
            padding-left: 50px; /* Space for toggle button */
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #8B0000 0%, #A52A2A 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            box-shadow: 0 4px 8px rgba(139, 0, 0, 0.2);
        }
        
        .user-details {
            text-align: right;
        }
        
        .user-details .user-name {
            font-weight: 600;
            margin: 0;
            color: #333;
            font-size: 0.95rem;
        }
        
        .user-details .user-role {
            font-size: 0.8rem;
            color: #666;
            margin: 3px 0 0 0;
        }
        
        /* Card Styling */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 25px;
            border: 1px solid #f0f0f0;
        }
        
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #eef2f7;
            padding: 18px 25px;
            border-radius: 12px 12px 0 0 !important;
        }
        
        .card-header h5 {
            margin: 0;
            font-weight: 600;
            color: #333;
            font-size: 1.1rem;
        }
        
        /* Toggle Button - POSISI DI SAMPING SIDEBAR */
        .toggle-btn {
            background: linear-gradient(135deg, #8B0000 0%, #A52A2A 100%);
            border: none;
            border-radius: 10px;
            padding: 10px;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(139, 0, 0, 0.2);
            position: fixed;
            top: 100px; /* Adjust position */
            left: calc(var(--sidebar-width) - 20px);
            z-index: 1001;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
        }
        
        .toggle-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(139, 0, 0, 0.3);
        }
        
        .toggle-btn.collapsed {
            left: calc(var(--sidebar-collapsed) - 20px);
        }
        
        /* Mobile Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0 !important;
            }
            
            .toggle-btn {
                display: none;
            }
            
            .page-title h3,
            .page-title p {
                padding-left: 0; /* Reset padding on mobile */
            }
            
            .top-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
                padding: 15px;
            }
            
            .user-info {
                width: 100%;
                justify-content: center;
            }
        }
        
        /* Button Styling */
        .btn-primary {
            background: linear-gradient(135deg, #8B0000 0%, #A52A2A 100%);
            border: none;
            border-radius: 8px;
            padding: 10px 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #800000 0%, #8B0000 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 0, 0, 0.3);
        }
        
        /* Footer */
        .footer {
            margin-top: 50px;
            padding: 25px 0;
            text-align: center;
            color: #666;
            font-size: 0.85rem;
            border-top: 1px solid #eef2f7;
        }
        
        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }
        
        /* Badge Custom */
        .badge-notification {
            font-size: 0.75rem;
            padding: 4px 8px;
        }
        
        /* Fix untuk toggle button yang ketimpa */
        .toggle-btn {
            z-index: 1001;
        }
        
        .sidebar {
            z-index: 1000;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="logo-container">
            <img src="{{ asset('logo_sitemu_arsip.png') }}" alt="SITEMU ARSIP" class="sidebar-logo">
        </div>
        <div class="brand-text">
            <h4>SITEMU ARSIP</h4>
            <small>KPU Provinsi Bali</small>
        </div>
    </div>
    
    
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
            </a>
        </li>
        
        <!-- Menu untuk semua user yang login -->
        @auth
            <!-- Menu untuk Admin dan Super Admin (kelola arsip) -->
            @if(auth()->user()->role === 'admin' || auth()->user()->role === 'super_admin')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('arsip.*') ? 'active' : '' }}" href="{{ route('arsip.index') }}">
                    <i class="bi bi-folder"></i> <span>Kelola Arsip</span>
                    @php
                        $totalArsip = App\Models\Arsip::count();
                    @endphp
                    @if($totalArsip > 0)
                    <span class="badge bg-warning text-dark badge-notification">{{ $totalArsip }}</span>
                    @endif
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('pemusnahan.*') ? 'active' : '' }}" href="#">
                    <i class="bi bi-trash"></i> <span>Pemusnahan</span>
                    @php
                        $arsipMusnah = App\Models\Arsip::where('status_arsip', 'UMSUL_MUSNAH')->count();
                    @endphp
                    @if($arsipMusnah > 0)
                    <span class="badge bg-danger badge-notification">{{ $arsipMusnah }}</span>
                    @endif
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="bi bi-search"></i> <span>Temu Kembali</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="bi bi-file-text"></i> <span>Laporan</span>
                </a>
            </li>
            @endif
            
            <!-- Menu khusus untuk Super Admin -->
            @if(auth()->user()->role === 'super_admin')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('master.kode-klasifikasi.*') ? 'active' : '' }}" href="{{ route('master.kode-klasifikasi.index') }}">
                    <i class="bi bi-tags"></i> <span>Master Kode Klasifikasi</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('master.sub-bagian.*') ? 'active' : '' }}" href="{{ route('master.sub-bagian.index') }}">
                    <i class="bi bi-diagram-3"></i> <span>Master Sub Bagian</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                    <i class="bi bi-people"></i> <span>Manajemen User</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="bi bi-gear"></i> <span>Pengaturan Sistem</span>
                </a>
            </li>
            @endif
            
            <!-- Menu untuk User biasa (jika ada fitur khusus) -->
            @if(auth()->user()->role === 'user')
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="bi bi-search"></i> <span>Pencarian Arsip</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="bi bi-download"></i> <span>Unduh Dokumen</span>
                </a>
            </li>
            @endif
        @endauth
    </ul>
    
    <div class="logout-btn">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-light w-100 d-flex align-items-center justify-content-center">
                <i class="bi bi-box-arrow-right"></i> <span class="ms-2">Logout</span>
            </button>
        </form>
    </div>
</div>

<!-- Sidebar Toggle Button -->
<button class="toggle-btn d-none d-lg-block" id="sidebarToggle">
    <i class="bi bi-chevron-left"></i>
</button>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <!-- Top Header -->
    <div class="top-header fade-in">
        <div class="page-title">
            <h3>@yield('page-title', 'Dashboard')</h3>
            <p>@yield('page-subtitle', 'Sistem Temu Arsip Digital KPU Provinsi Bali')</p>
        </div>
        
        <div class="user-info">
            <div class="user-avatar">
                {{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1)) : 'A' }}
            </div>
            <div class="user-details">
                @auth
                <p class="user-name">{{ auth()->user()->name }}</p>
                <p class="user-role">
                    @php
                        // Mapping role ke label yang lebih user-friendly
                        $roleLabels = [
                            'super_admin' => 'Super Administrator',
                            'admin' => 'Administrator',
                            'user' => 'Pengguna'
                        ];
                        $role = auth()->user()->role ?? 'user';
                        $roleLabel = $roleLabels[$role] ?? 'Pengguna';
                        
                        // Tambahkan badge berdasarkan role
                        $roleBadgeColors = [
                            'super_admin' => 'danger',
                            'admin' => 'warning',
                            'user' => 'info'
                        ];
                        $badgeColor = $roleBadgeColors[$role] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $badgeColor }}">{{ $roleLabel }}</span>
                </p>
                @else
                <p class="user-name">Guest</p>
                <p class="user-role">Pengunjung</p>
                @endauth
            </div>
        </div>
    </div>

    <!-- Page Content -->
    <div class="fade-in">
        @yield('content')
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <p class="mb-0">
            &copy; {{ date('Y') }} SITEMU ARSIP - KPU Provinsi Bali
            <br>
            <small>v1.0.0</small>
        </p>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom Script -->
<script>
    // Sidebar Toggle Functionality
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const toggleBtn = document.getElementById('sidebarToggle');
    
    // Toggle sidebar collapse
    toggleBtn.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
        toggleBtn.classList.toggle('collapsed');
        
        // Change icon
        const icon = this.querySelector('i');
        if (sidebar.classList.contains('collapsed')) {
            icon.className = 'bi bi-chevron-right';
        } else {
            icon.className = 'bi bi-chevron-left';
        }
        
        // Save state to localStorage
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    });
    
    // Mobile sidebar toggle
    document.addEventListener('DOMContentLoaded', function() {
        // Load saved sidebar state
        const savedState = localStorage.getItem('sidebarCollapsed');
        if (savedState === 'true' && window.innerWidth > 992) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
            toggleBtn.classList.add('collapsed');
            toggleBtn.querySelector('i').className = 'bi bi-chevron-right';
        }
        
        // Mobile menu toggle button
        const mobileToggle = document.createElement('button');
        mobileToggle.className = 'btn btn-primary d-lg-none';
        mobileToggle.innerHTML = '<i class="bi bi-list"></i>';
        mobileToggle.style.cssText = 'position: fixed; top: 15px; right: 15px; z-index: 1001; border-radius: 8px; width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #8B0000 0%, #A52A2A 100%); border: none; color: white;';
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
        document.body.appendChild(mobileToggle);
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 992 && 
                sidebar.classList.contains('show') &&
                !sidebar.contains(event.target) &&
                !mobileToggle.contains(event.target)) {
                sidebar.classList.remove('show');
            }
        });
    });
</script>

@stack('scripts')

</body>
</html>