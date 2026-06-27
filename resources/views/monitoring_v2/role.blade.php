@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
        <div class="px-6 py-5 border-b border-slate-100 bg-white flex justify-between items-center flex-wrap gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-slate-50 text-slate-600 rounded-lg">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Progres Per {{ $role == 'Pengawas' ? 'PML' : 'PPL' }}</h3>
            </div>
            
            <div class="flex items-center gap-3">
                <select id="kecamatanFilter" class="text-sm border-slate-200 rounded-lg text-slate-600 focus:ring-brand-500 focus:border-brand-500 py-2 pl-3 pr-8">
                    <option value="">Semua Kecamatan</option>
                    @php
                        $allKec = [];
                        foreach($leaderboard as $d) {
                            foreach(array_keys($d['kecamatans']) as $k) {
                                $allKec[$k] = true;
                            }
                        }
                        $allKec = array_keys($allKec);
                        sort($allKec);
                    @endphp
                    @foreach($allKec as $k)
                        <option value="{{ $k }}">{{ $k }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <div class="px-6 py-3 bg-slate-50 border-b border-slate-100 text-xs text-slate-500 flex items-center gap-2">
            <i data-lucide="info" class="w-4 h-4"></i> Klik baris nama petugas untuk melihat rincian progres per SLS
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left whitespace-nowrap" id="dataTable">
                <thead class="text-[11px] text-slate-500 bg-slate-50/50 uppercase font-bold border-b border-slate-100 tracking-wider">
                    <tr>
                        <th class="px-4 py-4 w-10 text-center"></th>
                        <th class="px-4 py-4 w-16">No</th>
                        <th class="px-6 py-4">Nama</th>
                        <th class="px-4 py-4 text-center">Total SLS</th>
                        <th class="px-6 py-4 text-right">Realisasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @php $index = 1; @endphp
                    @foreach($leaderboard as $d)
                        @php
                            arsort($d['kecamatans']);
                            $domKec = array_key_first($d['kecamatans']);
                            
                            $realisasi = $d['total'];
                            $targetVal = $d['target'];
                            $slsCount = $d['sls_count'];
                            
                            $pct = $targetVal > 0 ? round(($realisasi / $targetVal) * 100, 2) : 0;
                        @endphp
                        
                        {{-- Main Row --}}
                        <tr class="hover:bg-slate-50 transition-colors data-row cursor-pointer group" data-kecamatan="{{ $domKec }}" onclick="toggleDetail('detail-{{$index}}', 'icon-{{$index}}')">
                            <td class="px-4 py-4 text-center text-slate-400 group-hover:text-brand-500">
                                <i id="icon-{{$index}}" data-lucide="chevron-right" class="w-4 h-4 inline-block transition-transform duration-200"></i>
                            </td>
                            <td class="px-4 py-4 text-slate-500 index-col">{{ $index }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $d['name'] }}</td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-brand-100 text-brand-700 text-xs font-bold">{{ $slsCount }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex flex-col items-end gap-1">
                                    <div class="text-xs font-bold">
                                        <span class="text-brand-600 text-sm">{{ $realisasi }}</span>
                                        <span class="text-slate-400 font-normal"> / {{ $targetVal }}</span>
                                    </div>
                                    <div class="w-24 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-brand-500 h-1.5 rounded-full" style="width: {{ min($pct, 100) }}%"></div>
                                    </div>
                                    <span class="text-[10px] text-slate-500">{{ $pct }}%</span>
                                </div>
                            </td>
                        </tr>
                        
                        {{-- Detail Row --}}
                        <tr id="detail-{{$index}}" class="hidden bg-slate-50/50 detail-row" data-kecamatan="{{ $domKec }}">
                            <td colspan="5" class="p-0 border-b border-slate-200">
                                <div class="px-8 py-6 border-l-2 border-brand-500 ml-4">
                                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                                        <i data-lucide="layout-list" class="w-4 h-4 text-brand-500"></i>
                                        DETAIL SLS &mdash; <span class="text-slate-700">{{ $d['name'] }}</span>
                                    </h4>
                                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                                        <table class="w-full text-[11px] text-left whitespace-nowrap">
                                            <thead class="bg-slate-50 text-slate-500 uppercase font-bold border-b border-slate-100">
                                                <tr>
                                                    <th class="px-4 py-3">Kode SLS</th>
                                                    <th class="px-3 py-3 text-center">Open</th>
                                                    <th class="px-3 py-3 text-center">Draft</th>
                                                    <th class="px-3 py-3 text-center">Submit<br>(Pencacah)</th>
                                                    <th class="px-3 py-3 text-center">Submit<br>(Respondent)</th>
                                                    <th class="px-3 py-3 text-center">Approved</th>
                                                    <th class="px-3 py-3 text-center">Rejected</th>
                                                    <th class="px-3 py-3 text-center">Revoked</th>
                                                    <th class="px-3 py-3 text-center">Completed</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100">
                                                @foreach($d['sls_details'] as $sls)
                                                    <tr class="hover:bg-slate-50 transition-colors">
                                                        <td class="px-4 py-3 font-semibold text-slate-700">{{ $sls['kode_sls'] }}</td>
                                                        <td class="px-3 py-3 text-center">{!! $sls['open'] > 0 ? '<span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-orange-100 text-orange-700 font-bold">'.$sls['open'].'</span>' : '<span class="text-slate-300 font-medium">0</span>' !!}</td>
                                                        <td class="px-3 py-3 text-center">{!! $sls['draft'] > 0 ? '<span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-slate-200 text-slate-700 font-bold">'.$sls['draft'].'</span>' : '<span class="text-slate-300 font-medium">0</span>' !!}</td>
                                                        <td class="px-3 py-3 text-center">{!! $sls['submit_pencacah'] > 0 ? '<span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-emerald-100 text-emerald-700 font-bold">'.$sls['submit_pencacah'].'</span>' : '<span class="text-slate-300 font-medium">0</span>' !!}</td>
                                                        <td class="px-3 py-3 text-center">{!! $sls['submit_respondent'] > 0 ? '<span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-blue-100 text-blue-700 font-bold">'.$sls['submit_respondent'].'</span>' : '<span class="text-slate-300 font-medium">0</span>' !!}</td>
                                                        <td class="px-3 py-3 text-center">{!! $sls['approved'] > 0 ? '<span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-emerald-500 text-white font-bold shadow-sm">'.$sls['approved'].'</span>' : '<span class="text-slate-300 font-medium">0</span>' !!}</td>
                                                        <td class="px-3 py-3 text-center">{!! $sls['rejected'] > 0 ? '<span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-red-100 text-red-700 font-bold">'.$sls['rejected'].'</span>' : '<span class="text-slate-300 font-medium">0</span>' !!}</td>
                                                        <td class="px-3 py-3 text-center">{!! $sls['revoked'] > 0 ? '<span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-red-500 text-white font-bold shadow-sm">'.$sls['revoked'].'</span>' : '<span class="text-slate-300 font-medium">0</span>' !!}</td>
                                                        <td class="px-3 py-3 text-center">{!! $sls['completed'] > 0 ? '<span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-purple-100 text-purple-700 font-bold">'.$sls['completed'].'</span>' : '<span class="text-slate-300 font-medium">0</span>' !!}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @php $index++; @endphp
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleDetail(detailId, iconId) {
        const detailRow = document.getElementById(detailId);
        const icon = document.getElementById(iconId);
        
        if (detailRow.classList.contains('hidden')) {
            detailRow.classList.remove('hidden');
            icon.style.transform = 'rotate(90deg)';
        } else {
            detailRow.classList.add('hidden');
            icon.style.transform = 'rotate(0deg)';
        }
    }

    document.getElementById('kecamatanFilter').addEventListener('change', function() {
        const filter = this.value;
        const mainRows = document.querySelectorAll('.data-row');
        const detailRows = document.querySelectorAll('.detail-row');
        
        let visibleIndex = 1;
        
        // Hide all detail rows and reset icons
        detailRows.forEach(row => {
            row.classList.add('hidden');
        });
        
        document.querySelectorAll('[id^="icon-"]').forEach(icon => {
            icon.style.transform = 'rotate(0deg)';
        });
        
        mainRows.forEach(row => {
            if (filter === '' || row.getAttribute('data-kecamatan') === filter) {
                row.style.display = '';
                row.querySelector('.index-col').innerText = visibleIndex++;
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>
@endpush
@endsection
