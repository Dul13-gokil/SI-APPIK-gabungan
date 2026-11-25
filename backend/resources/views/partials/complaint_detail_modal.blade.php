<!-- Modal Detail Pengaduan Terpadu (Desain 1:1 Landing Page SI-APPIK V2 — 760px Desktop & Mobile Bottom Sheet) -->
<div id="complaintDetailModal" class="fixed inset-0 z-[9999] hidden flex items-end sm:items-center justify-center p-3.5 sm:p-5 bg-slate-900/65 overscroll-contain">
    <div class="bg-white w-full sm:max-w-[760px] rounded-[24px] shadow-[0_20px_50px_rgba(15,23,42,0.22)] border border-[#E2E8F0] overflow-hidden flex flex-col max-h-[86vh] sm:max-h-[90vh]">

        <!-- Drag Handle Khusus Mobile Bottom Sheet -->
        <div class="sm:hidden flex justify-center pt-3 pb-1 bg-white shrink-0">
            <span class="w-11 h-1 rounded-full bg-[#CBD5E1]"></span>
        </div>

        <!-- Sticky Modal Top Header (3 Badge 1 Baris + Tanggal & Kode Aduan + Tombol X di Tengah Kanan) -->
        <div class="px-4 sm:px-7 pt-2.5 pb-3.5 sm:py-5 border-b border-[#E2E8F0] bg-white flex items-center justify-between gap-2.5 shrink-0">
            <div class="min-w-0 flex-1 space-y-2">
                <!-- Baris 1: Selalu 1 Baris di Mobile & Desktop -->
                <div class="flex items-center gap-1 sm:gap-2 flex-nowrap overflow-x-auto no-scrollbar">
                    <span id="sharedModalStatusBadge" class="shrink-0 px-2 sm:px-3 py-0.5 sm:py-1 rounded-full text-[10.5px] sm:text-xs font-bold whitespace-nowrap border bg-[#ECFDF5] text-[#047857] border-[#A7F3D0]">
                        STATUS
                    </span>
                    <span id="sharedModalTypeBadge" class="shrink-0 px-2 sm:px-3 py-0.5 sm:py-1 rounded-full text-[10.5px] sm:text-xs font-bold bg-[#FEF2F2] text-[#DC2626] border border-[#FECACA] inline-flex items-center gap-1 whitespace-nowrap">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#DC2626] shrink-0"></span>
                        <span>Kerusakan Ringan</span>
                    </span>
                    <span id="sharedModalCategoryBadge" class="shrink min-w-0 truncate px-2 sm:px-3 py-0.5 sm:py-1 rounded-full bg-[#F1F5F9] text-[#475569] font-semibold text-[10.5px] sm:text-xs border border-[#E2E8F0]">
                        Infrastruktur
                    </span>
                </div>

                <!-- Baris 2: Tanggal Aduan + Kode Aduan -->
                <div class="flex items-center flex-wrap gap-2 text-[11.5px] sm:text-[13px] text-[#475569]">
                    <div class="flex items-center gap-1.5 font-medium">
                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-[#64748B] shrink-0"></i>
                        <span id="sharedModalDate">01 Jan 2026, 00:00 WITA</span>
                    </div>
                    <span class="text-slate-300">&bull;</span>
                    <span id="sharedModalCode" class="font-mono font-bold text-[#2563EB] bg-[#EFF6FF] border border-[#BFDBFE] px-2 py-0.5 rounded-full text-[10.5px] sm:text-[11px]">#ADU-0000</span>
                </div>
            </div>

            <button type="button" onclick="closeSharedDetailModal()" class="w-8 h-8 sm:w-9 sm:h-9 rounded-[10px] bg-[#F8FAFC] hover:bg-slate-100 text-[#475569] hover:text-[#0F172A] border border-[#E2E8F0] flex items-center justify-center transition cursor-pointer shrink-0" title="Tutup Detail" aria-label="Tutup Rincian">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Modal Scrollable Body -->
        <div class="px-5 py-4 sm:px-7 sm:py-5 overflow-y-auto space-y-4 flex-1 overscroll-contain bg-white" style="-webkit-overflow-scrolling: touch;">

            <!-- Judul Aduan -->
            <h3 id="sharedModalTitle" class="font-outfit font-bold text-lg sm:text-xl text-[#0F172A] leading-snug">
                Judul Pengaduan
            </h3>

            <!-- 1. Galeri Foto Berdampingan 2 Kolom (Rasio 16:10 Side-by-Side) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3.5">
                <!-- Kiri: Foto Bukti Kerusakan Awal -->
                <div id="sharedModalPhotoContainer" class="space-y-1.5">
                    <div class="aspect-[16/10] rounded-xl sm:rounded-2xl overflow-hidden border border-[#E2E8F0] bg-[#1E293B] relative flex items-center justify-center">
                        <img id="sharedModalPhoto" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="Foto Kerusakan" class="w-full h-full object-cover">
                        <div class="absolute bottom-2 left-2 sm:bottom-3 sm:left-3 bg-[#0F172A]/85 text-white text-[10px] sm:text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1.5 pointer-events-none">
                            <i data-lucide="camera" class="w-3 h-3 shrink-0 hidden sm:inline"></i>
                            <span id="sharedModalPhotoCountLabel">Foto Kondisi Lokasi</span>
                        </div>
                    </div>
                    <div id="sharedModalCitizenGallery" class="flex items-center gap-1.5 overflow-x-auto pb-0.5 hidden"></div>
                </div>

                <!-- Kanan: Foto Hasil Pengerjaan Fisik (Selalu tampil berdampingan, placeholder bila belum ada) -->
                <div id="sharedModalCompletionPhotoContainer" class="space-y-1.5">
                    <div id="sharedModalCompletionBox" class="aspect-[16/10] rounded-xl sm:rounded-2xl overflow-hidden border border-[#A7F3D0] bg-[#ECFDF5] relative flex items-center justify-center">
                        <img id="sharedModalCompletionPhoto" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="Foto Hasil Pengerjaan" class="w-full h-full object-cover">
                        <div id="sharedModalCompletionPlaceholder" class="hidden inset-0 absolute flex flex-col items-center justify-center text-center p-3 text-[#047857] bg-[#ECFDF5]">
                            <i id="sharedModalCompletionIcon" data-lucide="clock" class="w-5 h-5 mb-1 text-[#10B981]"></i>
                            <span id="sharedModalCompletionText" class="text-[11px] sm:text-xs font-semibold leading-tight">Menunggu Foto Hasil Pengerjaan</span>
                        </div>
                        <div id="sharedModalCompletionPill" class="absolute bottom-2 left-2 sm:bottom-3 sm:left-3 bg-[#047857]/90 text-white text-[10px] sm:text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1.5 pointer-events-none">
                            <i data-lucide="badge-check" class="w-3 h-3 shrink-0 hidden sm:inline"></i>
                            <span id="sharedModalCompletionPhotoLabel">Foto Hasil Pengerjaan</span>
                        </div>
                    </div>
                    <div id="sharedModalOfficerGallery" class="flex items-center gap-1.5 overflow-x-auto pb-0.5 hidden"></div>
                </div>
            </div>

            <!-- 2. Grid Informasi Pelapor & Lokasi (2 Kolom Seimbang) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-3.5">
                <!-- Kartu Kiri: Pelapor Aduan -->
                <div class="p-4 rounded-2xl bg-[#F8FAFC] border border-[#E2E8F0] space-y-1.5">
                    <div class="flex items-center gap-1.5 text-[11px] font-bold text-[#475569] uppercase tracking-wider">
                        <i data-lucide="user" class="w-3.5 h-3.5 text-[#2563EB]"></i>
                        <span>Pelapor Aduan</span>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span id="sharedModalReporter" class="font-outfit font-bold text-sm sm:text-base text-[#0F172A] leading-tight truncate">
                            Warga Terdaftar
                        </span>
                        <span id="sharedModalAnonymousBadge" class="hidden text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-200 text-slate-700">Anonim</span>
                    </div>
                    <div id="sharedModalReporterPhoneWrap" class="pt-0.5">
                        <a id="sharedModalReporterPhoneLink" href="#" class="hidden items-center gap-1.5 text-xs text-[#047857] font-bold font-mono hover:underline shrink-0">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#10B981]"></i>
                            <span id="sharedModalReporterPhoneText">-</span>
                        </a>
                    </div>
                </div>

                <!-- Kartu Kanan: Lokasi Kerusakan -->
                <div class="p-4 rounded-2xl bg-[#F8FAFC] border border-[#E2E8F0] space-y-2 flex flex-col justify-between">
                    <div class="space-y-1">
                        <div class="flex items-center gap-1.5 text-[11px] font-bold text-[#475569] uppercase tracking-wider">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#2563EB]"></i>
                            <span>Lokasi Kerusakan</span>
                        </div>
                        <p id="sharedModalFullAddress" class="text-xs sm:text-[13px] font-semibold text-[#0F172A] leading-relaxed">
                            Alamat lokasi kerusakan...
                        </p>
                    </div>
                    <div class="flex items-center justify-between gap-2 pt-0.5">
                        <span id="sharedModalCoords" class="hidden">-</span>
                        <button type="button" onclick="const c = (document.getElementById('sharedModalCoords')?.textContent || '').trim(); if(c && c !== '-') window.open('https://www.google.com/maps?q=' + encodeURIComponent(c.replace(/\s+/g,'')), '_blank', 'noopener');"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#EFF6FF] hover:bg-[#DBEAFE] text-[#2563EB] border border-[#BFDBFE] text-xs font-bold transition cursor-pointer">
                            <i data-lucide="map" class="w-3.5 h-3.5"></i>
                            <span>Buka di Peta</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 3. Kartu Regu Lapangan Pelaksana -->
            <div id="sharedModalOfficerCard" class="hidden p-3.5 sm:p-4 rounded-2xl bg-[#EFF6FF]/60 border border-[#BFDBFE] space-y-2.5">
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2.5">
                    <div class="flex items-start gap-2.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-[#2563EB] text-white flex items-center justify-center font-bold text-xs shrink-0">
                            <i data-lucide="hard-hat" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] text-[#2563EB] font-extrabold uppercase tracking-wider block">Regu Lapangan Pelaksana</span>
                            <h4 id="sharedModalOfficerName" class="font-outfit font-bold text-xs sm:text-sm text-[#0F172A] break-words leading-snug">
                                Regu Aspal 01 - Lasusua
                            </h4>
                            <span id="sharedModalOfficerDept" class="text-[11px] font-medium text-[#475569] block break-words mt-0.5">
                                Dinas Pekerjaan Umum dan Penataan Ruang (PUPR)
                            </span>
                        </div>
                    </div>
                    <div id="sharedModalDeadlineBox" class="flex sm:block items-center justify-between sm:text-right gap-2 pt-2 sm:pt-0 border-t sm:border-t-0 border-[#BFDBFE]/50 shrink-0">
                        <span class="text-[10px] text-[#64748B] font-medium sm:block">Batas Waktu:</span>
                        <span id="sharedModalOfficerDeadline" class="text-xs font-bold text-[#2563EB] bg-white px-2.5 py-0.5 rounded-lg border border-[#BFDBFE] inline-block sm:mt-0.5">
                            -
                        </span>
                    </div>
                </div>

                <div class="p-2.5 rounded-xl bg-white border border-[#E2E8F0] flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-6 h-6 rounded-lg bg-[#EFF6FF] text-[#2563EB] flex items-center justify-center shrink-0">
                            <i data-lucide="crown" class="w-3.5 h-3.5"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] text-[#64748B] font-bold block leading-none">Ketua Regu:</span>
                            <span id="sharedModalTeamLeader" class="font-bold text-[#0F172A] break-words block mt-0.5 text-xs">Pengawas Lapangan</span>
                        </div>
                    </div>
                    <a id="sharedModalOfficerPhone" href="#" class="px-2.5 py-1 rounded-lg bg-[#EFF6FF] hover:bg-[#DBEAFE] text-[#2563EB] font-bold font-mono text-xs flex items-center gap-1.5 transition shrink-0">
                        <i data-lucide="phone-call" class="w-3 h-3"></i>
                        <span id="sharedModalOfficerPhoneText">081100000000</span>
                    </a>
                </div>

                <div id="sharedModalInstructionRow" class="pt-1.5 text-xs text-[#475569] flex items-start gap-1.5 border-t border-[#BFDBFE]/60">
                    <i data-lucide="clipboard-list" class="w-3.5 h-3.5 text-[#2563EB] shrink-0 mt-0.5"></i>
                    <span>Instruksi: <em id="sharedModalOfficerInstruction" class="text-[#0F172A] font-medium">Lakukan penanganan fisik sesuai SOP dinas terkait</em></span>
                </div>
            </div>

            <!-- 4. Spesifikasi Ruas Jalan Rusak Panjang -->
            <div id="sharedModalMajorRoadBox" class="hidden rounded-2xl p-4 bg-[#EFF6FF]/60 border border-[#BFDBFE] text-[#2563EB] space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="route" class="w-4 h-4 text-[#2563EB]"></i>
                        <span class="text-xs font-bold uppercase tracking-wider">Kerusakan Ruas Panjang</span>
                    </div>
                    <span id="sharedModalRoadLengthBadge" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#2563EB] text-white">Major</span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs pt-1">
                    <div class="p-2.5 rounded-xl bg-white border border-[#E2E8F0]">
                        <span class="text-[10px] text-[#64748B] block">Kondisi Kerusakan:</span>
                        <span id="sharedModalRoadCondition" class="font-bold text-[#0F172A]">-</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-white border border-[#E2E8F0]">
                        <span class="text-[10px] text-[#64748B] block">Estimasi Panjang:</span>
                        <span id="sharedModalRoadLength" class="font-bold text-[#0F172A]">-</span>
                    </div>
                </div>
            </div>

            <!-- 5. Deskripsi Lengkap Kerusakan -->
            <div class="space-y-1.5">
                <h4 class="text-[11px] font-bold text-[#475569] uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="file-text" class="w-3.5 h-3.5 text-[#64748B]"></i>
                    <span>Deskripsi Kondisi Kerusakan</span>
                </h4>
                <div id="sharedModalDescription" class="p-4 rounded-2xl bg-[#F8FAFC] border border-[#E2E8F0] text-xs sm:text-[13px] text-[#334155] leading-relaxed whitespace-pre-line font-sans">
                    Kondisi kerusakan infrastruktur...
                </div>
            </div>

        </div>

        <!-- Modal Footer Actions -->
        <div class="px-5 py-3.5 sm:px-7 border-t border-[#E2E8F0] bg-white flex items-center justify-end gap-2 flex-wrap shrink-0">
            @auth
                <a id="sharedModalBapBtn" href="#" target="_blank" class="hidden px-4 py-2.5 rounded-xl border border-[#BFDBFE] bg-[#EFF6FF] hover:bg-[#DBEAFE] text-[#2563EB] font-bold text-xs transition items-center gap-1.5">
                    <i data-lucide="file-check-2" class="w-3.5 h-3.5"></i>
                    <span>Cetak BAP</span>
                </a>
            @endauth

            @if(($role ?? 'public') === 'petugas')
                <a id="sharedModalNavBtn" href="#" target="_blank" class="px-4 py-2.5 rounded-xl border border-[#E2E8F0] bg-white hover:bg-[#F8FAFC] text-[#334155] font-bold text-xs transition flex items-center gap-1.5">
                    <i data-lucide="navigation" class="w-3.5 h-3.5 text-[#2563EB]"></i>
                    <span>Rute Google Maps</span>
                </a>
            @endif

            <button type="button" onclick="closeSharedDetailModal()" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                <span>Tutup Rincian</span>
            </button>
        </div>

    </div>
</div>
