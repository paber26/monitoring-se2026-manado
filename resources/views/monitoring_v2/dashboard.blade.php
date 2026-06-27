@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Summary Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
                <i data-lucide="file-text" class="w-7 h-7"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">Total Assignment Dikerjakan</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($totalDocs, 0, ',', '.') }}</h3>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="target" class="w-7 h-7"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">Total SLS Target</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($totalTargetSls, 0, ',', '.') }}</h3>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center">
                <i data-lucide="map" class="w-7 h-7"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">Total SLS Dikerjakan</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($totalSlsDikerjakan, 0, ',', '.') }}</h3>
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
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const pivotData = @json($pivotData);
        const statusCounts = @json($statusCounts);
        
        // Setup Chart
        const regionDataArray = Object.keys(pivotData).map(r => ({
            region: r,
            total: pivotData[r].total
        }));
        
        // Sort by total descending
        regionDataArray.sort((a, b) => b.total - a.total);
        
        const regionLabels = regionDataArray.map(d => d.region);
        const regionTotals = regionDataArray.map(d => d.total);
        
        // Render Region Chart
        const regionCtx = document.getElementById('regionChart');
        if (regionCtx) {
            new Chart(regionCtx, {
                type: 'bar',
                data: {
                    labels: regionLabels,
                    datasets: [{
                        label: 'Total Dikerjakan',
                        data: regionTotals,
                        backgroundColor: '#0ea5e9',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, border: { display: false } }
                    }
                }
            });
        }
        
        // Render Status Chart
        const statusLabels = Object.keys(statusCounts);
        const statusValues = Object.values(statusCounts);
        
        const statusCtx = document.getElementById('statusChart');
        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusValues,
                        backgroundColor: [
                            '#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#f97316'
                        ],
                        borderWidth: 2,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } }
                    },
                    cutout: '65%'
                }
            });
        }
    });
</script>
@endpush
