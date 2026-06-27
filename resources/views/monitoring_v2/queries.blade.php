@extends('layouts.app')

@section('title', 'Query Update Per Kecamatan')

@section('content')
<div class="mb-6 flex justify-between items-end">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Query Update Per Kecamatan</h2>
        <p class="text-slate-500 mt-1">Gunakan query di bawah ini untuk melakukan pembaruan data secara parsial (maksimal 800 assignment per query).</p>
    </div>
</div>

@if(empty($queries))
<div class="bg-white rounded-xl border border-slate-200 p-8 text-center shadow-sm">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 text-slate-400 mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
        </svg>
    </div>
    <h3 class="text-lg font-medium text-slate-800">Belum Ada Data Query</h3>
    <p class="text-slate-500 mt-1">Data query belum di-generate atau file tidak ditemukan.</p>
</div>
@else
<div x-data="{ selectedKecamatan: '' }" class="space-y-6">
    
    <!-- Filter Section -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex-1 w-full max-w-md">
            <label for="kecamatan-filter" class="block text-sm font-medium text-slate-700 mb-1">Pilih Kecamatan</label>
            <select id="kecamatan-filter" x-model="selectedKecamatan" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-slate-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                <option value="">-- Pilih Kecamatan Terlebih Dahulu --</option>
                @foreach($queries as $kecCode => $data)
                    <option value="{{ $kecCode }}">[{{ $kecCode }}] {{ $data['name'] }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Empty State Prompt -->
    <div x-show="selectedKecamatan === ''" class="bg-blue-50 text-blue-700 p-4 rounded-xl border border-blue-100 flex items-center space-x-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 flex-shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-sm font-medium">Silakan pilih kecamatan pada menu dropdown di atas untuk menampilkan daftar query.</p>
    </div>

    <!-- Data List -->
    <div class="space-y-6" x-show="selectedKecamatan !== ''" x-cloak>
        @foreach($queries as $kecCode => $data)
        <div x-show="selectedKecamatan === '{{ $kecCode }}'" class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden" x-data="{ expanded: true }">
            <!-- Header -->
            <button @click="expanded = !expanded" class="w-full px-6 py-4 flex items-center justify-between bg-slate-50 hover:bg-slate-100 transition-colors focus:outline-none">
                <div class="flex items-center space-x-4">
                    <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <div class="text-left">
                        <h3 class="text-lg font-bold text-slate-800">Kecamatan: {{ $data['name'] }} ({{ $kecCode }})</h3>
                        <p class="text-sm text-slate-500">{{ count($data['chunks']) }} Chunk Query &bull; Total Assignment: {{ number_format($data['total_assignment'], 0, ',', '.') }}</p>
                    </div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 transform transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
            
            <!-- Content -->
            <div x-show="expanded" x-collapse x-cloak>
                <div class="p-6 border-t border-slate-100 space-y-6 bg-white">
                    @foreach($data['chunks'] as $chunk)
                    <div class="border border-slate-200 rounded-lg overflow-hidden">
                        <div class="bg-slate-50 border-b border-slate-200 px-4 py-3 flex justify-between items-center">
                            <div>
                                <span class="font-medium text-slate-700">Chunk {{ $loop->iteration }}</span>
                                <span class="ml-2 text-sm text-slate-500">({{ $chunk['total_assignment'] }} assignment)</span>
                            </div>
                            <button onclick="copyToClipboard('sql-chunk-{{ $kecCode }}-{{ $loop->iteration }}')" class="inline-flex items-center px-3 py-1.5 border border-slate-300 shadow-sm text-sm font-medium rounded-md text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                </svg>
                                Copy SQL
                            </button>
                        </div>
                        <div class="p-0">
                            <textarea id="sql-chunk-{{ $kecCode }}-{{ $loop->iteration }}" readonly class="w-full h-32 p-4 font-mono text-sm text-slate-800 bg-slate-900 !text-green-400 border-0 focus:ring-0 resize-none overflow-y-auto" style="tab-size: 4;">{{ $chunk['sql'] }}</textarea>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<script>
function copyToClipboard(elementId) {
    var copyText = document.getElementById(elementId);
    copyText.select();
    copyText.setSelectionRange(0, 99999); /* For mobile devices */
    
    // Copy the text inside the text field
    navigator.clipboard.writeText(copyText.value).then(function() {
        // Optional: show a small toast or change button text temporarily
        const btn = event.currentTarget;
        const originalText = btn.innerHTML;
        btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Copied!`;
        setTimeout(() => {
            btn.innerHTML = originalText;
        }, 2000);
    });
}
</script>
@endsection
