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
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="icon" type="image/png" href="{{ asset('logo-small.png') }}">
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
        /* Custom table styling override for overflow handling */
        .data-table th, .data-table td {
            white-space: nowrap;
        }
        
        .menu-item.active {
            background-color: #1e293b; /* slate-800 */
            color: #ffffff;
        }
        .menu-item:not(.active) {
            color: #cbd5e1; /* slate-300 */
        }
        .menu-item.active .nav-icon {
            color: #0ea5e9; /* brand-500 */
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased flex h-screen overflow-hidden relative">

    @php
        $roles = \App\Models\Assignment::select('current_user_survey_role_name')
                    ->whereNotNull('current_user_survey_role_name')
                    ->where('current_user_survey_role_name', '!=', '')
                    ->distinct()
                    ->pluck('current_user_survey_role_name')
                    ->sort();
        
        $lastUpdate = \App\Models\Assignment::max('updated_at');
        $timeStr = $lastUpdate ? \Carbon\Carbon::parse($lastUpdate)->translatedFormat('d F Y H:i:s') : 'Tidak diketahui';
    @endphp

    <!-- Mobile Overlay -->
    <div id="mobileOverlay" class="fixed inset-0 bg-slate-900/50 z-20 hidden md:hidden opacity-0 transition-opacity duration-300"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="absolute inset-y-0 left-0 z-30 w-64 bg-sidebar flex flex-col flex-shrink-0 h-full transition-transform duration-300 -translate-x-full md:relative md:translate-x-0">
        <!-- Logo Area -->
        <div class="h-16 md:h-20 flex items-center px-6 border-b border-slate-700/50 justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('logo.png') }}" alt="Logo BPS" class="w-8 h-8 md:w-9 md:h-9 object-contain drop-shadow-sm">
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
            <a href="{{ route('dashboard') }}" class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-colors group">
                <i data-lucide="pie-chart" class="w-5 h-5 {{ request()->routeIs('dashboard') ? '' : 'text-slate-400' }} transition-colors nav-icon"></i>
                <span class="text-sm font-medium nav-text">Dashboard Utama</span>
            </a>
            
            <div class="pt-4 pb-2">
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Performa</p>
            </div>
            
            <a href="{{ route('leaderboard') }}" class="menu-item {{ request()->routeIs('leaderboard') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-colors relative group">
                <i data-lucide="trophy" class="w-5 h-5 text-yellow-500 nav-icon"></i>
                <span class="text-sm font-medium nav-text">Leaderboard Petugas</span>
            </a>
            
            <a href="{{ route('target.harian') }}" class="menu-item {{ request()->routeIs('target.harian') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-colors relative group">
                <i data-lucide="target" class="w-5 h-5 {{ request()->routeIs('target.harian') ? '' : 'text-slate-400' }} nav-icon"></i>
                <span class="text-sm font-medium nav-text">Kinerja Petugas</span>
            </a>

            <div class="pt-4 pb-2">
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Laporan Wilayah</p>
            </div>
            
            <a href="{{ route('dashboard.desa') }}" class="hidden menu-item {{ request()->routeIs('dashboard.desa') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-colors group">
                <i data-lucide="layout-dashboard" class="w-5 h-5 {{ request()->routeIs('dashboard.desa') ? '' : 'text-brand-500' }} transition-colors nav-icon"></i>
                <span class="text-sm font-medium nav-text">Dashboard Desa</span>
            </a>
            <a href="{{ route('progres.kecamatan') }}" class="menu-item {{ request()->routeIs('progres.kecamatan') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-colors group">
                <i data-lucide="map" class="w-5 h-5 {{ request()->routeIs('progres.kecamatan') ? '' : 'text-slate-400' }} transition-colors nav-icon"></i>
                <span class="text-sm font-medium nav-text">Progres Kecamatan</span>
            </a>
            <a href="{{ route('progres.sls') }}" class="menu-item {{ request()->routeIs('progres.sls') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-colors group">
                <i data-lucide="map-pin" class="w-5 h-5 {{ request()->routeIs('progres.sls') ? '' : 'text-slate-400' }} transition-colors nav-icon"></i>
                <span class="text-sm font-medium nav-text">Progres SLS</span>
            </a>
        </nav>
        <div class="px-4 pb-4">
            <div class="flex items-center justify-between text-xs text-slate-400 bg-slate-800 rounded-md px-3 py-2">
                <div class="flex items-center gap-2">
                    <i data-lucide="bar-chart" class="w-4 h-4 text-brand-500"></i>
                    <span>Total Akses</span>
                </div>
                <span class="font-bold text-white">{{ isset($visitorCount) ? number_format($visitorCount, 0, ',', '.') : 0 }}</span>
            </div>
        </div>
        
        <!-- User Profile (Bottom) -->
        <div class="p-4 border-t border-slate-700/50">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-slate-700 flex items-center justify-center text-sm font-medium text-white">
                    <i data-lucide="user" class="w-4 h-4"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-white">Admin Kab</p>
                    <p class="text-xs text-slate-400">BPS Kota Manado</p>
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
                    <h2 class="text-xl md:text-2xl font-bold text-slate-800">Monitoring Pencacahan SE2026 BPS Kota Manado</h2>
                </div>
                {{-- <div class="flex gap-2 self-start md:self-auto">
                    <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" class="bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition-colors shadow-sm">
                        <i data-lucide="upload" class="w-4 h-4"></i> Upload Data Monitoring
                    </button>
                </div> --}}
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

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto p-4 md:p-8">
            @yield('content')
            <div class="h-12"></div>
        </div>
    </main>

    <script>
        lucide.createIcons();
        
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
    </script>

    <!-- Upload Modal -->
    <div id="uploadModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeUploadModal()"></div>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 relative z-10 mx-4">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-xl font-bold text-slate-800">Upload Data Monitoring</h3>
                <button id="closeModalBtn" onclick="closeUploadModal()" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <form id="uploadForm" action="{{ route('upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-5" id="fileInputContainer">
                    <label class="block mb-2 text-sm font-medium text-slate-700" for="file">Pilih file Data Monitoring (Excel/CSV)</label>
                    <input class="block w-full text-sm text-slate-500 border border-slate-300 rounded-lg cursor-pointer bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand-500" id="file" name="file" type="file" accept=".xlsx,.xls,.csv" required>
                    <p class="mt-1 text-xs text-slate-500">Maksimal 50MB. Proses upload & sinkronisasi bisa memakan waktu hingga 1-2 menit.</p>
                </div>
                
                <!-- Progress UI (Hidden initially) -->
                <div id="progressContainer" class="hidden mb-5 space-y-3">
                    <div class="flex justify-between text-sm font-medium">
                        <span id="progressStatus" class="text-slate-700">Mempersiapkan...</span>
                        <span id="progressPercent" class="text-brand-600">0%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                        <div id="progressBar" class="bg-brand-500 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <p id="progressDetail" class="text-xs text-slate-500 truncate">-</p>
                </div>

                <div class="flex justify-end gap-3" id="actionButtons">
                    <button type="button" onclick="closeUploadModal()" class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-300 transition-colors">Batal</button>
                    <button type="submit" id="submitBtn" class="px-4 py-2 text-sm font-medium text-white bg-brand-600 rounded-lg hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 transition-colors">Upload</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function closeUploadModal() {
            if (!document.getElementById('submitBtn').disabled) {
                document.getElementById('uploadModal').classList.add('hidden');
            }
        }

        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const fileInput = document.getElementById('file');
            if (!fileInput.files.length) return;

            const formData = new FormData(this);
            
            // UI changes
            document.getElementById('fileInputContainer').classList.add('hidden');
            document.getElementById('progressContainer').classList.remove('hidden');
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin inline-block mr-2"></i>Uploading...';
            document.getElementById('closeModalBtn').classList.add('hidden');
            document.querySelector('#actionButtons button[type="button"]').classList.add('hidden');
            lucide.createIcons();

            let pollInterval = setInterval(async () => {
                try {
                    const res = await fetch("{{ route('upload.progress') }}");
                    const data = await res.json();
                    
                    if (data.status !== 'idle') {
                        let percent = 0;
                        if (data.total > 0) {
                            percent = Math.min(100, Math.round((data.current / data.total) * 100));
                        }
                        
                        document.getElementById('progressBar').style.width = percent + '%';
                        document.getElementById('progressPercent').innerText = percent + '%';
                        
                        if (data.status === 'reading' || data.status === 'importing') {
                            document.getElementById('progressStatus').innerText = 'Mengimpor ke Database...';
                            document.getElementById('progressDetail').innerText = 'Baris / SLS: ' + (data.sls || '-');
                        } else if (data.status === 'mapping') {
                            document.getElementById('progressStatus').innerText = 'Menyinkronkan Nama...';
                            document.getElementById('progressDetail').innerText = 'SLS Target: ' + (data.sls || '-');
                        } else if (data.status === 'error') {
                            clearInterval(pollInterval);
                            alert('Error: ' + data.message);
                            location.reload();
                        }
                    }
                } catch (err) {
                    console.log('Polling error', err);
                }
            }, 1000);

            // Send actual request
            fetch("{{ route('upload') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                clearInterval(pollInterval);
                if (data.success) {
                    document.getElementById('progressStatus').innerText = 'Selesai!';
                    document.getElementById('progressBar').style.width = '100%';
                    document.getElementById('progressPercent').innerText = '100%';
                    document.getElementById('progressDetail').innerText = 'Memuat ulang halaman...';
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alert(data.message || 'Terjadi kesalahan saat mengunggah');
                    location.reload();
                }
            })
            .catch(err => {
                clearInterval(pollInterval);
                alert('Request failed');
                location.reload();
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
