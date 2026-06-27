@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
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
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 bg-slate-50 uppercase font-semibold border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4 w-16">#</th>
                            <th class="px-6 py-4">Nama Pencacah</th>
                            <th class="px-6 py-4">Kecamatan</th>
                            <th class="px-6 py-4 text-right">Prelist</th>
                            <th class="px-6 py-4 text-right">Progress</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @php $index = 1; @endphp
                        @foreach($pclData as $d)
                            @php
                                $targetVal = $targets[$d['username']] ?? 0;
                                arsort($d['kecamatans']);
                                $domKec = array_key_first($d['kecamatans']);
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-3 text-slate-500">{{ $index++ }}</td>
                                <td class="px-6 py-3 font-semibold text-slate-800">{{ $d['name'] }}</td>
                                <td class="px-6 py-3 text-slate-600">{{ $domKec }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-slate-700">{{ number_format($targetVal, 0, ',', '.') }}</td>
                                <td class="px-6 py-3 text-right">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-brand-100 text-brand-800">
                                        {{ number_format($d['total'], 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
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
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 bg-slate-50 uppercase font-semibold border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4 w-16">#</th>
                            <th class="px-6 py-4">Nama Pengawas</th>
                            <th class="px-6 py-4">Kecamatan</th>
                            <th class="px-6 py-4 text-right">Prelist</th>
                            <th class="px-6 py-4 text-right">Progress</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @php $index = 1; @endphp
                        @foreach($pmlData as $d)
                            @php
                                $targetVal = $targets[$d['username']] ?? 0;
                                arsort($d['kecamatans']);
                                $domKec = array_key_first($d['kecamatans']);
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-3 text-slate-500">{{ $index++ }}</td>
                                <td class="px-6 py-3 font-semibold text-slate-800">{{ $d['name'] }}</td>
                                <td class="px-6 py-3 text-slate-600">{{ $domKec }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-slate-700">{{ number_format($targetVal, 0, ',', '.') }}</td>
                                <td class="px-6 py-3 text-right">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                        {{ number_format($d['total'], 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
