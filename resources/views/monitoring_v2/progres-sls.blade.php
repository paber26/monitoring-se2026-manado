@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
        <div class="px-6 py-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h3 class="text-lg font-bold text-slate-800">Progres Assignment Per SLS</h3>
            <form method="GET" action="{{ route('progres.sls') }}" class="flex gap-3" id="filterForm">
                <div class="relative">
                    <select name="kecamatan" onchange="document.getElementById('filterForm').submit()" class="appearance-none bg-slate-50 border border-slate-200 text-slate-700 py-2 pl-4 pr-10 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 cursor-pointer">
                        <option value="">Semua Kecamatan</option>
                        @foreach($uniqueKecamatan as $kec)
                            <option value="{{ $kec }}" {{ $filterKec == $kec ? 'selected' : '' }}>{{ $kec }}</option>
                        @endforeach
                    </select>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                </div>
                <div class="relative">
                    <select name="flag" onchange="document.getElementById('filterForm').submit()" class="appearance-none bg-slate-50 border border-slate-200 text-slate-700 py-2 pl-4 pr-10 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 cursor-pointer">
                        <option value="">Semua Flag SLS</option>
                        <option value="1" {{ $filterFlag === '1' ? 'selected' : '' }}>Ada Flag Open PBI (>0)</option>
                        <option value="0" {{ $filterFlag === '0' ? 'selected' : '' }}>Tidak Ada Flag (0)</option>
                    </select>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                </div>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left data-table">
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
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @php $index = 1; @endphp
                    @foreach($slsData as $d)
                        @php
                            $targetObj = isset($targets[$d->level_6_full_code]) ? $targets[$d->level_6_full_code] : null;
                            $targetPrelist = $targetObj ? $targetObj->target_value : 0;
                            $meta = $targetObj ? $targetObj->meta : [];
                            $flag = is_array($meta) && isset($meta['flag_sls_open_pbi']) ? $meta['flag_sls_open_pbi'] : 0;
                            $kk = is_array($meta) && isset($meta['kk_open_pbi']) ? $meta['kk_open_pbi'] : 0;
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3 text-slate-500">{{ $index++ }}</td>
                            <td class="px-6 py-3 font-semibold text-slate-800">{{ $d->level_3_name }}</td>
                            <td class="px-6 py-3 text-slate-600">{{ $d->level_4_name }}</td>
                            <td class="px-6 py-3 text-slate-600 max-w-xs truncate" title="{{ $d->level_5_name }}">{{ $d->level_5_name }}</td>
                            <td class="px-6 py-3 text-slate-500 font-mono text-xs">{{ $d->level_6_full_code }}</td>
                            <td class="px-6 py-3 text-right font-semibold text-slate-700">{{ number_format($targetPrelist, 0, ',', '.') }}</td>
                            <td class="px-6 py-3 text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                    {{ number_format($d->count, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-center">
                                @if($flag > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">{{ $flag }}</span>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-center">
                                @if($kk > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">{{ $kk }}</span>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
