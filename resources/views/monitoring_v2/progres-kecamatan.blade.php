@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-800">Progres Assignment Per Kecamatan</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left data-table">
                <thead class="text-xs text-slate-500 bg-slate-50 uppercase font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4">No.</th>
                        <th class="px-6 py-4">Nama Kecamatan</th>
                        <th class="px-6 py-4 text-right">Prelist</th>
                        <th class="px-6 py-4 text-right">Dikerjakan</th>
                        @foreach($statusKeys as $status)
                            <th class="px-6 py-4 text-center">{{ $status }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @php $index = 1; @endphp
                    @foreach($pivotData as $regionName => $counts)
                        @php 
                            $targetVal = isset($targets[$regionName]) ? $targets[$regionName] : 0;
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3 font-medium text-slate-500">{{ $index++ }}</td>
                            <td class="px-6 py-3 font-semibold text-slate-800">{{ $regionName }}</td>
                            <td class="px-6 py-3 text-right font-semibold text-slate-700">{{ number_format($targetVal, 0, ',', '.') }}</td>
                            <td class="px-6 py-3 text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-brand-100 text-brand-800">
                                    {{ number_format($counts['total'], 0, ',', '.') }}
                                </span>
                            </td>
                            @foreach($statusKeys as $status)
                                @php $val = $counts[$status] ?? 0; @endphp
                                <td class="px-6 py-3 text-center">
                                    @if($val > 0)
                                        <span class="text-slate-700 font-semibold">{{ number_format($val, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
