@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
        <div class="px-6 py-5 border-b border-slate-100 bg-white flex justify-between items-center flex-wrap gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-slate-50 text-slate-600 rounded-lg">
                    <i data-lucide="bar-chart-2" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Kinerja - {{ $role }}</h3>
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
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left whitespace-nowrap" id="dataTable">
                <thead class="text-[11px] text-slate-500 bg-slate-50 uppercase font-bold border-b border-slate-100 tracking-wider">
                    <tr>
                        <th class="px-6 py-4 w-16">No.</th>
                        <th class="px-6 py-4">Nama Petugas</th>
                        <th class="px-4 py-4 text-center">Prelist</th>
                        <th class="px-4 py-4 text-center">Dikerjakan</th>
                        <th class="px-4 py-4 text-center">Approved By Pengawas</th>
                        <th class="px-4 py-4 text-center">Draft</th>
                        <th class="px-4 py-4 text-center">Rejected By Pengawas</th>
                        <th class="px-4 py-4 text-center">Revoked By Pengawas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @php $index = 1; @endphp
                    @foreach($leaderboard as $d)
                        @php
                            arsort($d['kecamatans']);
                            $domKec = array_key_first($d['kecamatans']);
                            
                            $prelist = $d['total'];
                            $approved = $d['statuses']['APPROVED BY Pengawas'] ?? 0;
                            $rejected = $d['statuses']['REJECTED BY Pengawas'] ?? 0;
                            $revoked = $d['statuses']['REVOKED BY Pengawas'] ?? 0;
                            $draft = $d['statuses']['DRAFT'] ?? 0;
                            $submittedPcl = $d['statuses']['SUBMITTED BY Pencacah'] ?? 0;
                            
                            $dikerjakan = $approved + $rejected + $revoked + $submittedPcl;
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors data-row" data-kecamatan="{{ $domKec }}">
                            <td class="px-6 py-4 text-slate-500">{{ $index++ }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $d['name'] }}</td>
                            <td class="px-4 py-4 text-center font-medium text-slate-700">{{ $prelist > 0 ? $prelist : '-' }}</td>
                            <td class="px-4 py-4 text-center">
                                @if($dikerjakan > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-700">
                                        {{ $dikerjakan }}
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center font-medium {{ $approved > 0 ? 'text-slate-700' : 'text-slate-400' }}">{{ $approved > 0 ? $approved : '-' }}</td>
                            <td class="px-4 py-4 text-center font-medium {{ $draft > 0 ? 'text-slate-700' : 'text-slate-400' }}">{{ $draft > 0 ? $draft : '-' }}</td>
                            <td class="px-4 py-4 text-center font-medium {{ $rejected > 0 ? 'text-slate-700' : 'text-slate-400' }}">{{ $rejected > 0 ? $rejected : '-' }}</td>
                            <td class="px-4 py-4 text-center font-medium {{ $revoked > 0 ? 'text-slate-700' : 'text-slate-400' }}">{{ $revoked > 0 ? $revoked : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('kecamatanFilter').addEventListener('change', function() {
        const filter = this.value;
        const rows = document.querySelectorAll('.data-row');
        let visibleIndex = 1;
        
        rows.forEach(row => {
            if (filter === '' || row.getAttribute('data-kecamatan') === filter) {
                row.style.display = '';
                row.querySelector('td:first-child').innerText = visibleIndex++;
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>
@endpush
@endsection
