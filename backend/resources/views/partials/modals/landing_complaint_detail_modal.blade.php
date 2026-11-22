<!-- Modal Detail Pengaduan Publik (SI-APPIK Maroon & Cream · Frosted Glass, Subtle Rounded, Connected Stepper Desktop & Mobile) -->
<div id="landingComplaintDetailModal" role="dialog" aria-modal="true" aria-labelledby="landingModalTitle"
    class="fixed inset-0 z-[9999] hidden flex items-end sm:items-center justify-center p-0 sm:p-6 bg-[#4A0012]/60 backdrop-blur-[6px] overscroll-contain transition-opacity duration-250 smooth-ease opacity-0">
    <div id="landingModalCard"
        class="bg-white/92 backdrop-blur-2xl w-full sm:max-w-[820px] rounded-t-[14px] sm:rounded-[12px] border-t-[4px] border-t-[#800020] shadow-[0_28px_72px_rgba(74,0,18,0.38)] overflow-hidden flex flex-col max-h-[92vh] sm:max-h-[90vh] transition-all duration-300 smooth-ease translate-y-5 sm:translate-y-3 sm:scale-[0.98] opacity-0">

        <!-- Subtle Mobile Bottom Sheet Drag Cue -->
        <div class="sm:hidden flex justify-center pt-2.5 pb-0.5 bg-transparent shrink-0">
            <span class="w-10 h-1 rounded-full bg-[#E6D5C1]"></span>
        </div>

        <!-- Scrollable Main Surface -->
        <div class="px-5 pt-4 pb-5 sm:px-8 sm:pt-7 sm:pb-6 overflow-y-auto space-y-5 flex-1 overscroll-contain bg-transparent" style="-webkit-overflow-scrolling: touch;">

            <!-- Top Row: Solid Pekat Badge + Ticket & Date + Close Button -->
            <div class="flex items-start justify-between gap-3">
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5 min-w-0">
                    <span id="landingModalStatusBadge" class="shrink-0 px-3.5 py-1.5 rounded-[6px] text-[12px] font-bold bg-[#047857] text-white leading-none">
                        Selesai
                    </span>
                    <span class="text-[12.5px] sm:text-[13px] text-[#6D4E55] font-normal">
                        <span id="landingModalCode" class="font-medium text-[#593B42]">#ADU-0000</span>
                        <span class="mx-1.5">&middot;</span>
                        <span>Dilaporkan <span id="landingModalDate">01 Jan 2026, 00:00 WITA</span></span>
                    </span>
                </div>

                <button type="button" onclick="closeLandingDetailModal()" aria-label="Tutup Rincian Laporan"
                    class="w-10 h-10 sm:w-9 sm:h-9 rounded-[8px] bg-[#F3E6D5]/85 backdrop-blur-md hover:bg-[#E6D5C1] active:scale-[0.95] text-[#2B0F16] flex items-center justify-center transition-colors cursor-pointer shrink-0 focus-visible:outline-none">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Title & Category Subtitle -->
            <div class="space-y-1">
                <h3 id="landingModalTitle" class="font-outfit font-bold text-[20px] sm:text-[24px] text-[#2B0F16] leading-tight">
                    Judul Pengaduan
                </h3>
                <p class="text-[13px] sm:text-[13.5px] text-[#6D4E55]">
                    <span id="landingModalCategoryBadge">Infrastruktur</span>
                    <span class="mx-1.5">&middot;</span>
                    <span id="landingModalTypeText">Kerusakan Ringan</span>
                </p>
            </div>

            <!-- 1. Side-by-Side Before & After Photos (Subtle 8px Rounded + Frosted Glass Tags, Zero Outline) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div id="landingModalPhotoContainer" class="space-y-1.5">
                    <div class="aspect-[16/10] sm:h-[208px] w-full rounded-[8px] overflow-hidden bg-[#4A0012] relative flex items-center justify-center group">
                        <img id="landingModalPhoto" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                            alt="Foto Kondisi Dilaporkan" onclick="openLandingImageLightbox(this.src)"
                            class="w-full h-full object-cover cursor-zoom-in transition-transform duration-500 smooth-ease group-hover:scale-[1.03]">
                        <div class="absolute bottom-3 left-3 bg-[#4A0012]/84 backdrop-blur-md text-white text-[11.5px] font-semibold px-3 py-1.5 rounded-[6px] pointer-events-none">
                            Kondisi Dilaporkan
                        </div>
                    </div>
                    <div id="landingModalCitizenGallery" class="flex items-center gap-1.5 overflow-x-auto pb-0.5 hidden"></div>
                </div>

                <div id="landingModalCompletionPhotoContainer" class="space-y-1.5">
                    <div class="aspect-[16/10] sm:h-[208px] w-full rounded-[8px] overflow-hidden bg-[#F3E6D5]/80 relative flex items-center justify-center group">
                        <img id="landingModalCompletionPhoto" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                            alt="Foto Hasil Perbaikan" onclick="openLandingImageLightbox(this.src)"
                            class="w-full h-full object-cover cursor-zoom-in transition-transform duration-500 smooth-ease group-hover:scale-[1.03]">
                        <div id="landingModalCompletionPlaceholder" class="hidden inset-0 absolute flex flex-col items-center justify-center text-center p-4 bg-[#F3E6D5]/85 backdrop-blur-sm text-[#593B42]">
                            <i data-lucide="clock" class="w-5 h-5 mb-1.5 text-[#800020]"></i>
                            <span class="text-xs font-bold text-[#2B0F16]">Menunggu Dokumentasi Perbaikan</span>
                            <span class="text-[11px] text-[#6D4E55] mt-0.5">Foto tampil setelah Pekerja Lapangan selesai</span>
                        </div>
                        <div id="landingModalCompletionTag" class="absolute bottom-3 left-3 bg-[#047857]/88 backdrop-blur-md text-white text-[11.5px] font-semibold px-3 py-1.5 rounded-[6px] pointer-events-none">
                            &#10003; Hasil Perbaikan
                        </div>
                    </div>
                    <div id="landingModalOfficerGallery" class="flex items-center gap-1.5 overflow-x-auto pb-0.5 hidden"></div>
                </div>
            </div>

            <!-- 2. Frosted Warm Ivory Info Block (Zero Perimeter Outline) -->
            <div class="bg-[#FFF9F2]/88 backdrop-blur-md rounded-[8px] p-5 sm:px-6 sm:py-5 grid grid-cols-1 sm:grid-cols-2 gap-5 sm:gap-6">
                <div class="space-y-1.5">
                    <span class="text-[12px] font-semibold text-[#6D4E55] block">
                        Lokasi Kerusakan
                    </span>
                    <p id="landingModalFullAddress" class="text-[14.5px] font-bold text-[#2B0F16] leading-snug">
                        Alamat lokasi kerusakan...
                    </p>
                    <a id="landingModalMapsLink" href="#" target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center gap-1 text-[13px] font-bold text-[#800020] hover:text-[#4A0012] transition-colors pt-1">
                        <span>Lihat Titik di Peta</span>
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>

                <div class="sm:border-l sm:border-[#E6D5C1] sm:pl-6 space-y-3">
                    <div class="space-y-0.5">
                        <span class="text-[12px] font-semibold text-[#6D4E55] block">
                            Ditangani oleh
                        </span>
                        <p id="landingModalHandledBy" class="text-[14.5px] font-bold text-[#2B0F16] leading-snug">
                            Regu Lapangan, Dinas PUPR
                        </p>
                    </div>

                    <div class="space-y-0.5">
                        <span class="text-[12px] font-semibold text-[#6D4E55] block">
                            Warga Pelapor
                        </span>
                        <p class="text-[13.5px] font-medium text-[#593B42] leading-snug">
                            <span id="landingModalReporter">Warga Terdaftar</span>
                            <span class="text-[#6D4E55]"> (<span id="landingModalReporterPhone">********</span>)</span>
                            <span id="landingModalAnonymousBadge" class="hidden ml-1.5 text-[10px] font-bold px-1.5 py-0.5 rounded-[4px] bg-[#4A0012] text-white align-middle">Anonim</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- 3. Ruas Panjang Strip -->
            <div id="landingModalMajorRoadBox" class="hidden bg-[#FFF9F2] border-l-[4px] border-l-[#800020] rounded-[8px] px-5 py-3.5 flex flex-wrap items-center justify-between gap-4 text-xs">
                <div>
                    <span class="font-semibold text-[#6D4E55] block">Kerusakan Ruas Panjang</span>
                    <span id="landingModalRoadCondition" class="font-bold text-[#2B0F16]">-</span>
                </div>
                <div class="text-right">
                    <span class="font-semibold text-[#6D4E55] block">Estimasi Panjang</span>
                    <span id="landingModalRoadLength" class="font-bold text-[#800020]">-</span>
                </div>
            </div>

            <!-- 4. Progres Penanganan Aduan (Vertical Connected Stepper Mobile + Horizontal Desktop) -->
            <div class="pt-1 space-y-3">
                <span class="text-[11px] font-bold uppercase tracking-[0.08em] text-[#6D4E55] block">
                    PROGRES PENANGANAN ADUAN
                </span>
                <div id="landingModalTimeline" class="relative">
                    <!-- Desktop Horizontal Connecting Track -->
                    <div class="hidden sm:block absolute top-[6px] left-[6px] right-[6px] h-[2.5px] bg-[#E6D5C1] rounded-full z-0" aria-hidden="true">
                        <div id="landingProgressFill" class="h-full bg-[#800020] rounded-full transition-all duration-500 smooth-ease" style="width: 0%;"></div>
                    </div>

                    <div class="flex flex-col sm:grid sm:grid-cols-4 sm:gap-x-3 relative z-10">
                        <div class="flex sm:flex-col items-stretch sm:items-start gap-3.5 sm:gap-2" data-step="1">
                            <div class="flex flex-col items-center shrink-0 sm:block">
                                <span class="step-dot w-3.5 h-3.5 rounded-full bg-[#800020] ring-[3px] ring-white transition-colors duration-300 shrink-0 mt-0.5 sm:mt-0 block"></span>
                                <span data-mobile-connector="1" class="sm:hidden w-[2.5px] flex-1 min-h-[22px] bg-[#E6D5C1] my-1 rounded-full transition-colors duration-300"></span>
                            </div>
                            <div class="flex-1 pb-3.5 sm:pb-0 flex items-baseline justify-between sm:block gap-2">
                                <span class="step-label block text-[13px] sm:text-[12.5px] font-bold text-[#2B0F16] leading-tight">Dilaporkan</span>
                                <span class="step-sub block text-[11.5px] sm:text-[11px] text-[#6D4E55] sm:mt-0.5 shrink-0">Tercatat</span>
                            </div>
                        </div>

                        <div class="flex sm:flex-col items-stretch sm:items-start gap-3.5 sm:gap-2" data-step="2">
                            <div class="flex flex-col items-center shrink-0 sm:block">
                                <span class="step-dot w-3.5 h-3.5 rounded-full bg-[#E6D5C1] ring-[3px] ring-white transition-colors duration-300 shrink-0 mt-0.5 sm:mt-0 block"></span>
                                <span data-mobile-connector="2" class="sm:hidden w-[2.5px] flex-1 min-h-[22px] bg-[#E6D5C1] my-1 rounded-full transition-colors duration-300"></span>
                            </div>
                            <div class="flex-1 pb-3.5 sm:pb-0 flex items-baseline justify-between sm:block gap-2">
                                <span class="step-label block text-[13px] sm:text-[12.5px] font-medium text-[#6D4E55] leading-tight">Diverifikasi</span>
                                <span class="step-sub block text-[11.5px] sm:text-[11px] text-[#6D4E55] sm:mt-0.5 shrink-0">Operator PUPR</span>
                            </div>
                        </div>

                        <div class="flex sm:flex-col items-stretch sm:items-start gap-3.5 sm:gap-2" data-step="3">
                            <div class="flex flex-col items-center shrink-0 sm:block">
                                <span class="step-dot w-3.5 h-3.5 rounded-full bg-[#E6D5C1] ring-[3px] ring-white transition-colors duration-300 shrink-0 mt-0.5 sm:mt-0 block"></span>
                                <span data-mobile-connector="3" class="sm:hidden w-[2.5px] flex-1 min-h-[22px] bg-[#E6D5C1] my-1 rounded-full transition-colors duration-300"></span>
                            </div>
                            <div class="flex-1 pb-3.5 sm:pb-0 flex items-baseline justify-between sm:block gap-2">
                                <span class="step-label block text-[13px] sm:text-[12.5px] font-medium text-[#6D4E55] leading-tight">Dikerjakan Regu</span>
                                <span class="step-sub block text-[11.5px] sm:text-[11px] text-[#6D4E55] sm:mt-0.5 shrink-0">Pekerja Lapangan</span>
                            </div>
                        </div>

                        <div class="flex sm:flex-col items-stretch sm:items-end sm:text-right gap-3.5 sm:gap-2" data-step="4">
                            <div class="flex flex-col items-center shrink-0 sm:block">
                                <span class="step-dot w-3.5 h-3.5 rounded-full bg-[#E6D5C1] ring-[3px] ring-white transition-colors duration-300 shrink-0 mt-0.5 sm:mt-0 block"></span>
                            </div>
                            <div class="flex-1 flex items-baseline justify-between sm:block gap-2">
                                <span class="step-label block text-[13px] sm:text-[12.5px] font-medium text-[#6D4E55] leading-tight">Selesai Validasi</span>
                                <span class="step-sub block text-[11.5px] sm:text-[11px] text-[#6D4E55] sm:mt-0.5 shrink-0">Uji Mutu</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clean Frosted Warm Ivory Footer Bar (Zero Outline) -->
        <div class="px-5 py-4 sm:px-8 bg-[#FFF9F2]/90 backdrop-blur-lg flex flex-col sm:flex-row sm:items-center justify-between gap-3 shrink-0">
            <span id="landingModalFooterNote" class="text-[12.5px] text-[#593B42]">
                Tervalidasi resmi oleh Operator Dinas PUPR Kabupaten
            </span>
            <div class="flex items-center gap-3 shrink-0">
                <a id="landingModalFooterMapsLink" href="#" target="_blank" rel="noopener noreferrer"
                    class="flex-1 sm:flex-initial sm:w-[144px] h-[44px] sm:h-[42px] rounded-[8px] bg-[#F3E6D5] hover:bg-[#E6D5C1] active:scale-[0.98] text-[#2B0F16] font-bold text-[13px] inline-flex items-center justify-center transition-colors cursor-pointer">
                    Buka di Peta
                </a>
                <button type="button" onclick="closeLandingDetailModal()"
                    class="flex-1 sm:flex-initial sm:w-[118px] h-[44px] sm:h-[42px] rounded-[8px] bg-[#800020] hover:bg-[#630018] active:scale-[0.98] text-white font-bold text-[13px] inline-flex items-center justify-center transition-colors cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#800020]">
                    Tutup
                </button>
            </div>
        </div>

    </div>
</div>

<script>
(function() {
    let closeModalTimer = null;

    function maskPhonePublic(rawPhone) {
        if (!rawPhone || rawPhone === '-' || rawPhone === 'null') return '********';
        const rawStr = rawPhone.toString().trim();
        if (rawStr.includes('*')) return rawStr;
        const clean = rawStr.replace(/[^0-9]/g, '');
        if (clean.length >= 3) {
            const suffix = clean.slice(-3);
            const maskCount = Math.max(clean.length - 3, 6);
            return '*'.repeat(maskCount) + suffix;
        }
        return '********';
    }

    function updateProgressTracker(status, shortDate) {
        const s = String(status || '').toUpperCase();
        let activeStep = 1;
        let isCompleted = false;
        let footerNote = 'Menunggu verifikasi Operator Dinas PUPR Kabupaten';

        if (s === 'DISPATCHED') {
            activeStep = 2;
            footerNote = 'Diverifikasi dan ditugaskan kepada Pekerja Lapangan Dinas PUPR';
        } else if (s === 'IN_PROGRESS' || s === 'WAITING_VERIFICATION' || s === 'REVISION_REQUIRED') {
            activeStep = 3;
            footerNote = s === 'WAITING_VERIFICATION'
                ? 'Pekerjaan fisik selesai, menunggu uji mutu Operator Dinas PUPR'
                : 'Sedang dikerjakan oleh Pekerja Lapangan Dinas PUPR Kabupaten';
        } else if (s === 'COMPLETED' || s === 'CLOSED' || s === 'RESOLVED') {
            activeStep = 4;
            isCompleted = true;
            footerNote = 'Tervalidasi resmi oleh Operator Dinas PUPR Kabupaten';
        } else if (s === 'REJECTED') {
            activeStep = 1;
            footerNote = 'Laporan tidak memenuhi kriteria penanganan Dinas PUPR';
        }

        const fillEl = document.getElementById('landingProgressFill');
        if (fillEl) {
            const widths = { 1: '0%', 2: '33.33%', 3: '66.66%', 4: '100%' };
            fillEl.style.width = widths[activeStep] || '0%';
        }

        document.querySelectorAll('#landingModalTimeline [data-step]').forEach(node => {
            const stepNum = parseInt(node.getAttribute('data-step'), 10);
            const dot = node.querySelector('.step-dot');
            const label = node.querySelector('.step-label');
            const sub = node.querySelector('.step-sub');
            const reached = stepNum <= activeStep;

            if (dot) {
                dot.classList.remove('bg-[#800020]', 'bg-[#047857]', 'bg-[#E6D5C1]');
                if (reached) {
                    dot.classList.add(isCompleted && stepNum === 4 ? 'bg-[#047857]' : 'bg-[#800020]');
                } else {
                    dot.classList.add('bg-[#E6D5C1]');
                }
            }
            if (label) {
                label.classList.remove('font-bold', 'font-medium', 'text-[#2B0F16]', 'text-[#047857]', 'text-[#6D4E55]');
                if (reached) {
                    label.classList.add('font-bold', (isCompleted && stepNum === 4) ? 'text-[#047857]' : 'text-[#2B0F16]');
                } else {
                    label.classList.add('font-medium', 'text-[#6D4E55]');
                }
            }
            if (sub && stepNum === 1 && shortDate) {
                sub.textContent = shortDate;
            }
        });

        document.querySelectorAll('#landingModalTimeline [data-mobile-connector]').forEach(line => {
            const segIdx = parseInt(line.getAttribute('data-mobile-connector'), 10);
            const filled = segIdx < activeStep;
            line.classList.remove('bg-[#800020]', 'bg-[#047857]', 'bg-[#E6D5C1]');
            if (filled) {
                line.classList.add(isCompleted && segIdx === 3 ? 'bg-[#047857]' : 'bg-[#800020]');
            } else {
                line.classList.add('bg-[#E6D5C1]');
            }
        });

        const footerNoteEl = document.getElementById('landingModalFooterNote');
        if (footerNoteEl) footerNoteEl.textContent = footerNote;
    }

    window.openLandingDetailModal = function(dataOrId) {
        if (typeof dataOrId === 'object' && dataOrId !== null) {
            renderLandingModal(dataOrId);
        } else if (typeof dataOrId === 'number' || typeof dataOrId === 'string') {
            if (window.landingComplaintsMap && window.landingComplaintsMap[dataOrId]) {
                renderLandingModal(window.landingComplaintsMap[dataOrId]);
            } else {
                fetch(`/api/v1/complaints/${encodeURIComponent(dataOrId)}`)
                    .then(res => res.json())
                    .then(res => { if (res && res.data) renderLandingModal(res.data); })
                    .catch(err => console.error('[LandingModal] Gagal memuat pengaduan:', err));
            }
        }
    };

    function renderLandingModal(item) {
        if (!item) return;
        const modal = document.getElementById('landingComplaintDetailModal');
        const card = document.getElementById('landingModalCard');
        if (!modal) return;

        if (closeModalTimer) {
            clearTimeout(closeModalTimer);
            closeModalTimer = null;
        }

        const codeEl = document.getElementById('landingModalCode');
        const titleEl = document.getElementById('landingModalTitle');
        const catEl = document.getElementById('landingModalCategoryBadge');
        const dateEl = document.getElementById('landingModalDate');
        const typeText = document.getElementById('landingModalTypeText');

        const formattedDate = item.created_at_formatted || item.created_at || '-';
        const shortDate = formattedDate.includes(',') ? formattedDate.split(',')[0] : formattedDate;

        if (codeEl) codeEl.textContent = item.public_code ? ('#' + item.public_code) : '#ADU-0000';
        if (titleEl) titleEl.textContent = item.title || 'Laporan Kerusakan';
        if (catEl) catEl.textContent = item.category_name || item.category?.name || 'Infrastruktur';
        if (dateEl) dateEl.textContent = formattedDate;

        const isMajor = item.damage_type === 'major';
        if (typeText) typeText.textContent = isMajor ? 'Kerusakan Berat' : 'Kerusakan Ringan';

        // Solid Pekat Status Badge (Subtle 6px Rounded)
        const statusBadge = document.getElementById('landingModalStatusBadge');
        const st = String(item.status || '').toUpperCase();
        let statusLabel = 'Verifikasi';
        let statusBg = 'bg-[#DC2626] text-white';

        if (st === 'REJECTED') {
            statusLabel = 'Ditolak';
            statusBg = 'bg-[#991B1B] text-white';
        } else if (st === 'COMPLETED' || st === 'CLOSED' || st === 'RESOLVED') {
            statusLabel = 'Selesai';
            statusBg = 'bg-[#047857] text-white';
        } else if (st === 'IN_PROGRESS' || st === 'WAITING_VERIFICATION' || st === 'REVISION_REQUIRED') {
            statusLabel = 'Diproses';
            statusBg = 'bg-[#B45309] text-white';
        } else if (st === 'DISPATCHED') {
            statusLabel = 'Ditugaskan';
            statusBg = 'bg-[#1E40AF] text-white';
        }

        if (statusBadge) {
            statusBadge.textContent = statusLabel;
            statusBadge.className = `shrink-0 px-3.5 py-1.5 rounded-[6px] text-[12px] font-bold leading-none ${statusBg}`;
        }

        // Before Photo
        const photoContainer = document.getElementById('landingModalPhotoContainer');
        const photoImg = document.getElementById('landingModalPhoto');
        const citizenGallery = document.getElementById('landingModalCitizenGallery');

        let citizenPhotos = [];
        if (item.photos && Array.isArray(item.photos)) {
            citizenPhotos = item.photos
                .map(p => (typeof p === 'string' ? p : (p.url || (p.storage_key ? '/storage/' + p.storage_key : ''))))
                .filter(Boolean);
        } else if (item.photo_url) {
            citizenPhotos = [item.photo_url];
        }

        if (citizenPhotos.length > 0 && photoImg && photoContainer) {
            photoImg.src = citizenPhotos[0];
            photoContainer.classList.remove('hidden');
            if (citizenGallery) {
                if (citizenPhotos.length > 1) {
                    citizenGallery.classList.remove('hidden');
                    citizenGallery.innerHTML = '';
                    citizenPhotos.forEach(url => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'w-12 h-12 rounded-[6px] overflow-hidden bg-[#F3E6D5] shrink-0 hover:ring-2 hover:ring-[#800020] transition cursor-pointer';
                        btn.addEventListener('click', () => { photoImg.src = url; });
                        const img = document.createElement('img');
                        img.src = url;
                        img.alt = 'Thumbnail kondisi kerusakan';
                        img.className = 'w-full h-full object-cover';
                        btn.appendChild(img);
                        citizenGallery.appendChild(btn);
                    });
                } else {
                    citizenGallery.classList.add('hidden');
                    citizenGallery.innerHTML = '';
                }
            }
        } else if (photoContainer) {
            photoContainer.classList.add('hidden');
        }

        // After Photo
        const compContainer = document.getElementById('landingModalCompletionPhotoContainer');
        const compImg = document.getElementById('landingModalCompletionPhoto');
        const compPlaceholder = document.getElementById('landingModalCompletionPlaceholder');
        const compTag = document.getElementById('landingModalCompletionTag');
        const officerGallery = document.getElementById('landingModalOfficerGallery');

        const officerPhotos = (Array.isArray(item.officer_photos) && item.officer_photos.length > 0)
            ? item.officer_photos
            : (item.completion_photo_url ? [item.completion_photo_url] : (item.photo_after_url ? [item.photo_after_url] : []));

        if (compContainer) compContainer.classList.remove('hidden');

        if (officerPhotos.length > 0) {
            if (compImg) {
                compImg.src = officerPhotos[0];
                compImg.classList.remove('hidden');
            }
            if (compPlaceholder) compPlaceholder.classList.add('hidden');
            if (compTag) compTag.classList.remove('hidden');

            if (officerGallery) {
                if (officerPhotos.length > 1) {
                    officerGallery.classList.remove('hidden');
                    officerGallery.innerHTML = '';
                    officerPhotos.forEach(url => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'w-12 h-12 rounded-[6px] overflow-hidden bg-[#F3E6D5] shrink-0 hover:ring-2 hover:ring-[#047857] transition cursor-pointer';
                        btn.addEventListener('click', () => { if (compImg) compImg.src = url; });
                        const img = document.createElement('img');
                        img.src = url;
                        img.alt = 'Thumbnail hasil perbaikan';
                        img.className = 'w-full h-full object-cover';
                        btn.appendChild(img);
                        officerGallery.appendChild(btn);
                    });
                } else {
                    officerGallery.classList.add('hidden');
                    officerGallery.innerHTML = '';
                }
            }
        } else {
            if (compImg) compImg.classList.add('hidden');
            if (compPlaceholder) compPlaceholder.classList.remove('hidden');
            if (compTag) compTag.classList.add('hidden');
            if (officerGallery) {
                officerGallery.classList.add('hidden');
                officerGallery.innerHTML = '';
            }
        }

        // Ditangani oleh & Warga Pelapor
        const handledByEl = document.getElementById('landingModalHandledBy');
        if (handledByEl) {
            if (item.team_name) handledByEl.textContent = `${item.team_name}, Dinas PUPR`;
            else if (item.assignee_name || item.officer_name) handledByEl.textContent = `${item.assignee_name || item.officer_name}, Dinas PUPR`;
            else handledByEl.textContent = 'Menunggu Penugasan Regu, Dinas PUPR';
        }

        const reporterEl = document.getElementById('landingModalReporter');
        const anonBadge = document.getElementById('landingModalAnonymousBadge');
        const phoneEl = document.getElementById('landingModalReporterPhone');

        if (reporterEl) reporterEl.textContent = item.reporter_name || item.citizen_name || 'Warga Terdaftar';
        if (anonBadge) anonBadge.classList.toggle('hidden', !item.is_anonymous);

        const rawPhone = item.reporter_phone || item.citizen_phone || (item.user && item.user.phone) || '';
        if (phoneEl) phoneEl.textContent = item.is_anonymous ? '********' : maskPhonePublic(rawPhone);

        // Lokasi + Maps Links
        const mapsLink = document.getElementById('landingModalMapsLink');
        const footerMapsLink = document.getElementById('landingModalFooterMapsLink');
        const addrEl = document.getElementById('landingModalFullAddress');
        const hasCoords = Boolean(item.latitude && item.longitude);
        const mapsHref = hasCoords
            ? `https://www.google.com/maps?q=${encodeURIComponent(item.latitude + ',' + item.longitude)}`
            : '#';

        [mapsLink, footerMapsLink].forEach(link => {
            if (!link) return;
            link.href = mapsHref;
            link.classList.toggle('pointer-events-none', !hasCoords);
            link.classList.toggle('opacity-50', !hasCoords);
        });
        if (addrEl) addrEl.textContent = item.address || 'Alamat tidak tercatat rinci';

        // Ruas Panjang (Major Road)
        const majorBox = document.getElementById('landingModalMajorRoadBox');
        if (majorBox) {
            if (isMajor) {
                majorBox.classList.remove('hidden');
                const condEl = document.getElementById('landingModalRoadCondition');
                const lenEl = document.getElementById('landingModalRoadLength');
                if (condEl) condEl.textContent = item.road_condition || 'Rusak Parah / Berlubang';
                if (lenEl) {
                    const cleanLen = item.road_length ? item.road_length.toString().replace(/\s*(meter|m)\s*$/i, '').trim() : '';
                    lenEl.textContent = cleanLen ? (cleanLen + ' Meter') : 'Belum diukur';
                }
            } else {
                majorBox.classList.add('hidden');
            }
        }

        // Progress Tracker
        updateProgressTracker(item.status, shortDate);

        // Smooth Modal Entrance
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        if (window.lucide) window.lucide.createIcons({ root: modal });

        requestAnimationFrame(() => {
            modal.classList.remove('opacity-0');
            modal.classList.add('opacity-100');
            if (card) {
                card.classList.remove('translate-y-5', 'sm:translate-y-3', 'sm:scale-[0.98]', 'opacity-0');
                card.classList.add('translate-y-0', 'sm:scale-100', 'opacity-100');
            }
        });
    }

    window.closeLandingDetailModal = function() {
        const modal = document.getElementById('landingComplaintDetailModal');
        const card = document.getElementById('landingModalCard');
        if (!modal || modal.classList.contains('hidden')) return;

        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0');
        if (card) {
            card.classList.remove('translate-y-0', 'sm:scale-100', 'opacity-100');
            card.classList.add('translate-y-5', 'sm:translate-y-3', 'sm:scale-[0.98]', 'opacity-0');
        }
        document.body.style.overflow = '';

        closeModalTimer = setTimeout(() => {
            modal.classList.add('hidden');
            closeModalTimer = null;
        }, 240);
    };

    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const lightbox = document.getElementById('landingImageLightbox');
            if (lightbox && !lightbox.classList.contains('hidden')) {
                lightbox.classList.add('hidden');
                return;
            }
            window.closeLandingDetailModal();
        }
    });

    document.addEventListener('click', function(e) {
        const modal = document.getElementById('landingComplaintDetailModal');
        if (modal && e.target === modal) window.closeLandingDetailModal();
    });

    window.openDetailModal = window.openLandingDetailModal;
    window.closeDetailModal = window.closeLandingDetailModal;

    window.openLandingImageLightbox = function(src) {
        if (!src || src.startsWith('data:image/gif')) return;
        let overlay = document.getElementById('landingImageLightbox');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'landingImageLightbox';
            overlay.className = 'fixed inset-0 z-[10000] bg-[#4A0012]/92 backdrop-blur-[3px] flex items-center justify-center p-4 cursor-zoom-out transition-opacity duration-200 opacity-0';
            overlay.innerHTML = '<img id="landingLightboxImg" src="" alt="Preview Foto" class="max-w-full max-h-[90vh] rounded-[10px] shadow-2xl object-contain transition-transform duration-250 smooth-ease scale-[0.97]">';
            overlay.addEventListener('click', () => {
                overlay.classList.remove('opacity-100');
                overlay.classList.add('opacity-0');
                setTimeout(() => overlay.classList.add('hidden'), 180);
            });
            document.body.appendChild(overlay);
        }
        const imgEl = document.getElementById('landingLightboxImg');
        if (imgEl) imgEl.src = src;
        overlay.classList.remove('hidden');
        requestAnimationFrame(() => {
            overlay.classList.remove('opacity-0');
            overlay.classList.add('opacity-100');
            if (imgEl) {
                imgEl.classList.remove('scale-[0.97]');
                imgEl.classList.add('scale-100');
            }
        });
    };
})();
</script>
