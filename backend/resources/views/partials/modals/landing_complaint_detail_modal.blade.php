<!-- Modal Detail Pengaduan Publik: Nomor pelapor disamarkan (3 digit terakhir) -->
<div id="landingComplaintDetailModal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center p-3 sm:p-5 bg-slate-900/75 transition-opacity duration-150 overscroll-contain">
    <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[92vh] animate-in fade-in zoom-in-95" style="transform: translateZ(0); will-change: transform;">
        
        <!-- Modal Top Header (Selaras 1 Baris: Kerusakan -> Kategori -> Status) -->
        <div class="px-4 sm:px-5 py-3 sm:py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80 gap-2">
            <div class="flex items-center gap-1.5 sm:gap-2 flex-nowrap min-w-0 overflow-x-auto no-scrollbar py-0.5">
                <!-- 1. Tingkat Kerusakan -->
                <span id="landingModalTypeBadge" class="shrink-0 px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200 inline-flex items-center gap-1.5 whitespace-nowrap">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 shrink-0"></span>
                    <span id="landingModalTypeText">Kerusakan Ringan</span>
                </span>
                <!-- 2. Kategori Infrastruktur -->
                <span id="landingModalCategoryBadge" class="shrink-0 px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 font-semibold text-xs border border-slate-200 inline-flex items-center whitespace-nowrap">
                    Infrastruktur
                </span>
                <!-- 3. Status Pengerjaan -->
                <span id="landingModalStatusBadge" class="shrink-0 px-2.5 py-1 rounded-full text-xs font-bold whitespace-nowrap border">
                    STATUS
                </span>
            </div>
            <button type="button" onclick="closeLandingDetailModal()" class="w-8 h-8 rounded-full bg-slate-200/70 hover:bg-slate-300 text-slate-700 flex items-center justify-center transition cursor-pointer shrink-0 ml-1" title="Tutup Detail">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Modal Scrollable Body -->
        <div class="p-4 sm:p-5 overflow-y-auto space-y-4 flex-1 overscroll-contain" style="-webkit-overflow-scrolling: touch;">
            
            <!-- 1. Foto Bukti Kerusakan Awal (Warga) -->
            <div id="landingModalPhotoContainer" class="space-y-2">
                <div class="rounded-2xl overflow-hidden border border-slate-200 bg-slate-100 relative max-h-64 sm:max-h-72 flex items-center justify-center shadow-xs">
                    <img id="landingModalPhoto" src="" alt="Foto Kerusakan" class="w-full h-full object-cover max-h-64 sm:max-h-72">
                    <div class="absolute bottom-2.5 left-2.5 bg-black/75 backdrop-blur-xs text-white text-[10.5px] font-medium px-2.5 py-1 rounded-lg flex items-center gap-1.5 shadow-xs">
                        <i data-lucide="camera" class="w-3.5 h-3.5 text-white/90"></i>
                        <span id="landingModalPhotoLabel">Foto Bukti Kerusakan</span>
                    </div>
                </div>
                <!-- Strip Thumbnail Galeri Warga -->
                <div id="landingModalCitizenGallery" class="flex items-center gap-2 overflow-x-auto pb-1 hidden"></div>
            </div>

            <!-- 2. Foto Bukti Hasil Pengerjaan Fisik (Petugas Lapangan) -->
            <div id="landingModalCompletionPhotoContainer" class="space-y-2 hidden">
                <div class="rounded-2xl overflow-hidden border border-emerald-300 bg-emerald-50/50 relative max-h-64 sm:max-h-72 flex items-center justify-center shadow-xs">
                    <img id="landingModalCompletionPhoto" src="" alt="Foto Hasil Pengerjaan" class="w-full h-full object-cover max-h-64 sm:max-h-72">
                    <div class="absolute bottom-2.5 left-2.5 bg-emerald-950/90 backdrop-blur-xs text-white text-[10.5px] font-bold px-2.5 py-1 rounded-lg flex items-center gap-1.5 shadow-md">
                        <i data-lucide="badge-check" class="w-3.5 h-3.5 text-emerald-400"></i>
                        <span>Bukti Penyelesaian Lapangan</span>
                    </div>
                </div>
                <!-- Strip Thumbnail Galeri Petugas -->
                <div id="landingModalOfficerGallery" class="flex items-center gap-2 overflow-x-auto pb-1 hidden"></div>
            </div>

            <!-- 3. Judul & Info Waktu Laporan -->
            <div>
                <span id="landingModalCode" class="text-[11px] font-mono font-medium text-slate-400 block tracking-wide mb-0.5">#ADU-0000</span>
                <h3 id="landingModalTitle" class="font-outfit font-extrabold text-lg sm:text-xl text-slate-900 leading-snug">
                    Judul Pengaduan
                </h3>
                <div class="flex items-center gap-1.5 text-xs text-slate-500 pt-0.5">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span id="landingModalDate">01 Jan 2026, 00:00 WITA</span>
                </div>
            </div>

            <!-- 4. Identitas Pelapor (Disensor Ketat untuk Publik / UU PDP) -->
            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-full bg-blue-100 text-[#1E3A8A] flex items-center justify-center font-bold text-xs shrink-0">
                        <i data-lucide="user" class="w-4 h-4"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[10px] text-slate-400 block font-bold uppercase tracking-wider">PELAPOR ADUAN</span>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span id="landingModalReporter" class="font-outfit font-extrabold text-xs sm:text-sm text-slate-900 leading-tight truncate">
                                Warga Terdaftar
                            </span>
                            <span id="landingModalAnonymousBadge" class="hidden text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-200 text-slate-600">Anonim</span>
                        </div>
                    </div>
                </div>
                <!-- Nomor Telepon Pelapor Disensor: Karakter Bintang + Hanya 3 Digit Terakhir Kelihatan (UU PDP) -->
                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-100 border border-slate-200/80 text-slate-600 shrink-0" title="Nomor telepon pelapor disamarkan demi melindungi privasi (hanya 3 digit terakhir terlihat)">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span id="landingModalReporterPhone" class="font-mono text-xs font-bold text-slate-700 tracking-wider">********---</span>
                </div>
            </div>

            <!-- 5. Kartu Regu Lapangan Pelaksana -->
            <div id="landingModalOfficerCard" class="hidden p-3.5 rounded-2xl bg-blue-50/50 border border-blue-200/80 space-y-2.5">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-xl bg-[#1E3A8A] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs mt-0.5">
                            <i data-lucide="hard-hat" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] text-[#1E3A8A] font-extrabold uppercase tracking-wider block">REGU LAPANGAN PELAKSANA</span>
                            <h4 id="landingModalOfficerName" class="font-outfit font-extrabold text-xs sm:text-sm text-slate-900 truncate leading-tight">
                                Regu Aspal 01 - Lasusua
                            </h4>
                            <span id="landingModalOfficerDept" class="text-[10.5px] font-semibold text-slate-500 block truncate">
                                Dinas Pekerjaan Umum dan Penataan Ruang (PUPR)
                            </span>
                        </div>
                    </div>
                    <div id="landingModalDeadlineBox" class="text-right shrink-0">
                        <span class="text-[9.5px] text-slate-400 font-bold block">Batas Waktu:</span>
                        <span id="landingModalOfficerDeadline" class="text-xs font-bold text-[#1E3A8A] bg-white px-2 py-0.5 rounded-lg border border-blue-200 inline-block mt-0.5 shadow-2xs">
                            -
                        </span>
                    </div>
                </div>

                <!-- Kontak Ketua Regu (Koordinator Lapangan Resmi) -->
                <div class="p-2.5 rounded-xl bg-white border border-slate-200/80 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-6 h-6 rounded-lg bg-blue-50 text-[#1E3A8A] flex items-center justify-center shrink-0">
                            <i data-lucide="crown" class="w-3.5 h-3.5"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] text-slate-400 font-bold block leading-none">Ketua Regu (Koordinator Lapangan):</span>
                            <span id="landingModalTeamLeader" class="font-bold text-slate-900 truncate block mt-0.5 text-xs">Petugas Lapangan</span>
                        </div>
                    </div>
                    <a id="landingModalOfficerPhone" href="#" class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#1E3A8A] font-bold font-mono text-xs flex items-center gap-1.5 transition shrink-0" title="Hubungi Petugas Lapangan">
                        <i data-lucide="phone-call" class="w-3 h-3"></i>
                        <span id="landingModalOfficerPhoneText">081100000004</span>
                    </a>
                </div>

                <!-- Instruksi Teknis Pengerjaan -->
                <div id="landingModalInstructionRow" class="pt-1 text-[11px] text-slate-600 flex items-start gap-1.5 border-t border-blue-200/40">
                    <i data-lucide="clipboard-list" class="w-3.5 h-3.5 text-[#1E3A8A] shrink-0 mt-0.5"></i>
                    <span>Instruksi: <em id="landingModalOfficerInstruction" class="text-slate-800 font-medium">Lakukan penanganan fisik sesuai SOP dinas terkait</em></span>
                </div>
            </div>

            <!-- 6. Lokasi Kerusakan & GIS Spasial -->
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                    <span class="flex items-center gap-1.5">
                        <i data-lucide="map-pin" class="w-4 h-4 text-[#1E3A8A]"></i>
                        <span>Lokasi Kerusakan</span>
                    </span>
                    <span id="landingModalCoords" class="font-mono text-[11px] font-bold text-[#1E3A8A] bg-white px-2 py-0.5 rounded-md border border-slate-200 shadow-2xs">
                        -
                    </span>
                </div>
                <p id="landingModalFullAddress" class="text-xs font-semibold text-slate-800 leading-relaxed">
                    Alamat lokasi kerusakan...
                </p>
            </div>

            <!-- 7. Spesifikasi Ruas Jalan Rusak Panjang (> 500 Meter / Parah) -->
            <div id="landingModalMajorRoadBox" class="hidden rounded-2xl p-4 bg-[#1E3A8A]/5 border border-[#1E3A8A]/20 text-[#1E3A8A] space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="route" class="w-4 h-4 text-[#1E3A8A]"></i>
                        <span class="text-xs font-bold uppercase tracking-wider">Kerusakan Ruas Panjang</span>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#1E3A8A] text-white">Major</span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs pt-1">
                    <div class="p-2.5 rounded-xl bg-white border border-[#1E3A8A]/15">
                        <span class="text-[10px] text-slate-500 block">Kondisi Kerusakan:</span>
                        <span id="landingModalRoadCondition" class="font-bold text-slate-800">-</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-white border border-[#1E3A8A]/15">
                        <span class="text-[10px] text-slate-500 block">Estimasi Panjang:</span>
                        <span id="landingModalRoadLength" class="font-bold text-slate-800">-</span>
                    </div>
                </div>
            </div>

            <!-- 8. Deskripsi Lengkap Kerusakan -->
            <div class="space-y-2">
                <h4 class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                    <i data-lucide="file-text" class="w-4 h-4 text-slate-400"></i>
                    <span>Deskripsi Kondisi Kerusakan</span>
                </h4>
                <div id="landingModalDescription" class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-700 leading-relaxed whitespace-pre-line font-sans">
                    Kondisi kerusakan infrastruktur...
                </div>
            </div>

        </div>

        <!-- Modal Footer Actions -->
        <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/80 flex items-center justify-end gap-2">
            <button type="button" onclick="closeLandingDetailModal()" class="px-5 py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-[#172e6e] text-white font-bold text-xs shadow-md shadow-[#1E3A8A]/20 hover:shadow-lg transition flex items-center gap-1.5 cursor-pointer">
                <span>Tutup Rincian</span>
            </button>
        </div>

    </div>
</div>

<script>
(function() {
    function maskPhonePublic(rawPhone) {
        if (!rawPhone || rawPhone === '-' || rawPhone === 'null') {
            return '********';
        }
        const clean = rawPhone.toString().replace(/[^0-9]/g, '');
        if (clean.length >= 3) {
            const suffix = clean.slice(-3); // Hanya 3 angka di belakang nomor yang kelihatan
            const maskCount = Math.max(clean.length - 3, 6);
            return '*'.repeat(maskCount) + suffix;
        }
        return '********';
    }

    window.openLandingDetailModal = function(dataOrId) {
        let item = null;
        if (typeof dataOrId === 'object' && dataOrId !== null) {
            item = dataOrId;
            renderLandingModal(item);
        } else if (typeof dataOrId === 'number' || typeof dataOrId === 'string') {
            // Cek jika ada map aduan publik di window
            if (window.landingComplaintsMap && window.landingComplaintsMap[dataOrId]) {
                item = window.landingComplaintsMap[dataOrId];
                renderLandingModal(item);
            } else {
                fetch(`/api/v1/complaints/${dataOrId}`)
                    .then(res => res.json())
                    .then(res => {
                        if (res.data) {
                            renderLandingModal(res.data);
                        }
                    })
                    .catch(err => console.error('[LandingModal] Gagal memuat pengaduan:', err));
            }
        }
    };

    function renderLandingModal(item) {
        if (!item) return;

        const modal = document.getElementById('landingComplaintDetailModal');
        if (!modal) return;

        // 1. Kode & Judul
        document.getElementById('landingModalCode').innerText = item.public_code ? ('#' + item.public_code) : '#ADU-0000';
        document.getElementById('landingModalTitle').innerText = item.title || 'Laporan Kerusakan';
        document.getElementById('landingModalCategoryBadge').innerText = item.category_name || item.category?.name || 'Infrastruktur';
        document.getElementById('landingModalDate').innerText = item.created_at_formatted || item.created_at || '-';

        // 2. Tipe Kerusakan
        const isMajor = item.damage_type === 'major';
        const typeBadge = document.getElementById('landingModalTypeBadge');
        const typeText = document.getElementById('landingModalTypeText');
        if (typeText) typeText.innerText = isMajor ? 'Kerusakan Berat' : 'Kerusakan Ringan';

        // 3. Status Badge
        const statusBadge = document.getElementById('landingModalStatusBadge');
        let statusLabel = item.status;
        let statusClass = 'bg-slate-100 text-slate-700 border-slate-200';

        if (item.status === 'REJECTED') {
            statusLabel = 'DITOLAK';
            statusClass = 'bg-red-50 text-red-700 border-red-200';
        } else if (item.status === 'COMPLETED' || item.status === 'CLOSED') {
            statusLabel = 'SELESAI';
            statusClass = 'bg-emerald-50 text-emerald-800 border-emerald-200';
        } else if (item.status === 'WAITING_VERIFICATION') {
            statusLabel = 'VALIDASI';
            statusClass = 'bg-amber-50 text-amber-700 border-amber-300';
        } else if (item.status === 'IN_PROGRESS') {
            statusLabel = 'DIKERJAKAN';
            statusClass = 'bg-amber-50 text-amber-800 border-amber-300';
        } else if (item.status === 'DISPATCHED') {
            statusLabel = 'DITUGASKAN';
            statusClass = 'bg-blue-50 text-[#1E3A8A] border-blue-200';
        } else if (item.status === 'SUBMITTED' || item.status === 'VERIFICATION') {
            statusLabel = 'VERIFIKASI';
            statusClass = 'bg-red-50 text-red-700 border-red-200';
        } else if (item.status === 'REVISION_REQUIRED') {
            statusLabel = 'PERLU REVISI';
            statusClass = 'bg-amber-50 text-amber-800 border-amber-300';
        }

        if (statusBadge) {
            statusBadge.innerText = statusLabel;
            statusBadge.className = `shrink-0 px-2.5 py-1 rounded-full text-xs font-bold whitespace-nowrap border ${statusClass}`;
        }

        // 4. Foto Bukti Kerusakan Warga
        const photoContainer = document.getElementById('landingModalPhotoContainer');
        const photoImg = document.getElementById('landingModalPhoto');
        const citizenGallery = document.getElementById('landingModalCitizenGallery');

        let citizenPhotos = [];
        if (item.photos && Array.isArray(item.photos)) {
            citizenPhotos = item.photos.map(p => (typeof p === 'string' ? p : (p.url || (p.storage_key ? '/storage/' + p.storage_key : '')))).filter(Boolean);
        } else if (item.photo_url) {
            citizenPhotos = [item.photo_url];
        }

        if (citizenPhotos.length > 0) {
            photoImg.src = citizenPhotos[0];
            photoContainer.classList.remove('hidden');

            if (citizenGallery) {
                if (citizenPhotos.length > 1) {
                    citizenGallery.classList.remove('hidden');
                    citizenGallery.innerHTML = citizenPhotos.map(url => `
                        <button type="button" onclick="document.getElementById('landingModalPhoto').src='${url}';" 
                            class="w-12 h-12 rounded-xl overflow-hidden border border-slate-200 bg-slate-100 shrink-0 hover:ring-2 hover:ring-[#1E3A8A] transition cursor-pointer">
                            <img src="${url}" class="w-full h-full object-cover">
                        </button>
                    `).join('');
                } else {
                    citizenGallery.classList.add('hidden');
                    citizenGallery.innerHTML = '';
                }
            }
        } else {
            photoContainer.classList.add('hidden');
        }

        // 5. Foto Pengerjaan Petugas Lapangan
        const compContainer = document.getElementById('landingModalCompletionPhotoContainer');
        const compImg = document.getElementById('landingModalCompletionPhoto');
        const officerGallery = document.getElementById('landingModalOfficerGallery');

        const officerPhotos = (item.officer_photos && item.officer_photos.length > 0)
            ? item.officer_photos
            : (item.completion_photo_url ? [item.completion_photo_url] : []);

        if (officerPhotos.length > 0) {
            compImg.src = officerPhotos[0];
            compContainer.classList.remove('hidden');

            if (officerGallery) {
                if (officerPhotos.length > 1) {
                    officerGallery.classList.remove('hidden');
                    officerGallery.innerHTML = officerPhotos.map(url => `
                        <button type="button" onclick="document.getElementById('landingModalCompletionPhoto').src='${url}';" 
                            class="w-12 h-12 rounded-xl overflow-hidden border border-emerald-300 bg-emerald-50 shrink-0 hover:ring-2 hover:ring-emerald-600 transition cursor-pointer">
                            <img src="${url}" class="w-full h-full object-cover">
                        </button>
                    `).join('');
                } else {
                    officerGallery.classList.add('hidden');
                    officerGallery.innerHTML = '';
                }
            }
        } else {
            compContainer.classList.add('hidden');
        }

        // 6. Pelapor Aduan & Nomor Telepon Sensor
        const reporterEl = document.getElementById('landingModalReporter');
        const anonBadge = document.getElementById('landingModalAnonymousBadge');
        const phoneEl = document.getElementById('landingModalReporterPhone');

        if (reporterEl) reporterEl.innerText = item.reporter_name || item.citizen_name || 'Warga Terdaftar';
        if (anonBadge) {
            if (item.is_anonymous) anonBadge.classList.remove('hidden');
            else anonBadge.classList.add('hidden');
        }

        const rawPhone = item.reporter_phone || item.citizen_phone || (item.user && item.user.phone) || '';
        if (phoneEl) {
            if (item.is_anonymous) {
                phoneEl.innerText = '********';
            } else {
                phoneEl.innerText = maskPhonePublic(rawPhone);
            }
        }

        // 7. Kartu Regu Lapangan Pelaksana
        const officerCard = document.getElementById('landingModalOfficerCard');
        if (officerCard) {
            const hasOfficer = item.team_name || item.officer_name || item.assignee_name || item.department_name;
            const isInProgressOrHandled = ['IN_PROGRESS', 'DISPATCHED', 'WAITING_VERIFICATION', 'COMPLETED', 'CLOSED'].includes(item.status);

            if (hasOfficer && isInProgressOrHandled) {
                officerCard.classList.remove('hidden');

                const offNameEl = document.getElementById('landingModalOfficerName');
                const offDeptEl = document.getElementById('landingModalOfficerDept');
                const leaderEl = document.getElementById('landingModalTeamLeader');
                const teamPhoneLink = document.getElementById('landingModalOfficerPhone');
                const teamPhoneText = document.getElementById('landingModalOfficerPhoneText');
                const deadlineEl = document.getElementById('landingModalOfficerDeadline');
                const instEl = document.getElementById('landingModalOfficerInstruction');

                const teamNameStr = item.team_name 
                    ? (item.team_code ? `[${item.team_code}] ${item.team_name}` : item.team_name)
                    : (item.assignee_name || 'Regu Lapangan PUPR');
                if (offNameEl) offNameEl.innerText = teamNameStr;
                if (offDeptEl) offDeptEl.innerText = item.department_name || 'Dinas Pekerjaan Umum dan Penataan Ruang (PUPR)';
                if (leaderEl) leaderEl.innerText = item.team_leader || item.assignee_name || item.officer_name || 'Petugas Lapangan';

                const teamPhone = item.team_leader_phone || item.officer_phone || item.assignee_phone || '081100000004';
                if (teamPhoneText) teamPhoneText.innerText = teamPhone;
                if (teamPhoneLink) teamPhoneLink.href = 'tel:' + teamPhone.replace(/[^0-9]/g, '');

                if (deadlineEl) deadlineEl.innerText = item.deadline || item.target_deadline || item.disposition_deadline || '14 Hari Kerja';
                if (instEl) instEl.innerText = item.instruction || 'Lakukan perbaikan dan penanganan fisik sesuai SOP dinas teknis';
            } else {
                officerCard.classList.add('hidden');
            }
        }

        // 8. Lokasi Kerusakan
        const coordsEl = document.getElementById('landingModalCoords');
        const addrEl = document.getElementById('landingModalFullAddress');
        if (coordsEl) coordsEl.innerText = (item.latitude && item.longitude) ? `${item.latitude}, ${item.longitude}` : '-';
        if (addrEl) addrEl.innerText = item.address || 'Alamat tidak tercatat rinci';

        // 9. Ruas Panjang (Major Road)
        const majorBox = document.getElementById('landingModalMajorRoadBox');
        if (majorBox) {
            if (isMajor) {
                majorBox.classList.remove('hidden');
                const condEl = document.getElementById('landingModalRoadCondition');
                const lenEl = document.getElementById('landingModalRoadLength');
                if (condEl) condEl.innerText = item.road_condition || 'Rusak Parah / Berlubang';
                if (lenEl) {
                    const cleanLen = item.road_length ? item.road_length.toString().replace(/\s*(meter|m)\s*$/i, '').trim() : '';
                    lenEl.innerText = cleanLen ? (cleanLen + ' Meter') : 'Belum diukur';
                }
            } else {
                majorBox.classList.add('hidden');
            }
        }

        // 10. Deskripsi
        const descEl = document.getElementById('landingModalDescription');
        if (descEl) descEl.innerText = item.description || 'Tidak ada catatan deskripsi tambahan.';

        // Buka modal
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        if (window.lucide) window.lucide.createIcons();
    }

    window.closeLandingDetailModal = function() {
        const modal = document.getElementById('landingComplaintDetailModal');
        if (modal) modal.classList.add('hidden');
        document.body.style.overflow = '';
    };

    // Integrasi tombol ESC & klik backdrop
    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') window.closeLandingDetailModal();
    });

    document.addEventListener('click', function(e) {
        const modal = document.getElementById('landingComplaintDetailModal');
        if (modal && e.target === modal) window.closeLandingDetailModal();
    });

    // Alias agar pemanggilan di landing page lama langsung otomatis mengarah ke modal landing page ini
    window.openDetailModal = window.openLandingDetailModal;
    window.closeDetailModal = window.closeLandingDetailModal;
})();
</script>
