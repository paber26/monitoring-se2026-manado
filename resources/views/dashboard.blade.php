<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1280">
    <title>BPS Monitoring</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            900: '#0c4a6e',
                        },
                        sidebar: '#1e293b' // slate-800
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom scrollbar for a cleaner look */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .view-section {
            display: none;
        }
        .view-section.active {
            display: block;
        }
        /* Custom table styling override for overflow handling */
        .data-table th, .data-table td {
            white-space: nowrap;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased flex h-screen overflow-hidden relative">

    <!-- Mobile Overlay -->
    <div id="mobileOverlay" class="fixed inset-0 bg-slate-900/50 z-20 hidden md:hidden opacity-0 transition-opacity duration-300"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="absolute inset-y-0 left-0 z-30 w-64 bg-sidebar text-slate-300 flex flex-col flex-shrink-0 h-full transition-transform duration-300 -translate-x-full md:relative md:translate-x-0">
        <!-- Logo Area -->
        <div class="h-16 md:h-20 flex items-center px-6 border-b border-slate-700/50 justify-between">
            <div class="flex items-center gap-3">
                <img src="logo.png" alt="Logo BPS" class="w-8 h-8 md:w-9 md:h-9 object-contain drop-shadow-sm">
                <div>
                    <h1 class="text-white font-bold text-base md:text-lg leading-tight">BPS</h1>
                    <p class="text-[10px] md:text-xs text-slate-400 font-medium tracking-wider uppercase">Monitoring</p>
                </div>
            </div>
            <button id="closeSidebarBtn" class="md:hidden text-slate-400 hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-1" id="sideMenu">
            <a href="#" class="menu-item active flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-colors group" data-target="view-dashboard">
                <i data-lucide="layout-dashboard" class="w-5 h-5 text-brand-500 transition-colors nav-icon"></i>
                <span class="text-sm font-medium text-white nav-text">Dashboard Utama</span>
            </a>
            <a href="#" class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-colors group" data-target="view-progres">
                <i data-lucide="map" class="w-5 h-5 text-slate-400 transition-colors nav-icon"></i>
                <span class="text-sm font-medium nav-text">Progres Kecamatan</span>
            </a>
            <a href="#" class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-colors group" data-target="view-sls">
                <i data-lucide="map-pin" class="w-5 h-5 text-slate-400 transition-colors nav-icon"></i>
                <span class="text-sm font-medium nav-text">Progres SLS</span>
            </a>
            
            <div class="pt-4 pb-2">
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Performa</p>
            </div>
            
            <a href="#" class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-colors relative group" data-target="view-leaderboard">
                <i data-lucide="trophy" class="w-5 h-5 text-yellow-500 nav-icon"></i>
                <span class="text-sm font-medium nav-text">Leaderboard Petugas</span>
            </a>
            

            
            <a href="#" class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-colors relative group" data-target="view-target-harian">
                <i data-lucide="target" class="w-5 h-5 text-slate-400 nav-icon"></i>
                <span class="text-sm font-medium nav-text">Target Harian</span>
            </a>
            
            <!-- Dynamic Role Menus -->
            <div id="dynamicMenusContainer"></div>
        </nav>
        
        <!-- User Profile (Bottom) -->
        <div class="p-4 border-t border-slate-700/50">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-slate-700 flex items-center justify-center text-sm font-medium text-white">
                    <i data-lucide="user" class="w-4 h-4"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-white">Admin Kab</p>
                    <p class="text-xs text-slate-400">Minahasa Selatan</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
        
        <!-- Mobile Navbar -->
        <div class="md:hidden bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between flex-shrink-0 shadow-sm z-10">
            <div class="flex items-center gap-3">
                <img src="{{ asset('logo.png') }}" alt="Logo BPS" class="w-7 h-7 object-contain">
                <h1 class="font-bold text-slate-800 text-sm">BPS Monitoring</h1>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="location.reload()" class="p-2 text-brand-600 hover:bg-brand-50 rounded-lg">
                    <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                </button>
                <button id="menuToggle" class="p-2 -mr-2 text-slate-600 hover:bg-slate-100 rounded-lg">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
            </div>
        </div>

        <!-- Header -->
        <header class="bg-white px-4 md:px-8 py-4 md:py-5 border-b border-slate-200 flex-shrink-0 z-10 shadow-sm hidden md:block">
            <div class="flex justify-between items-start md:items-center flex-col md:flex-row gap-4">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-slate-800">Monitoring Pencacahan SE2026 Minahasa Selatan</h2>
                    <div class="flex items-center gap-2 mt-1 text-xs md:text-sm text-slate-500">
                        <i data-lucide="clock" class="w-4 h-4"></i>
                        <p id="last-update">Kondisi terakhir diupdate: <span class="font-medium text-slate-700">Memuat...</span></p>
                    </div>
                </div>
                <div class="flex gap-2 self-start md:self-auto">
                    <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" class="bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition-colors shadow-sm">
                        <i data-lucide="upload" class="w-4 h-4"></i> Upload Data Monitoring
                    </button>
                </div>
            </div>
            
            @if(session('success'))
                <div class="mt-4 p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
                    <span class="font-medium">Berhasil!</span> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mt-4 p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
                    <span class="font-medium">Error!</span> {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mt-4 p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
                    <span class="font-medium">Validasi Gagal:</span>
                    <ul class="list-disc pl-5 mt-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </header>

        <!-- Scrollable Dashboard Content -->
        <div class="flex-1 overflow-y-auto p-4 md:p-8" id="mainViews">
            
            <!-- View: Dashboard Utama -->
            <div id="view-dashboard" class="view-section active max-w-7xl mx-auto space-y-6">
                <!-- Summary Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center gap-4">
                        <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i data-lucide="file-text" class="w-7 h-7"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-500">Total Assignment Dikerjakan</p>
                            <h3 class="text-2xl font-bold text-slate-800 mt-1" id="total-docs">0</h3>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center gap-4">
                        <div class="w-14 h-14 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i data-lucide="target" class="w-7 h-7"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-500">Total SLS Target</p>
                            <h3 class="text-2xl font-bold text-slate-800 mt-1" id="total-target-sls">0</h3>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center gap-4">
                        <div class="w-14 h-14 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center">
                            <i data-lucide="map" class="w-7 h-7"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-500">Total SLS Dikerjakan</p>
                            <h3 class="text-2xl font-bold text-slate-800 mt-1" id="total-regions">0</h3>
                        </div>
                    </div>
                </div>

                <!-- Charts Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                        <h3 class="text-lg font-bold text-slate-800 mb-4">Progres Assignment Per Kecamatan</h3>
                        <div class="relative w-full h-72">
                            <canvas id="regionChart"></canvas>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                        <h3 class="text-lg font-bold text-slate-800 mb-4">Proporsi Status Assignment</h3>
                        <div class="relative w-full h-72">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- View: Progres Kecamatan -->
            <div id="view-progres" class="view-section max-w-7xl mx-auto">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100">
                        <h3 class="text-lg font-bold text-slate-800">Progres Assignment Per Kecamatan</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table id="pivotTable" class="w-full text-sm text-left data-table">
                            <thead class="text-xs text-slate-500 bg-slate-50 uppercase font-semibold border-b border-slate-100">
                                <tr id="pivotHeadRow"></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- View: Progres SLS -->
            <div id="view-sls" class="view-section max-w-7xl mx-auto">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
                    <div class="px-6 py-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <h3 class="text-lg font-bold text-slate-800">Progres Assignment Per SLS</h3>
                        <div class="flex gap-3">
                            <div class="relative">
                                <select id="filterKecamatanSls" class="appearance-none bg-slate-50 border border-slate-200 text-slate-700 py-2 pl-4 pr-10 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 cursor-pointer">
                                    <option value="">Semua Kecamatan</option>
                                </select>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                            </div>
                            <div class="relative">
                                <select id="filterFlagSls" class="appearance-none bg-slate-50 border border-slate-200 text-slate-700 py-2 pl-4 pr-10 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 cursor-pointer">
                                    <option value="">Semua Flag SLS</option>
                                    <option value="1">Ada Flag Open PBI (>0)</option>
                                    <option value="0">Tidak Ada Flag (0)</option>
                                </select>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table id="slsTable" class="w-full text-sm text-left data-table">
                            <thead class="text-xs text-slate-500 bg-slate-50 uppercase font-semibold border-b border-slate-100">
                                <tr>
                                    <th class="px-6 py-4">No</th>
                                    <th class="px-6 py-4">Kecamatan</th>
                                    <th class="px-6 py-4">Desa/Kelurahan</th>
                                    <th class="px-6 py-4">Nama SLS</th>
                                    <th class="px-6 py-4">Kode SLS</th>
                                    <th class="px-6 py-4 text-right">Prelist</th>
                                    <th class="px-6 py-4 text-right">Dikerjakan</th>
                                    <th class="px-6 py-4 text-center">Flag SLS</th>
                                    <th class="px-6 py-4 text-center">KK Open PBI</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- View: Leaderboard -->
            <div id="view-leaderboard" class="view-section max-w-7xl mx-auto space-y-6">
                <!-- Top 10 Grids -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Top 10 Pencacah Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
                        <div class="px-6 py-5 border-b border-slate-100 bg-white flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                                    <i data-lucide="medal" class="w-5 h-5"></i>
                                </div>
                                <h3 class="text-lg font-bold text-slate-800">Top 10 Pencacah</h3>
                            </div>
                            <span class="text-xs font-medium bg-slate-100 text-slate-500 px-2.5 py-1 rounded-full">Kabupaten</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table id="lbKabPclTable" class="w-full text-sm text-left">
                                <thead class="text-xs text-slate-500 bg-slate-50 uppercase font-semibold border-b border-slate-100">
                                    <tr>
                                        <th class="px-6 py-4 w-16">#</th>
                                        <th class="px-6 py-4">Nama Pencacah</th>
                                        <th class="px-6 py-4">Kecamatan</th>
                                        <th class="px-6 py-4 text-right">Prelist</th>
                                        <th class="px-6 py-4 text-right">Progress</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-700"></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Top 10 Pengawas Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
                        <div class="px-6 py-5 border-b border-slate-100 bg-white flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                                    <i data-lucide="award" class="w-5 h-5"></i>
                                </div>
                                <h3 class="text-lg font-bold text-slate-800">Top 10 Pengawas</h3>
                            </div>
                            <span class="text-xs font-medium bg-slate-100 text-slate-500 px-2.5 py-1 rounded-full">Kabupaten</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table id="lbKabPmlTable" class="w-full text-sm text-left">
                                <thead class="text-xs text-slate-500 bg-slate-50 uppercase font-semibold border-b border-slate-100">
                                    <tr>
                                        <th class="px-6 py-4 w-16">#</th>
                                        <th class="px-6 py-4">Nama Pengawas</th>
                                        <th class="px-6 py-4">Kecamatan</th>
                                        <th class="px-6 py-4 text-right">Prelist</th>
                                        <th class="px-6 py-4 text-right">Progress</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-700"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>


            <!-- View: Target Harian -->
            <div id="view-target-harian" class="view-section max-w-7xl mx-auto space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-white flex flex-col gap-4">
                        <div class="flex items-center gap-2">
                            <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                                <i data-lucide="target" class="w-5 h-5"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-800">Target Harian Petugas</h3>
                        </div>
                        
                        <div class="flex flex-wrap gap-4 items-end bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Tanggal Mulai</label>
                                <input type="date" id="targetStartDate" class="bg-white border border-slate-200 text-slate-700 py-2 px-3 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" value="2026-06-15">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Tanggal Pemantauan</label>
                                <input type="date" id="targetCurrentDate" class="bg-white border border-slate-200 text-slate-700 py-2 px-3 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Hari Kerja (Tanpa Minggu)</label>
                                <input type="number" id="targetWorkingDays" class="bg-slate-100 border border-slate-200 text-slate-500 py-2 px-3 rounded-lg text-sm w-24 font-bold cursor-not-allowed focus:outline-none pointer-events-none" readonly disabled tabindex="-1">
                            </div>
                            <div class="flex-1 min-w-[20px]"></div>
                            <div class="relative">
                                <select id="filterTargetKecamatan" class="appearance-none bg-white border border-slate-200 text-slate-700 py-2 pl-4 pr-10 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 cursor-pointer">
                                    <option value="">Semua Kecamatan</option>
                                </select>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                            </div>
                            <div class="relative">
                                <select id="filterTargetRole" class="appearance-none bg-white border border-slate-200 text-slate-700 py-2 pl-4 pr-10 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 cursor-pointer">
                                    <option value="Pencacah">Pencacah</option>
                                    <option value="Pengawas">Pengawas</option>
                                </select>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table id="targetHarianTable" class="w-full text-sm text-left">
                            <thead class="text-xs text-slate-500 bg-slate-50 uppercase font-semibold border-b border-slate-100">
                                <tr>
                                    <th class="px-6 py-4 w-16">No</th>
                                    <th class="px-6 py-4">Nama Petugas</th>
                                    <th class="px-6 py-4">Kecamatan</th>
                                    <th class="px-6 py-4 text-right">Target Total</th>
                                    <th class="px-6 py-4 text-right">Target/Hari</th>
                                    <th class="px-6 py-4 text-right bg-brand-50">Target S.d Hari Ini</th>
                                    <th class="px-6 py-4 text-right bg-emerald-50">Realisasi</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- User Tables dynamically injected here -->
            <div id="userViewsContainer" class="max-w-7xl mx-auto space-y-6"></div>

            <div class="h-12"></div>
        </div>
    </main>

    <script>
        // Initialize Lucide Icons immediately for static DOM elements
        lucide.createIcons();
        
        // Mobile Sidebar Logic
        const menuToggle = document.getElementById('menuToggle');
        const closeSidebarBtn = document.getElementById('closeSidebarBtn');
        const sidebar = document.getElementById('sidebar');
        const mobileOverlay = document.getElementById('mobileOverlay');

        function toggleSidebar() {
            const isClosed = sidebar.classList.contains('-translate-x-full');
            if (isClosed) {
                mobileOverlay.classList.remove('hidden');
                setTimeout(() => {
                    sidebar.classList.remove('-translate-x-full');
                    mobileOverlay.classList.remove('opacity-0');
                }, 10);
            } else {
                sidebar.classList.add('-translate-x-full');
                mobileOverlay.classList.add('opacity-0');
                setTimeout(() => {
                    mobileOverlay.classList.add('hidden');
                }, 300);
            }
        }

        if (menuToggle) menuToggle.addEventListener('click', toggleSidebar);
        if (closeSidebarBtn) closeSidebarBtn.addEventListener('click', toggleSidebar);
        if (mobileOverlay) mobileOverlay.addEventListener('click', toggleSidebar);

        // Auto close sidebar on mobile when menu clicked
        document.getElementById('sideMenu').addEventListener('click', (e) => {
            if (e.target.closest('.menu-item') && window.innerWidth < 768) {
                if (!sidebar.classList.contains('-translate-x-full')) {
                    toggleSidebar();
                }
            }
        });
        document.getElementById('dynamicMenusContainer').addEventListener('click', (e) => {
            if (e.target.closest('.menu-item') && window.innerWidth < 768) {
                if (!sidebar.classList.contains('-translate-x-full')) {
                    toggleSidebar();
                }
            }
        });
    </script>

    <!-- Upload Modal -->
    <div id="uploadModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('uploadModal').classList.add('hidden')"></div>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 relative z-10 mx-4">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-xl font-bold text-slate-800">Upload Data Monitoring</h3>
                <button onclick="document.getElementById('uploadModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form action="{{ route('upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-5">
                    <label class="block mb-2 text-sm font-medium text-slate-700" for="file">Pilih file Data Monitoring (Excel/CSV)</label>
                    <input class="block w-full text-sm text-slate-500 border border-slate-300 rounded-lg cursor-pointer bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand-500" id="file" name="file" type="file" accept=".xlsx,.xls,.csv" required>
                    <p class="mt-1 text-xs text-slate-500">Maksimal 50MB.</p>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-300 transition-colors">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-brand-600 rounded-lg hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 transition-colors">Upload</button>
                </div>
            </form>
        </div>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
