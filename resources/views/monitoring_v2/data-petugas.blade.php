@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Data Petugas SE2026</h2>
        <button onclick="document.getElementById('uploadPetugasModal').classList.remove('hidden')" class="bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition-colors shadow-sm">
            <i data-lucide="upload" class="w-4 h-4"></i> Upload Excel Petugas
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-800 text-sm font-medium flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm font-medium flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4"></i> {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-sm text-slate-500 font-medium mb-1">Total Open</p>
            <p class="text-3xl font-bold text-blue-600">{{ number_format($summaries['open'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-sm text-slate-500 font-medium mb-1">Total Draft</p>
            <p class="text-3xl font-bold text-slate-600">{{ number_format($summaries['draft'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-sm text-slate-500 font-medium mb-1">Submitted (Pencacah)</p>
            <p class="text-3xl font-bold text-orange-500">{{ number_format($summaries['submitted_by_pencacah'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-sm text-slate-500 font-medium mb-1">Approved (Pengawas)</p>
            <p class="text-3xl font-bold text-green-600">{{ number_format($summaries['approved_by_pengawas'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-sm text-slate-500 font-medium mb-1">Rejected (Pengawas)</p>
            <p class="text-3xl font-bold text-red-600">{{ number_format($summaries['rejected_by_pengawas'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-sm text-slate-500 font-medium mb-1">Completed (Admin)</p>
            <p class="text-3xl font-bold text-emerald-600">{{ number_format($summaries['completed_by_admin_kabupaten'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-sm text-slate-500 font-medium mb-1">Submitted Respondent</p>
            <p class="text-3xl font-bold text-purple-600">{{ number_format($summaries['submitted_respondent'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="flex gap-2 mb-4">
        <button id="tab-pencacah" onclick="switchTab('pencacah')" class="px-5 py-2 rounded-lg text-sm font-semibold bg-brand-600 text-white shadow-sm transition-all">
            Pencacah <span class="ml-1 bg-white/20 px-1.5 py-0.5 rounded text-xs">{{ $petugasList->count() }}</span>
        </button>
        <button id="tab-pengawas" onclick="switchTab('pengawas')" class="px-5 py-2 rounded-lg text-sm font-semibold bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 transition-all">
            Pengawas <span class="ml-1 bg-slate-100 px-1.5 py-0.5 rounded text-xs">{{ $pengawasList->count() }}</span>
        </button>
    </div>

    <div id="panel-pencacah" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-200 bg-slate-50 flex flex-col md:flex-row justify-between items-center gap-4">
            <h3 class="font-semibold text-slate-800">Daftar Pencacah</h3>
            <div class="relative w-full md:w-64">
                <input type="text" id="searchPencacah" placeholder="Cari nama atau email..." class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm" id="pencacahTable">
                <thead class="bg-slate-50 text-slate-600 font-medium border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">Nama Petugas</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Kode Identitas</th>
                        <th class="px-4 py-3 text-center">Open</th>
                        <th class="px-4 py-3 text-center">Draft</th>
                        <th class="px-4 py-3 text-center">Submit Pencacah</th>
                        <th class="px-4 py-3 text-center">Approve Pengawas</th>
                        <th class="px-4 py-3 text-center">Reject Pengawas</th>
                        <th class="px-4 py-3 text-center">Selesai (Admin)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @forelse($petugasList as $p)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $p->nama ?: '-' }}</td>
                        <td class="px-4 py-3">{{ $p->email ?: '-' }}</td>
                        <td class="px-4 py-3">
                            @if($p->kode_identitas)
                                <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded text-xs font-mono">{{ $p->kode_identitas }}</span>
                            @else -
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center font-medium {{ $p->open > 0 ? 'text-blue-600' : 'text-slate-400' }}">{{ $p->open }}</td>
                        <td class="px-4 py-3 text-center font-medium {{ $p->draft > 0 ? 'text-slate-600' : 'text-slate-400' }}">{{ $p->draft }}</td>
                        <td class="px-4 py-3 text-center font-medium {{ $p->submitted_by_pencacah > 0 ? 'text-orange-500' : 'text-slate-400' }}">{{ $p->submitted_by_pencacah }}</td>
                        <td class="px-4 py-3 text-center font-medium {{ $p->approved_by_pengawas > 0 ? 'text-green-600' : 'text-slate-400' }}">{{ $p->approved_by_pengawas }}</td>
                        <td class="px-4 py-3 text-center font-medium {{ $p->rejected_by_pengawas > 0 ? 'text-red-600' : 'text-slate-400' }}">{{ $p->rejected_by_pengawas }}</td>
                        <td class="px-4 py-3 text-center font-medium {{ $p->completed_by_admin_kabupaten > 0 ? 'text-emerald-600' : 'text-slate-400' }}">{{ $p->completed_by_admin_kabupaten }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-slate-500">Belum ada data pencacah. Silakan upload file Excel.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="panel-pengawas" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hidden">
        <div class="p-4 border-b border-slate-200 bg-slate-50 flex flex-col md:flex-row justify-between items-center gap-4">
            <h3 class="font-semibold text-slate-800">Daftar Pengawas</h3>
            <div class="relative w-full md:w-64">
                <input type="text" id="searchPengawas" placeholder="Cari nama atau email..." class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm" id="pengawasTable">
                <thead class="bg-slate-50 text-slate-600 font-medium border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">Nama Pengawas</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Kode Identitas</th>
                        <th class="px-4 py-3 text-center">Open</th>
                        <th class="px-4 py-3 text-center">Draft</th>
                        <th class="px-4 py-3 text-center">Submit Pencacah</th>
                        <th class="px-4 py-3 text-center">Approve Pengawas</th>
                        <th class="px-4 py-3 text-center">Reject Pengawas</th>
                        <th class="px-4 py-3 text-center">Selesai (Admin)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @forelse($pengawasList as $p)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $p->nama ?: '-' }}</td>
                        <td class="px-4 py-3">{{ $p->email ?: '-' }}</td>
                        <td class="px-4 py-3">
                            @if($p->kode_identitas)
                                <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded text-xs font-mono">{{ $p->kode_identitas }}</span>
                            @else -
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center font-medium {{ $p->open > 0 ? 'text-blue-600' : 'text-slate-400' }}">{{ $p->open }}</td>
                        <td class="px-4 py-3 text-center font-medium {{ $p->draft > 0 ? 'text-slate-600' : 'text-slate-400' }}">{{ $p->draft }}</td>
                        <td class="px-4 py-3 text-center font-medium {{ $p->submitted_by_pencacah > 0 ? 'text-orange-500' : 'text-slate-400' }}">{{ $p->submitted_by_pencacah }}</td>
                        <td class="px-4 py-3 text-center font-medium {{ $p->approved_by_pengawas > 0 ? 'text-green-600' : 'text-slate-400' }}">{{ $p->approved_by_pengawas }}</td>
                        <td class="px-4 py-3 text-center font-medium {{ $p->rejected_by_pengawas > 0 ? 'text-red-600' : 'text-slate-400' }}">{{ $p->rejected_by_pengawas }}</td>
                        <td class="px-4 py-3 text-center font-medium {{ $p->completed_by_admin_kabupaten > 0 ? 'text-emerald-600' : 'text-slate-400' }}">{{ $p->completed_by_admin_kabupaten }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-slate-500">Belum ada data pengawas. Silakan upload file Excel Pengawas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="uploadPetugasModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closePetugasModal()"></div>
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 relative z-10 mx-4">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-xl font-bold text-slate-800">Upload Data Petugas</h3>
            <button onclick="closePetugasModal()" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <form action="{{ route('data.petugas.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label class="block mb-2 text-sm font-medium text-slate-700">Jenis Petugas</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 p-3 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                        <input type="radio" name="role" value="Pencacah" checked class="accent-brand-600">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Pencacah</p>
                            <p class="text-xs text-slate-500">Upload data pencacah</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-2 p-3 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                        <input type="radio" name="role" value="Pengawas" class="accent-brand-600">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Pengawas</p>
                            <p class="text-xs text-slate-500">Upload data pengawas</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="mb-5">
                <label class="block mb-2 text-sm font-medium text-slate-700" for="filePetugas">Pilih file Excel (.xlsx/.xls)</label>
                <input class="block w-full text-sm text-slate-500 border border-slate-300 rounded-lg cursor-pointer bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand-500" id="filePetugas" name="file" type="file" accept=".xlsx,.xls,.csv" required>
                <p class="mt-2 text-xs text-slate-500">Kolom: Nama, Email, Kode Identitas, Open, Draft, Submitted By Pencacah, dll.</p>
            </div>
            
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closePetugasModal()" class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 transition-colors">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-brand-600 rounded-lg hover:bg-brand-700 transition-colors">Upload</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function closePetugasModal() {
        document.getElementById('uploadPetugasModal').classList.add('hidden');
    }

    function switchTab(tab) {
        ['pencacah', 'pengawas'].forEach(t => {
            document.getElementById('panel-' + t).classList.add('hidden');
            const btn = document.getElementById('tab-' + t);
            btn.className = 'px-5 py-2 rounded-lg text-sm font-semibold bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 transition-all';
        });
        document.getElementById('panel-' + tab).classList.remove('hidden');
        document.getElementById('tab-' + tab).className = 'px-5 py-2 rounded-lg text-sm font-semibold bg-brand-600 text-white shadow-sm transition-all';
    }

    function makeSearchable(inputId, tableId) {
        document.getElementById(inputId).addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            document.querySelectorAll('#' + tableId + ' tbody tr').forEach(row => {
                if (row.cells.length > 1) {
                    let text = row.cells[0].textContent.toLowerCase() + row.cells[1].textContent.toLowerCase();
                    row.style.display = text.includes(filter) ? '' : 'none';
                }
            });
        });
    }

    makeSearchable('searchPencacah', 'pencacahTable');
    makeSearchable('searchPengawas', 'pengawasTable');
</script>
@endpush
@endsection
