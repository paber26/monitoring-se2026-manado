@extends('layouts.app')

@section('content')
<style>
    :root {
        --bps-orange: #E87722;
        --bps-orange-hover: #D0651A;
        --bps-yellow-light: #FFF9F0;
        --bps-border: #FEEBC8;
        --text-dark: #333333;
        --text-muted: #666666;
    }

    .dashboard-card {
        background: #FFFFFF;
        border-radius: 8px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        overflow: hidden;
        border: 1px solid #E5E7EB;
    }

    .dashboard-header {
        padding: 1.5rem;
        border-bottom: 1px solid #E5E7EB;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .dashboard-header h1 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-dark);
    }

    .tabs {
        display: flex;
        border-bottom: 2px solid #E5E7EB;
        overflow-x: auto;
        background: #FFFFFF;
    }

    .tab {
        padding: 1rem 1.5rem;
        font-weight: 600;
        font-size: 0.85rem;
        color: var(--text-muted);
        cursor: pointer;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        white-space: nowrap;
        text-transform: uppercase;
    }

    .tab:hover {
        color: var(--bps-orange);
    }

    .tab.active {
        color: var(--bps-orange);
        border-bottom-color: var(--bps-orange);
    }

    .dashboard-controls {
        padding: 1rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #FFFFFF;
        border-bottom: 1px solid var(--bps-border);
        gap: 1rem;
        flex-wrap: wrap;
    }

    .dashboard-controls select {
        padding: 0.5rem 2rem 0.5rem 1rem;
        border: 1px solid #E5E7EB;
        border-radius: 4px;
        background: #FFFFFF;
        color: var(--text-dark);
        font-size: 0.875rem;
        outline: none;
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23666666%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E");
        background-repeat: no-repeat;
        background-position: right 0.7rem top 50%;
        background-size: 0.65rem auto;
    }

    .dashboard-controls select:focus {
        border-color: var(--bps-orange);
    }

    .dashboard-table-responsive {
        overflow-x: auto;
        background: #FFFFFF;
    }

    .dashboard-table-responsive table {
        width: 100%;
        border-collapse: collapse;
        text-align: center;
    }

    .dashboard-table-responsive th {
        background: var(--bps-yellow-light);
        color: var(--text-dark);
        font-weight: 600;
        font-size: 0.75rem;
        padding: 1rem;
        border-bottom: 1px solid var(--bps-border);
        border-right: 1px solid var(--bps-border);
        white-space: nowrap;
    }
    
    .dashboard-table-responsive th:last-child { border-right: none; }

    .dashboard-table-responsive .col-number {
        font-size: 0.7rem;
        color: var(--text-muted);
        margin-top: 0.25rem;
        font-weight: normal;
    }

    .dashboard-table-responsive td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #E5E7EB;
        border-right: 1px solid #E5E7EB;
        font-size: 0.875rem;
    }
    
    .dashboard-table-responsive td:last-child { border-right: none; }

    .dashboard-table-responsive tr:hover {
        background-color: #F9FAFB;
    }

    .percentage {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.15rem 0.4rem;
        border-radius: 4px;
        background: var(--bps-yellow-light);
        color: #B45309;
        margin-left: 0.5rem;
    }

    .dashboard-footer {
        padding: 1rem 1.5rem;
        background: var(--bps-yellow-light);
        border-top: 1px solid var(--bps-border);
        display: flex;
        justify-content: space-between;
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--bps-orange-hover);
    }

    .loading-overlay {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(255,255,255,0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 50;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #E5E7EB;
        border-top-color: var(--bps-orange);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin { to { transform: rotate(360deg); } }
</style>

<div class="loading-overlay" id="loading">
    <div class="spinner"></div>
</div>

<div class="dashboard-card">
    <div class="dashboard-header">
        <i data-lucide="layout-dashboard" class="text-brand-500 w-6 h-6"></i>
        <h1 class="text-slate-800 font-bold text-xl">Dashboard Level Desa</h1>
    </div>

    <div class="tabs">
        <div class="tab active" data-tab="progres">PROGRES PENDATAAN</div>
        <div class="tab" data-tab="usaha">USAHA/PERUSAHAAN</div>
        <div class="tab" data-tab="skala">SKALA USAHA</div>
        <div class="tab" data-tab="keluarga">USAHA KELUARGA</div>
    </div>

    <div class="dashboard-controls">
        <div style="display:flex; align-items:center; gap: 1rem;">
            <span style="font-size:0.875rem; font-weight:600;">KAB. MINAHASA SELATAN</span>
            <select id="kecamatanSelect">
                <option value="">-- Semua Kecamatan --</option>
                <option value="7105010">010 - MODOINDING</option>
                <option value="7105020">020 - TOMPASO BARU</option>
                <option value="7105021">021 - MAESAAN</option>
                <option value="7105070">070 - RANOYAPO</option>
                <option value="7105080">080 - MOTOLING</option>
                <option value="7105081">081 - KUMELEMBUAI</option>
                <option value="7105082">082 - MOTOLING BARAT</option>
                <option value="7105083">083 - MOTOLING TIMUR</option>
                <option value="7105090">090 - SINONSAYANG</option>
                <option value="7105100">100 - TENGA</option>
                <option value="7105111">111 - AMURANG</option>
                <option value="7105112">112 - AMURANG BARAT</option>
                <option value="7105113">113 - AMURANG TIMUR</option>
                <option value="7105120">120 - TARERAN</option>
                <option value="7105121">121 - SULUUN TARERAN</option>
                <option value="7105130">130 - TUMPAAN</option>
                <option value="7105131">131 - TATAPAAN</option>
            </select>
        </div>
        <div style="font-size: 0.8rem; color: var(--text-muted);">
            Sumber: FASIH Pendataan SE 2026
        </div>
    </div>

    <div class="dashboard-table-responsive">
        <table id="dataTable">
            <thead id="tableHeader">
                <!-- Header will be injected here based on tab -->
            </thead>
            <tbody id="tableBody">
                <!-- Data will be injected here -->
            </tbody>
        </table>
    </div>

    <div class="dashboard-footer" id="tableFooter">
        <!-- Footer summary injected here -->
    </div>
</div>

<script>
    const fileNames = [
        '7105010.json', '7105020.json', '7105021.json', '7105070.json', 
        '7105080.json', '7105081.json', '7105082.json', '7105083.json', 
        '7105090.json', '7105100.json', '7105111.json', '7105112.json', 
        '7105113.json', '7105120.json', '7105121.json', '7105130.json', '7105131.json'
    ];

    let rawData = [];
    let currentTab = 'progres';

    async function init() {
        try {
            document.getElementById('loading').style.display = 'flex';
            
            // Adjust the fetch URL to use Laravel's asset helper
            let fetchPromises = fileNames.map(file => fetch('{{ asset("data/desa") }}/' + file + '?t=' + new Date().getTime()).then(res => {
                if (!res.ok) return null;
                return res.json();
            }).catch(() => null));
            
            const results = await Promise.all(fetchPromises);
            
            results.forEach(res => {
                if (res && Array.isArray(res)) {
                    rawData = rawData.concat(res);
                }
            });

            renderTable();

        } catch (err) {
            console.error(err);
            alert("Gagal memuat data dari server.");
        } finally {
            document.getElementById('loading').style.display = 'none';
        }
    }

    function extractData(filterKecamatan) {
        const desaMap = new Map();

        rawData.forEach(item => {
            if (filterKecamatan && !item.id_wilayah.startsWith(filterKecamatan)) return;

            if (!desaMap.has(item.id_wilayah)) {
                desaMap.set(item.id_wilayah, {
                    id: item.id_wilayah,
                    desa: item.nama_desa,
                    kecamatan: item.nama_kecamatan,
                    indikator: {}
                });
            }
            
            desaMap.get(item.id_wilayah).indikator[item.nama_indikator] = item.total_value || 0;
        });

        let arr = Array.from(desaMap.values());
        arr.sort((a, b) => a.id.localeCompare(b.id));
        return arr;
    }

    function renderTable() {
        const filterKec = document.getElementById('kecamatanSelect').value;
        const data = extractData(filterKec);
        
        const thead = document.getElementById('tableHeader');
        const tbody = document.getElementById('tableBody');
        const tfoot = document.getElementById('tableFooter');
        
        thead.innerHTML = '';
        tbody.innerHTML = '';

        let headers = [];
        let columns = [];
        
        if (currentTab === 'progres') {
            headers = [
                { title: "KODE", sub: "(1)", align: "center", width: "15%" },
                { title: "KECAMATAN / DESA", sub: "(2)", align: "left", width: "35%" },
                { title: "JUMLAH PRELIST USAHA & KELUARGA", sub: "(3)", align: "center", width: "25%" },
                { title: "JUMLAH RESPONDEN DIDATA", sub: "(4)", align: "center", width: "25%" }
            ];
            columns = [
                d => `<div class="text-center">${d.id.substring(0, 10)}</div>`,
                d => `<div class="text-left font-medium" style="color: var(--bps-orange);">${d.desa}</div><div style="font-size:0.75rem; color:var(--text-muted);">${d.kecamatan}</div>`,
                d => `<div class="text-center font-medium">${(d.indikator['Jumlah Prelist Awal'] || 0).toLocaleString('id-ID')}</div>`,
                d => {
                    let target = d.indikator['Jumlah Prelist Awal'] || 0;
                    let realisasi = d.indikator['Progres Pendataan'] || 0;
                    let pct = target > 0 ? ((realisasi / target) * 100).toFixed(2) : "0.00";
                    return `<div class="text-center font-medium">${realisasi.toLocaleString('id-ID')} <span class="percentage">${pct}%</span></div>`;
                }
            ];
        } else if (currentTab === 'usaha') {
            headers = [
                { title: "KODE", sub: "(1)", align: "center" },
                { title: "KECAMATAN / DESA", sub: "(2)", align: "left" },
                { title: "USAHA DITEMUKAN", sub: "(3)", align: "center" },
                { title: "USAHA BARU", sub: "(4)", align: "center" },
                { title: "USAHA TIDAK DITEMUKAN", sub: "(5)", align: "center" },
                { title: "USAHA DITUTUP", sub: "(6)", align: "center" }
            ];
            columns = [
                d => `<div class="text-center">${d.id.substring(0, 10)}</div>`,
                d => `<div class="text-left font-medium" style="color: var(--bps-orange);">${d.desa}</div>`,
                d => `<div class="text-center">${(d.indikator['Jumlah Usaha Ditemukan'] || 0).toLocaleString('id-ID')}</div>`,
                d => `<div class="text-center">${(d.indikator['Jumlah Usaha Baru'] || 0).toLocaleString('id-ID')}</div>`,
                d => `<div class="text-center">${(d.indikator['Jumlah Usaha Tidak Ditemukan'] || 0).toLocaleString('id-ID')}</div>`,
                d => `<div class="text-center">${(d.indikator['Jumlah Usaha Ditutup'] || 0).toLocaleString('id-ID')}</div>`
            ];
        } else if (currentTab === 'skala') {
            headers = [
                { title: "KODE", sub: "(1)", align: "center" },
                { title: "KECAMATAN / DESA", sub: "(2)", align: "left" },
                { title: "PRELIST UB", sub: "(3)", align: "center" },
                { title: "PROGRES UB", sub: "(4)", align: "center" },
                { title: "PRELIST UM", sub: "(5)", align: "center" },
                { title: "PROGRES UM", sub: "(6)", align: "center" },
                { title: "PRELIST UMK", sub: "(7)", align: "center" },
                { title: "PROGRES UMK", sub: "(8)", align: "center" }
            ];
            columns = [
                d => `<div class="text-center">${d.id.substring(0, 10)}</div>`,
                d => `<div class="text-left font-medium" style="color: var(--bps-orange);">${d.desa}</div>`,
                d => `<div class="text-center">${(d.indikator['Jumlah UB Prelist Awal'] || 0).toLocaleString('id-ID')}</div>`,
                d => `<div class="text-center font-medium">${(d.indikator['Progres Pendataan UB dari CAWI & CAPI'] || 0).toLocaleString('id-ID')}</div>`,
                d => `<div class="text-center">${(d.indikator['Jumlah UM Prelist Awal'] || 0).toLocaleString('id-ID')}</div>`,
                d => `<div class="text-center font-medium">${(d.indikator['Progres Pendataan UM dari CAWI & CAPI'] || 0).toLocaleString('id-ID')}</div>`,
                d => `<div class="text-center">${(d.indikator['Jumlah UMK Prelist Awal'] || 0).toLocaleString('id-ID')}</div>`,
                d => `<div class="text-center font-medium">${(d.indikator['Progres Pendataan UMK dari CAPI'] || 0).toLocaleString('id-ID')}</div>`
            ];
        } else if (currentTab === 'keluarga') {
            headers = [
                { title: "KODE", sub: "(1)", align: "center" },
                { title: "KECAMATAN / DESA", sub: "(2)", align: "left" },
                { title: "KELUARGA PRELIST AWAL", sub: "(3)", align: "center" },
                { title: "KELUARGA MEMILIKI USAHA", sub: "(4)", align: "center" },
                { title: "USAHA DALAM KELUARGA", sub: "(5)", align: "center" }
            ];
            columns = [
                d => `<div class="text-center">${d.id.substring(0, 10)}</div>`,
                d => `<div class="text-left font-medium" style="color: var(--bps-orange);">${d.desa}</div>`,
                d => `<div class="text-center">${(d.indikator['Jumlah Keluarga Prelist Awal'] || 0).toLocaleString('id-ID')}</div>`,
                d => `<div class="text-center">${(d.indikator['Jumlah Keluarga yang Memiliki Usaha'] || 0).toLocaleString('id-ID')}</div>`,
                d => `<div class="text-center font-medium">${(d.indikator['Progres Pendataan Usaha dalam Keluarga'] || 0).toLocaleString('id-ID')}</div>`
            ];
        }

        let trHead = '<tr>';
        headers.forEach(h => {
            let width = h.width ? `width="${h.width}"` : '';
            trHead += `<th ${width} style="text-align: ${h.align}">
                <div>${h.title}</div>
                <div class="col-number">${h.sub}</div>
            </th>`;
        });
        trHead += '</tr>';
        thead.innerHTML = trHead;

        if (data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="${headers.length}" class="text-center" style="padding: 2rem; color: var(--text-muted);">Tidak ada data</td></tr>`;
            tfoot.innerHTML = '';
            return;
        }

        data.forEach(d => {
            let tr = document.createElement('tr');
            let html = '';
            columns.forEach((colFn, i) => {
                html += `<td>${colFn(d)}</td>`;
            });
            tr.innerHTML = html;
            tbody.appendChild(tr);
        });

        let footerHtml = `<div style="flex: 1;">Total Keseluruhan (${data.length} Desa)</div>`;
        
        if (currentTab === 'progres') {
            let sumTarget = 0; let sumReal = 0;
            data.forEach(d => {
                sumTarget += (d.indikator['Jumlah Prelist Awal'] || 0);
                sumReal += (d.indikator['Progres Pendataan'] || 0);
            });
            let pct = sumTarget > 0 ? ((sumReal / sumTarget) * 100).toFixed(2) : "0.00";
            
            footerHtml = `
                <div style="width: 50%;">Total Keseluruhan (${data.length} Baris)</div>
                <div style="width: 25%; text-align: center;">${sumTarget.toLocaleString('id-ID')}</div>
                <div style="width: 25%; text-align: center;">${sumReal.toLocaleString('id-ID')} <span class="percentage" style="background:#FFF;">${pct}%</span></div>
            `;
        } else {
            footerHtml = `<div>Total Keseluruhan: ${data.length} Baris</div>`;
        }
        
        tfoot.innerHTML = footerHtml;
        
        // Re-initialize icons since new DOM elements were added
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', (e) => {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            e.target.classList.add('active');
            currentTab = e.target.dataset.tab;
            renderTable();
        });
    });

    document.getElementById('kecamatanSelect').addEventListener('change', () => {
        renderTable();
    });

    window.addEventListener('DOMContentLoaded', init);
</script>
@endsection
