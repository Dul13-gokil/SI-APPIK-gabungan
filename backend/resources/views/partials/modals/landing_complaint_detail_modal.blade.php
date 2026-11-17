<!-- Modal Detail Pengaduan Publik (Ringan, Responsif, Desktop 760px & Mobile Gap Lega) -->
<div id="landingComplaintDetailModal" class="fixed inset-0 z-[9999] hidden flex items-end sm:items-center justify-center p-3.5 sm:p-5 bg-slate-900/65 overscroll-contain">
    <div id="landingModalCard" class="bg-white w-full sm:max-w-[760px] rounded-[24px] shadow-[0_20px_50px_rgba(15,23,42,0.22)] border border-[#E2E8F0] overflow-hidden flex flex-col max-h-[86vh] sm:max-h-[90vh]">
        
        <!-- Drag Handle Khusus Mobile Bottom Sheet -->
        <div class="sm:hidden flex justify-center pt-3 pb-1 bg-white shrink-0">
            <span class="w-11 h-1 rounded-full bg-[#CBD5E1]"></span>
        </div>

        <!-- Sticky Modal Top Header (3 Badge 1 Baris + Tanggal & Kode Aduan + Tombol X di Tengah Kanan) -->
        <div class="px-4 sm:px-7 pt-2.5 pb-3.5 sm:py-5 border-b border-[#E2E8F0] bg-white flex items-center justify-between gap-2.5 shrink-0">
            <div class="min-w-0 flex-1 space-y-2">
                <!-- Baris 1: Selalu 1 Baris di Mobile & Desktop -->
                <div class="flex items-center gap-1 sm:gap-2 flex-nowrap overflow-x-auto no-scrollbar">
                    <!-- 1. Tingkat Kerusakan -->
                    <span id="landingModalTypeBadge" class="shrink-0 px-2 sm:px-3 py-0.5 sm:py-1 rounded-full text-[10.5px] sm:text-xs font-bold bg-[#FEF2F2] text-[#DC2626] border border-[#FECACA] inline-flex items-center gap-1 whitespace-nowrap">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#DC2626] shrink-0"></span>
                        <span id="landingModalTypeText">Kerusakan Ringan</span>
                    </span>
                    <!-- 2. Kategori Infrastruktur -->
                    <span id="landingModalCategoryBadge" class="shrink min-w-0 truncate px-2 sm:px-3 py-0.5 sm:py-1 rounded-full bg-[#F1F5F9] text-[#475569] font-semibold text-[10.5px] sm:text-xs border border-[#E2E8F0]">
                        Infrastruktur
                    </span>
                    <!-- 3. Status Pengerjaan -->
                    <span id="landingModalStatusBadge" class="shrink-0 px-2 sm:px-3 py-0.5 sm:py-1 rounded-full text-[10.5px] sm:text-xs font-bold whitespace-nowrap border bg-[#ECFDF5] text-[#047857] border-[#A7F3D0]">
                        STATUS
                    </span>
                </div>

                <!-- Baris 2: Tanggal Aduan + Kode Aduan -->
                <div class="flex items-center flex-wrap gap-2 text-[11.5px] sm:text-[13px] text-[#475569]">
                    <div class="flex items-center gap-1.5 font-medium">
                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-[#64748B] shrink-0"></i>
                        <span id="landingModalDate">01 Jan 2026, 00:00 WITA</span>
                    </div>
                    <span class="text-slate-300">&bull;</span>
                    <span id="landingModalCode" class="font-mono font-bold text-[#2563EB] bg-[#EFF6FF] border border-[#BFDBFE] px-2 py-0.5 rounded-full text-[10.5px] sm:text-[11px]">#ADU-0000</span>
                </div>
            </div>

            <!-- Tombol Tutup X di Tengah Kanan -->
            <button type="button" onclick="closeLandingDetailModal()" class="w-8 h-8 sm:w-9 sm:h-9 rounded-[10px] bg-[#F8FAFC] hover:bg-slate-100 text-[#475569] hover:text-[#0F172A] border border-[#E2E8F0] flex items-center justify-center transition cursor-pointer shrink-0 focus-visible:outline-2 focus-visible:outline-[#2563EB]" title="Tutup Detail" aria-label="Tutup Rincian">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Modal Scrollable Body -->
        <div class="px-5 py-4 sm:px-7 sm:py-5 overflow-y-auto space-y-4 flex-1 overscroll-contain bg-white" style="-webkit-overflow-scrolling: touch;">
            
            <!-- Judul Aduan -->
            <h3 id="landingModalTitle" class="font-outfit font-bold text-lg sm:text-xl text-[#0F172A] leading-snug">
                Judul Pengaduan
            </h3>

            <!-- 1. Galeri Foto Berdampingan 2 Kolom (Rasio 16:10 Side-by-Side) -->
            <div class="grid grid-cols-2 gap-2.5 sm:gap-3.5">
                <!-- Kiri: Foto Kondisi Lokasi -->
                <div id="landingModalPhotoContainer" class="space-y-1.5">
                    <div class="aspect-[16/10] rounded-xl sm:rounded-2xl overflow-hidden border border-[#E2E8F0] bg-[#1E293B] relative flex items-center justify-center">
                        <img id="landingModalPhoto" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="Foto Kondisi Lokasi" onclick="openLandingImageLightbox(this.src)" class="w-full h-full object-cover cursor-zoom-in">
                        <div class="absolute bottom-2 left-2 sm:bottom-3 sm:left-3 bg-[#0F172A]/85 text-white text-[10px] sm:text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1.5 pointer-events-none">
                            <i data-lucide="camera" class="w-3 h-3 shrink-0 hidden sm:inline"></i>
                            <span id="landingModalPhotoLabel">Foto Kondisi Lokasi</span>
                        </div>
                    </div>
                    <div id="landingModalCitizenGallery" class="flex items-center gap-1.5 overflow-x-auto pb-0.5 hidden"></div>
                </div>

                <!-- Kanan: Foto Hasil Pengerjaan (Selalu tampil berdampingan, fallback rapi bila belum selesai) -->
                <div id="landingModalCompletionPhotoContainer" class="space-y-1.5">
                    <div class="aspect-[16/10] rounded-xl sm:rounded-2xl overflow-hidden border border-[#A7F3D0] bg-[#ECFDF5] relative flex items-center justify-center">
                        <img id="landingModalCompletionPhoto" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="Foto Hasil Pengerjaan" onclick="openLandingImageLightbox(this.src)" class="w-full h-full object-cover cursor-zoom-in">
                        <div id="landingModalCompletionPlaceholder" class="hidden inset-0 absolute flex flex-col items-center justify-center text-center p-3 text-[#047857] bg-[#ECFDF5]">
                            <i data-lucide="clock" class="w-5 h-5 mb-1 text-[#10B981]"></i>
                            <span class="text-[11px] sm:text-xs font-semibold leading-tight">Menunggu Foto Hasil Pengerjaan</span>
                        </div>
                        <div class="absolute bottom-2 left-2 sm:bottom-3 sm:left-3 bg-[#047857]/90 text-white text-[10px] sm:text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1.5 pointer-events-none">
                            <i data-lucide="badge-check" class="w-3 h-3 shrink-0 hidden sm:inline"></i>
                            <span>Foto Hasil Pengerjaan</span>
                        </div>
                    </div>
                    <div id="landingModalOfficerGallery" class="flex items-center gap-1.5 overflow-x-auto pb-0.5 hidden"></div>
                </div>
            </div>

            <!-- 2. Grid Informasi Pelapor & Lokasi (2 Kolom Seimbang di Desktop, Bertumpuk di Mobile) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-3.5">
                <!-- Kartu Kiri: Pelapor Aduan -->
                <div class="p-4 rounded-2xl bg-[#F8FAFC] border border-[#E2E8F0] space-y-1.5">
                    <div class="flex items-center gap-1.5 text-[11px] font-bold text-[#475569] uppercase tracking-wider">
                        <i data-lucide="user" class="w-3.5 h-3.5 text-[#2563EB]"></i>
                        <span>PELAPOR ADUAN</span>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span id="landingModalReporter" class="font-outfit font-bold text-base text-[#0F172A] leading-tight">
                            Warga Terdaftar
                        </span>
                        <span id="landingModalAnonymousBadge" class="hidden text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-200 text-[#0F172A]">Anonim</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-[13px] font-semibold text-[#047857]" title="Nomor telepon pelapor disamarkan demi melindungi privasi (hanya 3 digit terakhir terlihat)">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 shrink-0"></i>
                        <span id="landingModalReporterPhone" class="font-mono tracking-wide">********---</span>
                        <span class="text-xs font-medium">(Terverifikasi)</span>
                    </div>
                </div>

                <!-- Kartu Kanan: Lokasi Kerusakan -->
                <div class="p-4 rounded-2xl bg-[#F8FAFC] border border-[#E2E8F0] space-y-2">
                    <div class="flex items-center gap-1.5 text-[11px] font-bold text-[#475569] uppercase tracking-wider">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#2563EB]"></i>
                        <span>LOKASI KERUSAKAN</span>
                    </div>
                    <p id="landingModalFullAddress" class="text-[13.5px] font-semibold text-[#0F172A] leading-snug">
                        Alamat lokasi kerusakan...
                    </p>
                    <a id="landingModalMapsLink" href="#" target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#EFF6FF] hover:bg-[#DBEAFE] text-[#2563EB] border border-[#BFDBFE] text-xs font-bold transition cursor-pointer" title="Buka titik lokasi di Google Maps">
                        <i data-lucide="map" class="w-3.5 h-3.5 shrink-0"></i>
                        <span>Buka di Peta</span>
                    </a>
                </div>
            </div>

            <!-- 3. Kartu Regu Lapangan Pelaksana -->
            <div id="landingModalOfficerCard" class="hidden p-4 rounded-2xl bg-[#EFF6FF] border border-[#BFDBFE] space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2.5">
                    <div class="flex items-start gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-xl bg-[#2563EB] text-white flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="hard-hat" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <h4 id="landingModalOfficerName" class="font-outfit font-bold text-[15px] text-[#0F172A] leading-snug">
                                Regu Aspal 01 - Lasusua
                            </h4>
                            <span id="landingModalOfficerDept" class="text-xs font-medium text-[#475569] block mt-0.5">
                                Dinas Pekerjaan Umum dan Penataan Ruang (PUPR)
                            </span>
                        </div>
                    </div>
                    <div id="landingModalDeadlineBox" class="shrink-0 self-start">
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-white border border-[#BFDBFE] text-[11px] font-bold text-[#1D4ED8]">
                            <span>Batas Waktu:</span>
                            <span id="landingModalOfficerDeadline">-</span>
                        </span>
                    </div>
                </div>

                <!-- Kontak Ketua Regu -->
                <div class="flex flex-wrap items-center justify-between gap-2 pt-0.5">
                    <div class="text-[13px] font-semibold text-[#0F172A]">
                        <span class="text-[#475569] font-medium">Ketua Regu (Koordinator Lapangan):</span>
                        <span id="landingModalTeamLeader" class="font-bold text-[#0F172A] ml-1">Petugas Lapangan</span>
                    </div>
                    <a id="landingModalOfficerPhone" href="#" class="px-3 py-1.5 rounded-full bg-white hover:bg-blue-100/70 border border-[#BFDBFE] text-[#2563EB] font-bold font-mono text-xs inline-flex items-center gap-1.5 transition shrink-0" title="Hubungi Petugas Lapangan">
                        <i data-lucide="phone" class="w-3 h-3"></i>
                        <span id="landingModalOfficerPhoneText">081100000004</span>
                    </a>
                </div>

                <!-- Instruksi Teknis -->
                <div id="landingModalInstructionRow" class="px-3 py-2.5 rounded-xl bg-white border border-[#DBEAFE] text-[13px] text-[#334155] flex items-start gap-2">
                    <i data-lucide="clipboard-list" class="w-4 h-4 text-[#2563EB] shrink-0 mt-0.5"></i>
                    <span>Instruksi: <span id="landingModalOfficerInstruction" class="font-medium text-[#0F172A]">Lakukan perbaikan dan penanganan fisik sesuai standar teknis</span></span>
                </div>
            </div>

            <!-- 4. Spesifikasi Ruas Jalan Rusak Panjang -->
            <div id="landingModalMajorRoadBox" class="hidden rounded-2xl p-4 bg-[#EFF6FF] border border-[#BFDBFE] text-[#2563EB] space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="route" class="w-4 h-4 text-[#2563EB]"></i>
                        <span class="text-xs font-bold uppercase tracking-wider">Kerusakan Ruas Panjang</span>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#2563EB] text-white">Berat</span>
                </div>
                <div class="grid grid-cols-2 gap-2.5 text-xs pt-1">
                    <div class="p-2.5 rounded-xl bg-white border border-[#BFDBFE]">
                        <span class="text-[11px] text-[#475569] block">Kondisi Kerusakan:</span>
                        <span id="landingModalRoadCondition" class="font-bold text-[#0F172A]">-</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-white border border-[#BFDBFE]">
                        <span class="text-[11px] text-[#475569] block">Estimasi Panjang:</span>
                        <span id="landingModalRoadLength" class="font-bold text-[#0F172A]">-</span>
                    </div>
                </div>
            </div>

            <!-- 5. Deskripsi Kondisi Kerusakan -->
            <div class="space-y-1.5">
                <h4 class="text-[11px] font-bold text-[#475569] uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="file-text" class="w-3.5 h-3.5 text-[#64748B]"></i>
                    <span>Deskripsi Kondisi Kerusakan</span>
                </h4>
                <div id="landingModalDescription" class="p-4 rounded-2xl bg-[#F8FAFC] border border-[#E2E8F0] text-xs sm:text-[13px] text-[#334155] leading-relaxed whitespace-pre-line font-sans">
                    Kondisi kerusakan infrastruktur...
                </div>
            </div>

        </div>

        <!-- Modal Footer Actions (Seragam Modal Admin & Role) -->
        <div class="px-5 py-3.5 sm:px-7 border-t border-[#E2E8F0] bg-white flex items-center justify-end shrink-0">
            <button type="button" onclick="closeLandingDetailModal()" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#2563EB]">
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
            statusBadge.className = `shrink-0 px-2 sm:px-3 py-0.5 sm:py-1 rounded-full text-[10.5px] sm:text-xs font-bold whitespace-nowrap border ${statusClass}`;
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

        // 5. Foto Pengerjaan Petugas Lapangan (Selalu tampil 2 kolom sejajar; fallback placeholder bila belum selesai)
        const compContainer = document.getElementById('landingModalCompletionPhotoContainer');
        const compImg = document.getElementById('landingModalCompletionPhoto');
        const compPlaceholder = document.getElementById('landingModalCompletionPlaceholder');
        const officerGallery = document.getElementById('landingModalOfficerGallery');

        const officerPhotos = (item.officer_photos && item.officer_photos.length > 0)
            ? item.officer_photos
            : (item.completion_photo_url ? [item.completion_photo_url] : []);

        if (compContainer) compContainer.classList.remove('hidden');

        if (officerPhotos.length > 0) {
            if (compImg) {
                compImg.src = officerPhotos[0];
                compImg.classList.remove('hidden');
            }
            if (compPlaceholder) compPlaceholder.classList.add('hidden');

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
            if (compImg) compImg.classList.add('hidden');
            if (compPlaceholder) compPlaceholder.classList.remove('hidden');
            if (officerGallery) {
                officerGallery.classList.add('hidden');
                officerGallery.innerHTML = '';
            }
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

        // 8. Lokasi Kerusakan + Link Google Maps
        const coordsEl = document.getElementById('landingModalCoords');
        const mapsLink = document.getElementById('landingModalMapsLink');
        const addrEl = document.getElementById('landingModalFullAddress');
        const hasCoords = Boolean(item.latitude && item.longitude);
        if (coordsEl) coordsEl.innerText = hasCoords ? `${item.latitude}, ${item.longitude}` : '-';
        if (mapsLink) {
            if (hasCoords) {
                mapsLink.href = `https://www.google.com/maps?q=${encodeURIComponent(item.latitude + ',' + item.longitude)}`;
                mapsLink.classList.remove('pointer-events-none', 'opacity-60');
            } else {
                mapsLink.href = '#';
                mapsLink.classList.add('pointer-events-none', 'opacity-60');
            }
        }
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

        // Buka modal (Render ikon hanya di dalam modal agar instan tanpa lag)
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        if (window.lucide) window.lucide.createIcons({ root: modal });
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

    window.copyLandingCoords = function() {
        const el = document.getElementById('landingModalCoords');
        if (!el) return;
        const txt = el.innerText.trim();
        if (!txt || txt === '-' || txt === 'Tersalin!') return;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(txt).then(() => {
                el.innerText = 'Tersalin!';
                setTimeout(() => { el.innerText = txt; }, 1600);
            }).catch(() => {});
        }
    };

    window.openLandingImageLightbox = function(src) {
        if (!src || src.startsWith('data:image/gif')) return;
        let overlay = document.getElementById('landingImageLightbox');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'landingImageLightbox';
            overlay.className = 'fixed inset-0 z-[10000] bg-slate-950/90 flex items-center justify-center p-4 cursor-zoom-out';
            overlay.innerHTML = '<img id="landingLightboxImg" src="" alt="Preview Foto" class="max-w-full max-h-[90vh] rounded-2xl shadow-2xl object-contain">';
            overlay.addEventListener('click', () => overlay.classList.add('hidden'));
            document.body.appendChild(overlay);
        }
        document.getElementById('landingLightboxImg').src = src;
        overlay.classList.remove('hidden');
    };
})();
</script>
