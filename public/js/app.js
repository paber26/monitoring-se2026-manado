let regionChartInstance = null;
let statusChartInstance = null;

document.addEventListener("DOMContentLoaded", async () => {
    try {
        const [dataRes, targetRes, metaRes] = await Promise.all([
            fetch('/api/data'),
            fetch('/api/target').catch(() => ({ json: () => ({ region: {}, user: {}, sls: {} }) })),
            fetch('/api/metadata').catch(() => ({ json: () => ({ file_timestamp: 'Tidak diketahui' }) }))
        ]);
        const data = await dataRes.json();
        let targets = { region: {}, user: {}, sls: {} };
        try { targets = await targetRes.json(); } catch(e){}
        let metadata = { file_timestamp: 'Tidak diketahui' };
        try { metadata = await metaRes.json(); } catch(e){}
        
        const elLastUpdate = document.getElementById('last-update');
        if (elLastUpdate && metadata.file_timestamp) {
            elLastUpdate.innerHTML = `Kondisi terakhir diupdate: <span class="font-medium text-slate-700">${metadata.file_timestamp}</span>`;
        }

        processData(data, targets);
    } catch (error) {
        console.error("Error loading data:", error);
        document.getElementById('mainViews').innerHTML = `
            <div class="max-w-3xl mx-auto mt-10">
                <div class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
                    <i data-lucide="alert-circle" class="w-10 h-10 text-red-500 mx-auto mb-3"></i>
                    <h3 class="text-lg font-bold text-red-700">Error memuat data!</h3>
                    <p class="text-red-600 mt-1">Pastikan file data.json tersedia dan formatnya valid.</p>
                </div>
            </div>
        `;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
});

function processData(data, targets) {
    // Basic calculations
    const totalDocs = data.length;
    let totalClean = 0;
    let totalError = 0;
    let totalRemark = 0;
    
    // Groupings
    const pivotData = {};
    const statusCounts = {};
    const statusKeys = new Set();
    const slsData = {};
    const users = {};

    
    data.forEach(item => {
        // Clean/Error calculations
        totalClean += item.sum_clean ? Number(item.sum_clean) : 0;
        totalError += item.sum_error ? Number(item.sum_error) : 0;
        totalRemark += item.sum_remark ? Number(item.sum_remark) : 0;
        
        // Regions and Status for Pivot
        const region = item.level_3_name || 'Tidak Diketahui';
        const status = item.assignment_status_alias || 'Unknown';
        
        if (!pivotData[region]) {
            pivotData[region] = { total: 0 };
        }
        pivotData[region].total += 1;
        
        if (!pivotData[region][status]) {
            pivotData[region][status] = 0;
        }
        pivotData[region][status] += 1;
        
        if (!statusCounts[status]) {
            statusCounts[status] = 0;
        }
        statusCounts[status] += 1;
        
        statusKeys.add(status);
        
        // SLS Progress
        const slsCode = item.level_6_full_code || 'Tidak Diketahui';
        if (slsCode !== 'Tidak Diketahui') {
            if (!slsData[slsCode]) {
                slsData[slsCode] = {
                    kecamatan: item.level_3_name || '',
                    desa: item.level_4_name || '',
                    nama_sls: item.level_5_name || '',
                    count: 0
                };
            }
            slsData[slsCode].count += 1;
        }
    });

    // Update DOM Summaries
    const elDocs = document.getElementById('total-docs');
    if (elDocs) elDocs.innerText = totalDocs.toLocaleString('id-ID');
    
    const elTargetSls = document.getElementById('total-target-sls');
    if (elTargetSls && targets && targets.sls) {
        elTargetSls.innerText = Object.keys(targets.sls).length.toLocaleString('id-ID');
    }

    const elRegions = document.getElementById('total-regions');
    if (elRegions) elRegions.innerText = Object.keys(slsData).length.toLocaleString('id-ID');

    // 1. Pivot Table (Region vs Status)
    const sortedStatuses = Array.from(statusKeys).sort();
    const pivotHead = document.getElementById('pivotHeadRow');
    
    // Create Header
    if (pivotHead) {
        pivotHead.innerHTML = `<th class="px-6 py-4">No.</th>
                               <th class="px-6 py-4">Kecamatan</th>
                               <th class="px-6 py-4 text-right">Prelist</th>
                               <th class="px-6 py-4 text-right">Dikerjakan</th>` + 
            sortedStatuses.map(s => `<th class="px-6 py-4 text-center">${s}</th>`).join('');
    }

    const pivotBody = document.querySelector('#pivotTable tbody');
    if (pivotBody) {
        pivotBody.innerHTML = '';
        const sortedPivotRegions = Object.entries(pivotData).sort((a, b) => b[1].total - a[1].total);
        sortedPivotRegions.forEach(([regionName, counts], index) => {
            const targetVal = (targets && targets.region && targets.region[regionName]) ? targets.region[regionName] : 0;
            const targetDisplay = targetVal > 0 ? targetVal.toLocaleString('id-ID') : '-';
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50 transition-colors group';
            let html = `<td class="px-6 py-3 font-medium text-slate-500">${index + 1}</td>
                        <td class="px-6 py-3 font-semibold text-slate-800">${regionName}</td>
                        <td class="px-6 py-3 text-right font-semibold text-slate-700">${targetDisplay}</td>
                        <td class="px-6 py-3 text-right"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-brand-100 text-brand-800">${counts.total.toLocaleString('id-ID')}</span></td>`;
            
            sortedStatuses.forEach(s => {
                const val = counts[s] || 0;
                const displayVal = val > 0 ? `<span class="text-slate-700 font-semibold">${val.toLocaleString('id-ID')}</span>` : `<span class="text-slate-300">-</span>`;
                html += `<td class="px-6 py-3 text-center">${displayVal}</td>`;
            });
            
            tr.innerHTML = html;
            pivotBody.appendChild(tr);
        });
    }

    // 4. SLS Table
    const slsTbody = document.querySelector('#slsTable tbody');
    const filterKecamatanSls = document.getElementById('filterKecamatanSls');
    const filterFlagSls = document.getElementById('filterFlagSls');
    
    function renderSlsTable() {
        if (!slsTbody) return;
        slsTbody.innerHTML = '';
        
        const selectedKec = filterKecamatanSls ? filterKecamatanSls.value : '';
        const selectedFlag = filterFlagSls ? filterFlagSls.value : '';
        
        let slsNo = 1;
        Object.keys(slsData).sort().forEach(slsCode => {
            const d = slsData[slsCode];
            const targetObj = targets && targets.sls && targets.sls[slsCode] ? targets.sls[slsCode] : {};
            const targetPrelist = targetObj.total_assignment || 0;
            const flag = targetObj.flag_sls_open_pbi || 0;
            const kk = targetObj.kk_open_pbi || 0;
            
            if (selectedKec && d.kecamatan !== selectedKec) return;
            if (selectedFlag === '1' && flag === 0) return;
            if (selectedFlag === '0' && flag !== 0) return;
            
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50 transition-colors';
            tr.innerHTML = `
                <td class="px-6 py-3 text-slate-500">${slsNo++}</td>
                <td class="px-6 py-3 font-semibold text-slate-800">${d.kecamatan}</td>
                <td class="px-6 py-3 text-slate-600">${d.desa}</td>
                <td class="px-6 py-3 text-slate-600 max-w-xs truncate" title="${d.nama_sls}">${d.nama_sls}</td>
                <td class="px-6 py-3 text-slate-500 font-mono text-xs">${slsCode}</td>
                <td class="px-6 py-3 text-right font-semibold text-slate-700">${targetPrelist.toLocaleString('id-ID')}</td>
                <td class="px-6 py-3 text-right"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">${d.count.toLocaleString('id-ID')}</span></td>
                <td class="px-6 py-3 text-center">
                    ${flag > 0 ? `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">${flag}</span>` : '<span class="text-slate-300">-</span>'}
                </td>
                <td class="px-6 py-3 text-center">
                    ${kk > 0 ? `<span class="text-rose-600 font-bold">${kk}</span>` : '<span class="text-slate-300">-</span>'}
                </td>
            `;
            slsTbody.appendChild(tr);
        });
    }
    
    if (filterKecamatanSls) {
        const kecSet = new Set();
        Object.values(slsData).forEach(d => {
            if (d.kecamatan) kecSet.add(d.kecamatan);
        });
        Array.from(kecSet).sort().forEach(kec => {
            const opt = document.createElement('option');
            opt.value = kec;
            opt.textContent = kec;
            filterKecamatanSls.appendChild(opt);
        });
        filterKecamatanSls.addEventListener('change', renderSlsTable);
    }
    
    if (filterFlagSls) {
        filterFlagSls.addEventListener('change', renderSlsTable);
    }
    renderSlsTable();
    
    // Render Charts
    renderCharts(pivotData, statusCounts);

    const validUserData = data.filter(d => d.source_from !== 'U-CAWI' && d.assignment_status_alias !== 'SUBMITTED RESPONDENT');
    const uniqueRoles = Array.from(new Set(validUserData.map(u => u.current_user_survey_role_name || 'Tidak Diketahui'))).sort();
    const uniqueKecamatan = Array.from(new Set(validUserData.map(u => u.level_3_name || 'Tidak Diketahui'))).sort();
    
    // -- LEADERBOARD LOGIC --
    const leaderboardData = {}; 
    validUserData.forEach(item => {
        if (item.assignment_status_alias && item.assignment_status_alias.toUpperCase() === 'DRAFT') {
            return; // Exclude DRAFT from leaderboard counts
        }
        uniqueRoles.forEach(roleName => {
            let username = '';
            if (roleName === 'Pencacah') {
                username = item.assigned_ppl_name;
            } else if (roleName === 'Pengawas') {
                username = item.assigned_pml_name;
            } else {
                if ((item.current_user_survey_role_name || 'Tidak Diketahui') === roleName) {
                    username = item.real_name || item.current_user_username || item.email || 'Sistem / Tidak Diketahui';
                }
            }
            if (username && username.trim() !== '' && username.trim() !== 'nan') {
                const kec = item.level_3_name || 'Tidak Diketahui';
                const userKey = username + '|' + roleName;
                
                if (!leaderboardData[userKey]) {
                    leaderboardData[userKey] = {
                        username: username,
                        role: roleName,
                        total: 0,
                        kecamatans: {}
                    };
                }
                leaderboardData[userKey].total += 1;
                
                if (!leaderboardData[userKey].kecamatans[kec]) {
                    leaderboardData[userKey].kecamatans[kec] = 0;
                }
                leaderboardData[userKey].kecamatans[kec] += 1;
            }
        });
    });

    const sortedLeaderboard = Object.values(leaderboardData).sort((a, b) => b.total - a.total);
    
    // Helper to generate initials for avatar
    function getInitials(name) {
        const parts = name.trim().split(' ');
        if (parts.length >= 2) {
            return (parts[0][0] + parts[1][0]).toUpperCase();
        } else if (parts.length === 1 && parts[0]) {
            return parts[0].substring(0, 2).toUpperCase();
        }
        return 'U';
    }

    function renderLeaderboardKabupatenRow(data, index, colorClass, textClass, ringClass) {
        const domKec = Object.entries(data.kecamatans).sort((a,b) => b[1] - a[1])[0][0];
        const targetVal = (targets && targets.user && targets.user[data.username]) ? targets.user[data.username] : 0;
        const targetDisplay = targetVal > 0 ? targetVal.toLocaleString('id-ID') : '-';
        const initials = getInitials(data.username);
        
        let rankBadge = `<div class="w-8 h-8 rounded-full ${colorClass} ${textClass} flex items-center justify-center font-bold text-sm ring-2 ${ringClass}">${index + 1}</div>`;
        if (index > 2) {
            rankBadge = `<div class="font-medium text-slate-500 text-center w-8">${index + 1}</div>`;
        }
        
        let pctHtml = '';
        if (targetVal > 0) {
            const pct = ((data.total / targetVal) * 100).toFixed(1).replace('.0', '').replace('.', ',');
            pctHtml = `<span class="text-xs text-slate-500 font-medium ml-1.5 w-10 inline-block text-right">${pct}%</span>`;
        }

        return `
            <tr class="hover:bg-slate-50 transition-colors group">
                <td class="px-6 py-3">${rankBadge}</td>
                <td class="px-6 py-3 font-semibold text-slate-800">${data.username}</td>
                <td class="px-6 py-3 text-slate-600">${domKec}</td>
                <td class="px-6 py-3 text-right font-semibold text-slate-700">${targetDisplay}</td>
                <td class="px-6 py-3 text-right whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">${data.total.toLocaleString('id-ID')}</span>
                    ${pctHtml}
                </td>
            </tr>
        `;
    }

    const lbKabPclTable = document.querySelector('#lbKabPclTable tbody');
    if (lbKabPclTable) {
        lbKabPclTable.innerHTML = '';
        const pclData = sortedLeaderboard.filter(d => d.role === 'Pencacah').slice(0, 10);
        pclData.forEach((data, index) => {
            let colorClass = 'bg-slate-200', textClass = 'text-slate-700', ringClass = 'ring-slate-100';
            if (index === 0) { colorClass = 'bg-yellow-100'; textClass = 'text-yellow-700'; ringClass = 'ring-yellow-50'; }
            if (index === 1) { colorClass = 'bg-slate-200'; textClass = 'text-slate-700'; ringClass = 'ring-slate-100'; }
            if (index === 2) { colorClass = 'bg-orange-100'; textClass = 'text-orange-700'; ringClass = 'ring-orange-50'; }
            lbKabPclTable.innerHTML += renderLeaderboardKabupatenRow(data, index, colorClass, textClass, ringClass);
        });
    }

    const lbKabPmlTable = document.querySelector('#lbKabPmlTable tbody');
    if (lbKabPmlTable) {
        lbKabPmlTable.innerHTML = '';
        const pmlData = sortedLeaderboard.filter(d => d.role === 'Pengawas').slice(0, 10);
        pmlData.forEach((data, index) => {
            let colorClass = 'bg-slate-200', textClass = 'text-slate-700', ringClass = 'ring-slate-100';
            if (index === 0) { colorClass = 'bg-yellow-100'; textClass = 'text-yellow-700'; ringClass = 'ring-yellow-50'; }
            if (index === 1) { colorClass = 'bg-slate-200'; textClass = 'text-slate-700'; ringClass = 'ring-slate-100'; }
            if (index === 2) { colorClass = 'bg-orange-100'; textClass = 'text-orange-700'; ringClass = 'ring-orange-50'; }
            lbKabPmlTable.innerHTML += renderLeaderboardKabupatenRow(data, index, colorClass, textClass, ringClass);
        });
    }


    // -- END LEADERBOARD LOGIC --

    
    const userViewsContainer = document.getElementById('userViewsContainer');
    const dynamicMenusContainer = document.getElementById('dynamicMenusContainer');
    userViewsContainer.innerHTML = '';    
    dynamicMenusContainer.innerHTML = '';

    uniqueRoles.forEach(roleName => {
        // Create view ID
        const viewId = `view-role-${roleName.replace(/\\s+/g, '-').toLowerCase()}`;
        
        // 1. Inject Menu Item
        const menuItem = document.createElement('a');
        menuItem.href = '#';
        menuItem.className = 'menu-item flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-colors group';
        menuItem.setAttribute('data-target', viewId);
        menuItem.innerHTML = `<i data-lucide="users" class="w-5 h-5 text-slate-400 group-hover:text-brand-500 transition-colors nav-icon"></i>
                              <span class="text-sm font-medium nav-text">Kinerja - ${roleName}</span>`;
        dynamicMenusContainer.appendChild(menuItem);

        // 2. Create View Container
        const viewSection = document.createElement('div');
        viewSection.id = viewId;
        viewSection.className = 'view-section';

        const card = document.createElement('div');
        card.className = 'bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden';
        
        // HTML Header with filter
        let html = `<div class="px-6 py-5 border-b border-slate-100 bg-white flex justify-between items-center flex-wrap gap-4">
                        <div class="flex items-center gap-2">
                            <div class="p-2 bg-slate-50 text-slate-600 rounded-lg">
                                <i data-lucide="bar-chart-2" class="w-5 h-5"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-800">Kinerja - ${roleName}</h3>
                        </div>
                        <div class="relative">
                            <select class="appearance-none bg-slate-50 border border-slate-200 text-slate-700 py-2 pl-4 pr-10 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 cursor-pointer" id="filter-${viewId}">
                                <option value="">Semua Kecamatan</option>
                                ${uniqueKecamatan.map(k => `<option value="${k}">${k}</option>`).join('')}
                            </select>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                        </div>
                    </div>`;
        
        html += `<div class="overflow-x-auto">
                    <table class="w-full text-sm text-left data-table" id="table-${viewId}">
                        <thead class="text-xs text-slate-500 bg-slate-50 uppercase font-semibold border-b border-slate-100">
                            <tr class="table-head">
                                <!-- Headers updated dynamically -->
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700"></tbody>
                    </table>
                 </div>`;
                 
        card.innerHTML = html;
        viewSection.appendChild(card);
        userViewsContainer.appendChild(viewSection);
        
        // Initial Render
        updateRoleTable(roleName, '', viewId, validUserData, targets);
        
        // Event Listener for Filter
        document.getElementById(`filter-${viewId}`).addEventListener('change', (e) => {
            updateRoleTable(roleName, e.target.value, viewId, validUserData, targets);
        });
    });

    // -- TARGET HARIAN LOGIC --
    const targetStartDate = document.getElementById('targetStartDate');
    const targetCurrentDate = document.getElementById('targetCurrentDate');
    const targetWorkingDays = document.getElementById('targetWorkingDays');
    const filterTargetKecamatan = document.getElementById('filterTargetKecamatan');
    const filterTargetRole = document.getElementById('filterTargetRole');
    const targetHarianTable = document.querySelector('#targetHarianTable tbody');
    
    if (targetStartDate && targetCurrentDate && targetWorkingDays && targetHarianTable) {
        uniqueKecamatan.forEach(kec => {
            filterTargetKecamatan.innerHTML += `<option value="${kec}">${kec}</option>`;
        });

        if (!targetCurrentDate.value) {
            // Adjust to current local date
            const today = new Date();
            const tzOffset = today.getTimezoneOffset() * 60000;
            const localISOTime = (new Date(today - tzOffset)).toISOString().slice(0, 10);
            targetCurrentDate.value = localISOTime;
        }

        function calculateWorkingDays(startDateStr, endDateStr) {
            if (!startDateStr || !endDateStr) return 0;
            const start = new Date(startDateStr);
            const end = new Date(endDateStr);
            if (start > end) return 0;
            
            let count = 0;
            let cur = new Date(start);
            while (cur <= end) {
                if (cur.getDay() !== 0) { // 0 is Sunday
                    count++;
                }
                cur.setDate(cur.getDate() + 1);
            }
            return count;
        }

        function renderTargetHarian() {
            const workingDays = calculateWorkingDays(targetStartDate.value, targetCurrentDate.value);
            targetWorkingDays.value = workingDays;

            const selectedKec = filterTargetKecamatan.value;
            const selectedRole = filterTargetRole.value;

            targetHarianTable.innerHTML = '';
            
            let targetData = sortedLeaderboard.filter(d => d.role === selectedRole);
            if (selectedKec !== '') {
                targetData = targetData.filter(d => d.kecamatans[selectedKec] > 0);
            }

            const tableRows = targetData.map(data => {
                const domKec = Object.entries(data.kecamatans).sort((a,b) => b[1] - a[1])[0][0];
                const targetVal = (targets && targets.user && targets.user[data.username]) ? targets.user[data.username] : 0;
                
                const targetPerHari = targetVal > 0 ? targetVal / 60 : 0;
                let targetHarianVal = targetVal > 0 ? Math.ceil(targetPerHari) : 0;
                
                let targetSdHariIni = targetVal > 0 ? Math.ceil(targetPerHari * workingDays) : 0;
                if (targetSdHariIni > targetVal) targetSdHariIni = targetVal;

                const realisasiTotal = data.total;
                const gap = realisasiTotal - targetSdHariIni;
                
                return { username: data.username, domKec, targetVal, targetHarianVal, targetSdHariIni, realisasi: realisasiTotal, gap };
            });

            if (tableRows.length === 0) {
                targetHarianTable.innerHTML = `<tr><td colspan="8" style="text-align:center; padding: 2rem; color: #94a3b8;">Tidak ada data.</td></tr>`;
            } else {
                tableRows.forEach((row, idx) => {
                    let statusHtml = '';
                    if (row.targetVal === 0) {
                        statusHtml = `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-500">No Target</span>`;
                    } else if (row.gap >= 0) {
                        statusHtml = `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-700">+${row.gap}</span>`;
                    } else {
                        statusHtml = `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-rose-100 text-rose-700">${row.gap}</span>`;
                    }

                    targetHarianTable.innerHTML += `
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-slate-500">${idx + 1}</td>
                            <td class="px-6 py-4 font-semibold text-slate-800">${row.username}</td>
                            <td class="px-6 py-4 text-slate-600">${row.domKec}</td>
                            <td class="px-6 py-4 text-right font-semibold text-slate-700">${row.targetVal > 0 ? row.targetVal.toLocaleString('id-ID') : '-'}</td>
                            <td class="px-6 py-4 text-right text-slate-500">${row.targetHarianVal}</td>
                            <td class="px-6 py-4 text-right font-bold text-slate-800 bg-brand-50/50">${row.targetSdHariIni.toLocaleString('id-ID')}</td>
                            <td class="px-6 py-4 text-right font-bold text-emerald-700 bg-emerald-50/50">${row.realisasi.toLocaleString('id-ID')}</td>
                            <td class="px-6 py-4 text-center">${statusHtml}</td>
                        </tr>
                    `;
                });
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        targetStartDate.addEventListener('change', renderTargetHarian);
        targetCurrentDate.addEventListener('change', renderTargetHarian);
        filterTargetKecamatan.addEventListener('change', renderTargetHarian);
        filterTargetRole.addEventListener('change', renderTargetHarian);
        
        renderTargetHarian();
    }
    // -- END TARGET HARIAN LOGIC --

    // 5. Setup Navigation Logic
    setupNavigation();
    
    // Initialize icons globally after rendering everything
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function updateRoleTable(roleName, regionFilter, viewId, validUserData, targets) {
    // Filter data for region
    let filteredData = validUserData;
    if (regionFilter) {
        filteredData = filteredData.filter(d => (d.level_3_name || 'Tidak Diketahui') === regionFilter);
    }
    
    // Aggregate data
    const usersAgg = {};
    const roleStatusSet = new Set();
    
    filteredData.forEach(item => {
        let username = '';
        if (roleName === 'Pencacah') {
            username = item.assigned_ppl_name;
        } else if (roleName === 'Pengawas') {
            username = item.assigned_pml_name;
        } else {
            if ((item.current_user_survey_role_name || 'Tidak Diketahui') === roleName) {
                username = item.real_name || item.current_user_username || item.email || 'Sistem / Tidak Diketahui';
            }
        }
        
        if (!username) return; // Lewati jika tidak termapping
        
        const status = item.assignment_status_alias || 'Unknown';
        
        if (!usersAgg[username]) {
            usersAgg[username] = { total: 0 };
        }
        usersAgg[username].total += 1;
        
        if (!usersAgg[username][status]) {
            usersAgg[username][status] = 0;
        }
        usersAgg[username][status] += 1;
        
        roleStatusSet.add(status);
    });
    
    const activeRoleStatuses = Array.from(roleStatusSet).sort();
    const roleUsers = Object.entries(usersAgg).sort((a, b) => b[1].total - a[1].total);
    
    // Render Header
    const tableHead = document.querySelector(`#table-${viewId} .table-head`);
    let headHtml = `<th class="px-6 py-4">No.</th>
                    <th class="px-6 py-4">Nama Petugas</th>
                    <th class="px-6 py-4 text-right">Prelist</th>
                    <th class="px-6 py-4 text-right">Dikerjakan</th>`;
    activeRoleStatuses.forEach(s => headHtml += `<th class="px-6 py-4 text-center">${s}</th>`);
    tableHead.innerHTML = headHtml;
    
    // Render Body
    const tbody = document.querySelector(`#table-${viewId} tbody`);
    let bodyHtml = '';
    
    if (roleUsers.length === 0) {
        bodyHtml = `<tr><td colspan="${4 + activeRoleStatuses.length}" style="text-align:center; padding: 2rem; color: #94a3b8;">Tidak ada data untuk filter ini.</td></tr>`;
    } else {
        roleUsers.forEach(([username, counts], index) => {
            const targetVal = (targets && targets.user && targets.user[username]) ? targets.user[username] : 0;
            const targetDisplay = targetVal > 0 ? targetVal.toLocaleString('id-ID') : '-';
            bodyHtml += `<tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3 font-medium text-slate-500">${index + 1}</td>
                            <td class="px-6 py-3 font-semibold text-slate-800">${username}</td>
                            <td class="px-6 py-3 text-right font-semibold text-slate-700">${targetDisplay}</td>
                            <td class="px-6 py-3 text-right"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">${counts.total.toLocaleString('id-ID')}</span></td>`;
            activeRoleStatuses.forEach(s => {
                const val = counts[s] || 0;
                const displayVal = val > 0 ? `<span class="text-slate-700 font-semibold">${val.toLocaleString('id-ID')}</span>` : `<span class="text-slate-300">-</span>`;
                bodyHtml += `<td class="px-6 py-3 text-center">${displayVal}</td>`;
            });
            bodyHtml += `</tr>`;
        });
    }
    tbody.innerHTML = bodyHtml;
}

function setupNavigation() {
    window.addEventListener('hashchange', handleHashChange);
    
    // Initial routing
    handleHashChange();

    const menuItems = document.querySelectorAll('.menu-item');
    menuItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = item.getAttribute('data-target');
            window.location.hash = targetId;
        });
    });
}

function handleHashChange() {
    let hash = window.location.hash.substring(1);
    if (!hash) hash = 'view-dashboard';
    
    const menuItems = document.querySelectorAll('.menu-item');
    const viewSections = document.querySelectorAll('.view-section');
    
    const targetItem = document.querySelector(`.menu-item[data-target="${hash}"]`);
    if (!targetItem) return;

    // Reset state
    menuItems.forEach(m => {
        m.classList.remove('active');
        const icon = m.querySelector('.nav-icon');
        if (icon) {
            icon.classList.remove('text-brand-500', 'text-yellow-500');
            if (m.getAttribute('data-target') === 'view-leaderboard') {
                icon.classList.add('text-yellow-500'); // Leaderboard icon default
            } else {
                icon.classList.add('text-slate-400');
            }
        }
        const text = m.querySelector('.nav-text');
        if (text) text.classList.remove('text-white');
    });

    // Activate clicked item
    targetItem.classList.add('active');
    const clickedIcon = targetItem.querySelector('.nav-icon');
    if (clickedIcon) {
        clickedIcon.classList.remove('text-slate-400');
        if (targetItem.getAttribute('data-target') === 'view-leaderboard') {
            clickedIcon.classList.add('text-yellow-500');
        } else {
            clickedIcon.classList.add('text-brand-500');
        }
    }
    const clickedText = targetItem.querySelector('.nav-text');
    if (clickedText) clickedText.classList.add('text-white');

    // Toggle views
    viewSections.forEach(v => {
        v.classList.remove('active');
    });
    
    const targetView = document.getElementById(hash);
    if (targetView) {
        targetView.classList.add('active');
    }
}

function renderCharts(pivotData, statusCounts) {
    const regionCtx = document.getElementById('regionChart');
    const statusCtx = document.getElementById('statusChart');
    
    if (!regionCtx || !statusCtx) return;

    const regionDataArray = Object.keys(pivotData).map(r => ({
        region: r,
        total: pivotData[r].total
    }));
    regionDataArray.sort((a, b) => b.total - a.total);

    const regionLabels = regionDataArray.map(item => item.region);
    const regionTotals = regionDataArray.map(item => item.total);

    if (regionChartInstance) {
        regionChartInstance.destroy();
    }
    
    regionChartInstance = new Chart(regionCtx, {
        type: 'bar',
        data: {
            labels: regionLabels,
            datasets: [{
                label: 'Total Assignment',
                data: regionTotals,
                backgroundColor: '#0ea5e9', // brand-500
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                x: { grid: { display: false } }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });

    const statusLabels = Object.keys(statusCounts).sort();
    const statusTotals = statusLabels.map(s => statusCounts[s]);
    
    const bgColors = ['#0ea5e9', '#10b981', '#f43f5e', '#f59e0b', '#8b5cf6', '#64748b', '#3b82f6', '#14b8a6'];

    if (statusChartInstance) {
        statusChartInstance.destroy();
    }
    
    statusChartInstance = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusTotals,
                backgroundColor: bgColors.slice(0, statusLabels.length),
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right' }
            }
        }
    });
}
