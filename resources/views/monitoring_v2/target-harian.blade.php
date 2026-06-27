@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
        <div class="px-6 py-5 border-b border-slate-100 bg-white flex flex-col gap-4">
            <div class="flex items-center gap-2">
                <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                    <i data-lucide="target" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Kinerja Petugas</h3>
            </div>
            
            <form method="GET" action="{{ route('target.harian') }}" class="flex flex-wrap gap-4 items-end bg-slate-50 p-4 rounded-xl border border-slate-100" id="filterForm">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" onchange="document.getElementById('filterForm').submit()" class="bg-white border border-slate-200 text-slate-700 py-2 px-3 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Tanggal Pemantauan</label>
                    <input type="date" name="current_date" value="{{ $currentDate }}" onchange="document.getElementById('filterForm').submit()" class="bg-white border border-slate-200 text-slate-700 py-2 px-3 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Hari Pencacahan (Tanpa Minggu) Ke-</label>
                    <input type="number" value="{{ $elapsedWorkingDays }}" class="bg-slate-100 border border-slate-200 text-slate-500 py-2 px-3 rounded-lg text-sm w-24 font-bold cursor-not-allowed focus:outline-none pointer-events-none" readonly disabled tabindex="-1">
                </div>
                <div class="flex-1 min-w-[20px]"></div>
                <div class="relative">
                    <select name="kecamatan" onchange="document.getElementById('filterForm').submit()" class="appearance-none bg-white border border-slate-200 text-slate-700 py-2 pl-4 pr-10 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 cursor-pointer">
                        <option value="">Semua Kecamatan</option>
                        @foreach($uniqueKecamatan as $kec)
                            <option value="{{ $kec }}" {{ $kecamatan == $kec ? 'selected' : '' }}>{{ $kec }}</option>
                        @endforeach
                    </select>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                </div>
                <div class="relative">
                    <select name="role" onchange="document.getElementById('filterForm').submit()" class="appearance-none bg-white border border-slate-200 text-slate-700 py-2 pl-4 pr-10 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 cursor-pointer">
                        <option value="Pencacah" {{ $role == 'Pencacah' ? 'selected' : '' }}>Pencacah</option>
                        <option value="Pengawas" {{ $role == 'Pengawas' ? 'selected' : '' }}>Pengawas</option>
                    </select>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                </div>
            </form>
        </div>

        <div class="px-6 py-3 bg-slate-50 border-b border-slate-100 text-xs text-slate-500 flex items-center gap-2">
            <i data-lucide="info" class="w-4 h-4"></i> Klik baris nama petugas untuk melihat rincian progres per SLS
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-slate-500 bg-slate-50 uppercase font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-4 w-10 text-center"></th>
                        <th class="px-4 py-4 w-12">No</th>
                        <th class="px-6 py-4">Nama Petugas</th>
                        <th class="px-6 py-4 text-center">Kecamatan</th>
                        <th class="px-6 py-4 text-right">Target Total</th>
                        <th class="px-6 py-4 text-right">Target/Hari</th>
                        <th class="px-6 py-4 text-right bg-brand-50/50">Target S.d Hari Ini</th>
                        <th class="px-6 py-4 text-right bg-emerald-50/50">Realisasi</th>
                        <th class="px-6 py-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @php $index = 1; @endphp
                    @foreach($targetData as $d)
                        @php
                            arsort($d['kecamatans']);
                            $domKec = array_key_first($d['kecamatans']);
                            $targetVal = $targets[$d['username']] ?? 0;
                            $targetPerHari = $workingDays > 0 ? ($targetVal / $workingDays) : 0;
                            
                            $minTarget = floor($targetPerHari);
                            $maxTarget = ceil($targetPerHari);
                            $targetRangeText = ($minTarget == $maxTarget) ? $minTarget : $minTarget . ' sd ' . $maxTarget;
                            
                            $targetSdHariIni = min($targetVal, $targetPerHari * $elapsedWorkingDays);
                            $isLate = $d['total'] < $targetSdHariIni;
                            $isFinished = $d['total'] >= $targetVal;
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors cursor-pointer group" onclick="toggleDetail('detail-{{$index}}', 'icon-{{$index}}')">
                            <td class="px-4 py-3 text-center text-slate-400 group-hover:text-brand-500">
                                <i id="icon-{{$index}}" data-lucide="chevron-right" class="w-4 h-4 inline-block transition-transform duration-200"></i>
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $index }}</td>
                            <td class="px-6 py-3 font-semibold text-slate-800">{{ $d['name'] }}</td>
                            <td class="px-6 py-3 text-center text-slate-600">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-600">{{ $domKec }}</span>
                            </td>
                            <td class="px-6 py-3 text-right font-semibold text-slate-700">{{ number_format($targetVal, 0, ',', '.') }}</td>
                            <td class="px-6 py-3 text-right font-semibold text-slate-500">{{ $targetRangeText }}</td>
                            <td class="px-6 py-3 text-right font-semibold text-brand-700 bg-brand-50/30">{{ number_format($targetSdHariIni, 1, ',', '.') }}</td>
                            <td class="px-6 py-3 text-right bg-emerald-50/30">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $isFinished ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-800' }}">
                                    {{ number_format($d['total'], 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-center">
                                @if($targetVal == 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600">Tidak Ada Target</span>
                                @elseif($isFinished)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">
                                        <i data-lucide="check-circle" class="w-3 h-3"></i> Tercapai
                                    </span>
                                @elseif($isLate)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                        <i data-lucide="alert-circle" class="w-3 h-3"></i> Tertinggal
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-brand-100 text-brand-800">
                                        <i data-lucide="trending-up" class="w-3 h-3"></i> Tercapai
                                    </span>
                                @endif
                            </td>
                        </tr>
                        
                        {{-- Detail Row --}}
                        <tr id="detail-{{$index}}" class="hidden bg-slate-50/50">
                            <td colspan="9" class="p-0 border-b border-slate-200">
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
                                                @if(!empty($d['sls_details']))
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
                                                @else
                                                    <tr>
                                                        <td colspan="9" class="px-4 py-8 text-center text-slate-400 italic">Belum ada data SLS yang terhubung untuk petugas ini</td>
                                                    </tr>
                                                @endif
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
</script>
@endpush
@endsection
