<!-- Modal Rincian Aduan Publik (v2 Civic Clarity) -->
<div id="landingComplaintDetailModal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center p-3 sm:p-5 bg-slate-900/60 overscroll-contain" role="dialog" aria-modal="true" aria-labelledby="landingModalTitle">
    <div class="bg-white w-full max-w-2xl rounded-xl border border-[#E2E8F0] overflow-hidden flex flex-col max-h-[92vh]">

        <!-- Header Modal -->
        <div class="px-4 sm:px-6 py-3.5 border-b border-[#E2E8F0] flex items-center justify-between bg-[#F8FAFC] gap-3">
            <div class="flex items-center gap-2 flex-wrap min-w-0">
                <span id="landingModalStatusBadge" class="px-2.5 py-1 rounded-md text-xs font-bold border inline-flex items-center gap-1.5 whitespace-nowrap">
                    STATUS
                </span>
                <span id="landingModalCategoryBadge" class="px-2.5 py-1 rounded-md bg-white text-[#475569] font-semibold text-xs border border-[#E2E8F0] whitespace-nowrap">
                    Infrastruktur
                </span>
                <span id="landingModalTypeBadge" class="px-2.5 py-1 rounded-md text-xs font-semibold bg-white text-[#0F172A] border border-[#E2E8F0] whitespace-nowrap">
                    <span id="landingModalTypeText">Kerusakan Titik</span>
                </span>
            </div>
            <button type="button" onclick="closeLandingDetailModal()" aria-label="Tutup rincian aduan"
                class="min-w-[44px] min-h-[44px] rounded-lg bg-white hover:bg-slate-100 text-[#0F172A] border border-[#E2E8F0] flex items-center justify-center transition cursor-pointer shrink-0">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Isi Modal -->
        <div class="p-4 sm:p-6 overflow-y-auto space-y-5 flex-1 overscroll-contain">

            <!-- Judul & Tanggal -->
            <div>
                <h3 id="landingModalTitle" class="font-outfit font-bold text-lg sm:text-xl text-[#0F172A] leading-snug">
                    Judul Pengaduan
                </h3>
                <div class="flex items-center gap-2 text-xs sm:text-sm text-[#475569] mt-1">
                    <i data-lucide="calendar" class="w-4 h-4 text-[#475569] shrink-0"></i>
                    <span id="landingModalDate">-</span>
                </div>
            </div>

            <!-- 1. Foto Sebelum & Sesudah (Before - After) -->
            <div id="landingModalPhotosGrid" class="grid grid-cols-1 gap-3">
                <!-- Foto Sebelum (Laporan Warga) -->
                <div id="landingModalPhotoContainer" class="space-y-2">
                    <div class="rounded-lg overflow-hidden border border-[#E2E8F0] bg-[#F8FAFC]">
                        <div class="px-3 py-1.5 bg-[#F8FAFC] border-b border-[#E2E8F0] flex items-center gap-1.5 text-xs font-bold text-[#0F172A]">
                            <i data-lucide="camera" class="w-3.5 h-3.5 text-[#475569]"></i>
                            <span id="landingModalPhotoLabel">Sebelum (Kondisi dari Lokasi)</span>
                        </div>
                        <img id="landingModalPhoto" src="" alt="Foto kondisi jalan dari lokasi" class="w-full h-52 sm:h-60 object-cover bg-slate-100">
                    </div>
                    <div id="landingModalCitizenGallery" class="flex items-center gap-2 overflow-x-auto pb-1 hidden"></div>
                </div>

                <!-- Foto Sesudah (Hasil Perbaikan Regu PUPR) -->
                <div id="landingModalCompletionPhotoContainer" class="space-y-2 hidden">
                    <div class="rounded-lg overflow-hidden border border-[#15803D] bg-[#F0FDF4]">
                        <div class="px-3 py-1.5 bg-[#DCFCE7] border-b border-[#BBF7D0] flex items-center gap-1.5 text-xs font-bold text-[#15803D]">
                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-[#15803D]"></i>
                            <span>Sesudah (Hasil Perbaikan PUPR)</span>
                        </div>
                        <img id="landingModalCompletionPhoto" src="" alt="Foto hasil perbaikan regu lapangan" class="w-full h-52 sm:h-60 object-cover bg-emerald-50">
                    </div>
                    <div id="landingModalOfficerGallery" class="flex items-center gap-2 overflow-x-auto pb-1 hidden"></div>
                </div>
            </div>

            <!-- 2. Lokasi Jalan & Kecamatan -->
            <div class="p-3.5 rounded-lg bg-[#F8FAFC] border border-[#E2E8F0] space-y-1">
                <div class="flex items-center gap-1.5 text-xs font-bold text-[#0F172A]">
                    <i data-lucide="map-pin" class="w-4 h-4 text-[#1E3A8A] shrink-0"></i>
                    <span>Lokasi Kerusakan</span>
                </div>
                <p id="landingModalFullAddress" class="text-sm font-medium text-[#0F172A] leading-relaxed">
                    Alamat lokasi kerusakan...
                </p>
            </div>

            <!-- Info Tambahan Ruas Panjang (Jika Major) -->
            <div id="landingModalMajorRoadBox" class="hidden p-3.5 rounded-lg bg-white border border-[#E2E8F0] space-y-2">
                <div class="flex items-center gap-2 text-xs font-bold text-[#1E3A8A]">
                    <i data-lucide="route" class="w-4 h-4 shrink-0"></i>
                    <span>Keterangan Ruas Jalan Panjang</span>
                </div>
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="p-2.5 rounded-md bg-[#F8FAFC] border border-[#E2E8F0]">
                        <span class="text-[#475569] block">Kondisi Jalan</span>
                        <span id="landingModalRoadCondition" class="font-bold text-[#0F172A] mt-0.5 block">-</span>
                    </div>
                    <div class="p-2.5 rounded-md bg-[#F8FAFC] border border-[#E2E8F0]">
                        <span class="text-[#475569] block">Perkiraan Panjang</span>
                        <span id="landingModalRoadLength" class="font-bold text-[#0F172A] mt-0.5 block">-</span>
                    </div>
                </div>
            </div>

            <!-- 3. Regu Pelaksana -->
            <div id="landingModalOfficerCard" class="hidden p-3.5 rounded-lg bg-white border border-[#E2E8F0] space-y-2.5">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-2.5 min-w-0">
                        <div class="w-9 h-9 rounded-lg bg-[#1E3A8A] text-white flex items-center justify-center shrink-0">
                            <i data-lucide="hard-hat" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-xs text-[#475569] font-semibold block">Regu Pelaksana Lapangan</span>
                            <h4 id="landingModalOfficerName" class="font-outfit font-bold text-sm text-[#0F172A] truncate">
                                Regu Lapangan PUPR
                            </h4>
                            <span id="landingModalOfficerDept" class="text-xs text-[#475569] block truncate">
                                Dinas Pekerjaan Umum dan Penataan Ruang (PUPR)
                            </span>
                        </div>
                    </div>
                    <div id="landingModalDeadlineBox" class="text-right shrink-0">
                        <span class="text-[11px] text-[#475569] block">Target Selesai</span>
                        <span id="landingModalOfficerDeadline" class="text-xs font-bold text-[#0F172A] bg-[#F8FAFC] px-2 py-0.5 rounded border border-[#E2E8F0] inline-block mt-0.5">
                            -
                        </span>
                    </div>
                </div>

                <div class="pt-2 border-t border-[#E2E8F0] flex items-center justify-between gap-2 text-xs">
                    <span class="text-[#475569]">Koordinator: <strong id="landingModalTeamLeader" class="text-[#0F172A]">Petugas</strong></span>
                    <a id="landingModalOfficerPhone" href="#" class="text-[#1E3A8A] font-bold hover:underline inline-flex items-center gap-1">
                        <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                        <span id="landingModalOfficerPhoneText">-</span>
                    </a>
                </div>
            </div>

            <!-- 4. Accordion Detail Teknis Internal (Terlipat secara default agar bersih) -->
            <details class="group rounded-lg border border-[#E2E8F0] bg-[#F8FAFC]">
                <summary class="px-4 py-3 text-xs sm:text-sm font-semibold text-[#0F172A] cursor-pointer flex items-center justify-between select-none list-none min-h-[44px]">
                    <span class="flex items-center gap-2">
                        <i data-lucide="file-text" class="w-4 h-4 text-[#475569]"></i>
                        <span>Lihat Catatan Lengkap &amp; Detail Teknis</span>
                    </span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-[#475569] transition-transform group-open:rotate-180"></i>
                </summary>
                <div class="px-4 pb-4 pt-2 border-t border-[#E2E8F0] bg-white space-y-3 text-xs">
                    <div>
                        <span class="text-[#475569] font-semibold block mb-1">Keterangan Kondisi:</span>
                        <div id="landingModalDescription" class="p-3 rounded-md bg-[#F8FAFC] border border-[#E2E8F0] text-[#0F172A] leading-relaxed whitespace-pre-line">
                            -
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div class="p-2.5 rounded-md bg-[#F8FAFC] border border-[#E2E8F0]">
                            <span class="text-[#475569] block">Pelapor</span>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span id="landingModalReporter" class="font-bold text-[#0F172A]">Warga Terdaftar</span>
                                <span id="landingModalAnonymousBadge" class="hidden text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-200 text-[#0F172A]">Anonim</span>
                            </div>
                            <span id="landingModalReporterPhone" class="font-mono text-[11px] text-[#475569] block mt-0.5">********</span>
                        </div>
                        <div class="p-2.5 rounded-md bg-[#F8FAFC] border border-[#E2E8F0]">
                            <span class="text-[#475569] block">Nomor Tiket &amp; Titik Peta</span>
                            <span id="landingModalCode" class="font-mono font-bold text-[#0F172A] block mt-0.5">#ADU-0000</span>
                            <span id="landingModalCoords" class="font-mono text-[11px] text-[#475569] block mt-0.5">-</span>
                        </div>
                    </div>
                    <div id="landingModalInstructionRow" class="p-2.5 rounded-md bg-[#F8FAFC] border border-[#E2E8F0] text-[#475569]">
                        <span class="font-semibold text-[#0F172A]">Catatan Penanganan:</span>
                        <span id="landingModalOfficerInstruction">-</span>
                    </div>
                </div>
            </details>

        </div>

        <!-- Footer Modal -->
        <div class="px-4 sm:px-6 py-3 border-t border-[#E2E8F0] bg-[#F8FAFC] flex items-center justify-end">
            <button type="button" onclick="closeLandingDetailModal()" class="min-h-[44px] px-5 py-2 rounded-lg bg-[#1E3A8A] hover:bg-[#172554] text-white font-semibold text-sm transition cursor-pointer">
                Tutup
            </button>
        </div>

    </div>
</div>

<script>
(function() {
    const MODAL_STATUS_MAP = {
        SUBMITTED:            { label: 'Masuk / Verifikasi', cls: 'bg-[#FEE2E2] text-[#DC2626] border-[#FECACA]', icon: 'alert-circle' },
        VERIFICATION:         { label: 'Masuk / Verifikasi', cls: 'bg-[#FEE2E2] text-[#DC2626] border-[#FECACA]', icon: 'alert-circle' },
        DISPATCHED:           { label: 'Ditugaskan',         cls: 'bg-[#FEF3C7] text-[#92400E] border-[#FDE68A]', icon: 'clock' },
        IN_PROGRESS:          { label: 'Sedang Dikerjakan',  cls: 'bg-[#FEF3C7] text-[#92400E] border-[#FDE68A]', icon: 'wrench' },
        WAITING_VERIFICATION: { label: 'Pemeriksaan Mutu',   cls: 'bg-[#FEF3C7] text-[#92400E] border-[#FDE68A]', icon: 'clipboard-check' },
        REVISION_REQUIRED:    { label: 'Perbaikan Lanjutan', cls: 'bg-[#FEF3C7] text-[#92400E] border-[#FDE68A]', icon: 'wrench' },
        COMPLETED:            { label: 'Selesai',            cls: 'bg-[#DCFCE7] text-[#15803D] border-[#BBF7D0]', icon: 'check-circle-2' },
        CLOSED:               { label: 'Selesai',            cls: 'bg-[#DCFCE7] text-[#15803D] border-[#BBF7D0]', icon: 'check-circle-2' },
        REJECTED:             { label: 'Ditolak',            cls: 'bg-[#FEE2E2] text-[#DC2626] border-[#FECACA]', icon: 'x-circle' }
    };

    function maskPhonePublic(rawPhone) {
        if (!rawPhone || rawPhone === '-' || rawPhone === 'null') return '********';
        const clean = rawPhone.toString().replace(/[^0-9]/g, '');
        if (clean.length >= 3) {
            return '*'.repeat(Math.max(clean.length - 3, 6)) + clean.slice(-3);
        }
        return '********';
    }

    function renderThumbnailStrip(galleryEl, photos, targetImgId, ringColor) {
        if (!galleryEl) return;
        if (photos.length <= 1) {
            galleryEl.classList.add('hidden');
            galleryEl.innerHTML = '';
            return;
        }
        galleryEl.classList.remove('hidden');
        galleryEl.innerHTML = photos.map((url, idx) => `
            <button type="button" onclick="document.getElementById('${targetImgId}').src='${url}';" aria-label="Lihat foto ${idx + 1}"
                class="w-12 h-12 rounded-md overflow-hidden border border-[#E2E8F0] bg-[#F8FAFC] shrink-0 hover:ring-2 hover:ring-[${ringColor}] transition cursor-pointer">
                <img src="${url}" alt="Thumbnail foto ${idx + 1}" class="w-full h-full object-cover">
            </button>
        `).join('');
    }

    window.openLandingDetailModal = function(dataOrId) {
        if (typeof dataOrId === 'object' && dataOrId !== null) {
            renderLandingModal(dataOrId);
        } else if (typeof dataOrId === 'number' || typeof dataOrId === 'string') {
            if (window.landingComplaintsMap && window.landingComplaintsMap[dataOrId]) {
                renderLandingModal(window.landingComplaintsMap[dataOrId]);
            } else {
                fetch(`/api/v1/complaints/${dataOrId}`)
                    .then(res => res.json())
                    .then(res => { if (res.data) renderLandingModal(res.data); })
                    .catch(err => console.error('[LandingModal] Gagal memuat pengaduan:', err));
            }
        }
    };

    function renderLandingModal(item) {
        if (!item) return;
        const modal = document.getElementById('landingComplaintDetailModal');
        if (!modal) return;

        // 1. Judul, Tanggal, Kategori & Tipe
        document.getElementById('landingModalCode').innerText = item.public_code ? ('#' + item.public_code) : '#ADU-0000';
        document.getElementById('landingModalTitle').innerText = item.title || 'Laporan Kerusakan';
        document.getElementById('landingModalCategoryBadge').innerText = item.category_name || item.category?.name || 'Infrastruktur';
        document.getElementById('landingModalDate').innerText = item.created_at_formatted || item.created_at || '-';

        const isMajor = item.damage_type === 'major';
        const typeText = document.getElementById('landingModalTypeText');
        if (typeText) typeText.innerText = isMajor ? 'Ruas Jalan Panjang' : 'Kerusakan Titik';

        // 2. Status Badge dengan ikon & teks
        const statusInfo = MODAL_STATUS_MAP[item.status] || { label: item.status || 'Diproses', cls: 'bg-slate-100 text-[#0F172A] border-[#E2E8F0]', icon: 'info' };
        const statusBadge = document.getElementById('landingModalStatusBadge');
        if (statusBadge) {
            statusBadge.className = `px-2.5 py-1 rounded-md text-xs font-bold border inline-flex items-center gap-1.5 whitespace-nowrap ${statusInfo.cls}`;
            statusBadge.innerHTML = `<i data-lucide="${statusInfo.icon}" class="w-3.5 h-3.5 shrink-0"></i><span>${statusInfo.label}</span>`;
        }

        // 3. Foto Sebelum (Warga) & Foto Sesudah (Petugas)
        const photosGrid = document.getElementById('landingModalPhotosGrid');
        const photoContainer = document.getElementById('landingModalPhotoContainer');
        const photoImg = document.getElementById('landingModalPhoto');
        const citizenGallery = document.getElementById('landingModalCitizenGallery');

        let citizenPhotos = [];
        if (Array.isArray(item.photos)) {
            citizenPhotos = item.photos.map(p => (typeof p === 'string' ? p : (p.url || (p.storage_key ? '/storage/' + p.storage_key : '')))).filter(Boolean);
        } else if (item.photo_url) {
            citizenPhotos = [item.photo_url];
        }

        if (citizenPhotos.length > 0) {
            photoImg.src = citizenPhotos[0];
            photoContainer.classList.remove('hidden');
            renderThumbnailStrip(citizenGallery, citizenPhotos, 'landingModalPhoto', '#1E3A8A');
        } else {
            photoContainer.classList.add('hidden');
        }

        const compContainer = document.getElementById('landingModalCompletionPhotoContainer');
        const compImg = document.getElementById('landingModalCompletionPhoto');
        const officerGallery = document.getElementById('landingModalOfficerGallery');
        const officerPhotos = (item.officer_photos && item.officer_photos.length > 0)
            ? item.officer_photos
            : (item.completion_photo_url ? [item.completion_photo_url] : []);

        if (officerPhotos.length > 0) {
            compImg.src = officerPhotos[0];
            compContainer.classList.remove('hidden');
            renderThumbnailStrip(officerGallery, officerPhotos, 'landingModalCompletionPhoto', '#15803D');
            if (photosGrid) photosGrid.className = 'grid grid-cols-1 sm:grid-cols-2 gap-3';
        } else {
            compContainer.classList.add('hidden');
            if (photosGrid) photosGrid.className = 'grid grid-cols-1 gap-3';
        }

        // 4. Lokasi & Ruas Panjang
        const coordsEl = document.getElementById('landingModalCoords');
        const addrEl = document.getElementById('landingModalFullAddress');
        if (coordsEl) coordsEl.innerText = (item.latitude && item.longitude) ? `${item.latitude}, ${item.longitude}` : '-';
        if (addrEl) addrEl.innerText = item.address || 'Alamat belum tercatat rinci';

        const majorBox = document.getElementById('landingModalMajorRoadBox');
        if (majorBox) {
            majorBox.classList.toggle('hidden', !isMajor);
            if (isMajor) {
                document.getElementById('landingModalRoadCondition').innerText = item.road_condition || 'Rusak Parah / Berlubang';
                const cleanLen = item.road_length ? item.road_length.toString().replace(/\s*(meter|m)\s*$/i, '').trim() : '';
                document.getElementById('landingModalRoadLength').innerText = cleanLen ? (cleanLen + ' Meter') : 'Belum diukur';
            }
        }

        // 5. Regu Pelaksana
        const officerCard = document.getElementById('landingModalOfficerCard');
        if (officerCard) {
            const hasOfficer = item.team_name || item.officer_name || item.assignee_name || item.department_name;
            const isHandled = ['IN_PROGRESS', 'DISPATCHED', 'WAITING_VERIFICATION', 'REVISION_REQUIRED', 'COMPLETED', 'CLOSED'].includes(item.status);
            if (hasOfficer && isHandled) {
                officerCard.classList.remove('hidden');
                const teamNameStr = item.team_name
                    ? (item.team_code ? `[${item.team_code}] ${item.team_name}` : item.team_name)
                    : (item.assignee_name || 'Regu Lapangan PUPR');
                document.getElementById('landingModalOfficerName').innerText = teamNameStr;
                document.getElementById('landingModalOfficerDept').innerText = item.department_name || 'Dinas Pekerjaan Umum dan Penataan Ruang (PUPR)';
                document.getElementById('landingModalTeamLeader').innerText = item.team_leader || item.assignee_name || item.officer_name || 'Petugas Lapangan';

                const teamPhone = item.team_leader_phone || item.officer_phone || item.assignee_phone || '081100000004';
                document.getElementById('landingModalOfficerPhoneText').innerText = teamPhone;
                document.getElementById('landingModalOfficerPhone').href = 'tel:' + teamPhone.replace(/[^0-9]/g, '');
                document.getElementById('landingModalOfficerDeadline').innerText = item.deadline || item.target_deadline || item.disposition_deadline || '14 Hari Kerja';
                document.getElementById('landingModalOfficerInstruction').innerText = item.instruction || 'Penanganan fisik sesuai standar teknis dinas';
            } else {
                officerCard.classList.add('hidden');
            }
        }

        // 6. Pelapor (Disensor UU PDP) & Deskripsi di dalam Accordion
        document.getElementById('landingModalReporter').innerText = item.reporter_name || item.citizen_name || 'Warga Terdaftar';
        document.getElementById('landingModalAnonymousBadge').classList.toggle('hidden', !item.is_anonymous);
        const rawPhone = item.reporter_phone || item.citizen_phone || (item.user && item.user.phone) || '';
        document.getElementById('landingModalReporterPhone').innerText = item.is_anonymous ? '********' : maskPhonePublic(rawPhone);
        document.getElementById('landingModalDescription').innerText = item.description || 'Tidak ada catatan tambahan.';

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        if (window.lucide) window.lucide.createIcons();
    }

    window.closeLandingDetailModal = function() {
        const modal = document.getElementById('landingComplaintDetailModal');
        if (modal) modal.classList.add('hidden');
        document.body.style.overflow = '';
    };

    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') window.closeLandingDetailModal();
    });

    document.addEventListener('click', function(e) {
        const modal = document.getElementById('landingComplaintDetailModal');
        if (modal && e.target === modal) window.closeLandingDetailModal();
    });

    window.openDetailModal = window.openLandingDetailModal;
    window.closeDetailModal = window.closeLandingDetailModal;
})();
</script>
