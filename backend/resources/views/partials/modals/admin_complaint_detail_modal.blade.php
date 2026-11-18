<!-- Modal Detail Dashboard Admin (Desain 1:1 Landing Page SI-APPIK V2 — 760px Desktop & Mobile Bottom Sheet) -->
<div id="adminComplaintDetailModal" class="fixed inset-0 z-[99999] hidden flex items-end sm:items-center justify-center p-3.5 sm:p-5 bg-slate-900/65 overscroll-contain">
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
                    <span id="admModalTypeBadge" class="shrink-0 px-2 sm:px-3 py-0.5 sm:py-1 rounded-full text-[10.5px] sm:text-xs font-bold bg-[#FEF2F2] text-[#DC2626] border border-[#FECACA] inline-flex items-center gap-1 whitespace-nowrap">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#DC2626] shrink-0"></span>
                        <span>Kerusakan Ringan</span>
                    </span>
                    <span id="admModalCategoryBadge" class="shrink min-w-0 truncate px-2 sm:px-3 py-0.5 sm:py-1 rounded-full bg-[#F1F5F9] text-[#475569] font-semibold text-[10.5px] sm:text-xs border border-[#E2E8F0]">
                        Kategori
                    </span>
                    <span id="admModalStatusBadge" class="shrink-0 px-2 sm:px-3 py-0.5 sm:py-1 rounded-full text-[10.5px] sm:text-xs font-bold whitespace-nowrap border bg-[#EFF6FF] text-[#2563EB] border-[#BFDBFE]">
                        STATUS
                    </span>
                </div>

                <!-- Baris 2: Tanggal Aduan + Kode Aduan -->
                <div class="flex items-center flex-wrap gap-2 text-[11.5px] sm:text-[13px] text-[#475569]">
                    <div class="flex items-center gap-1.5 font-medium">
                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-[#64748B] shrink-0"></i>
                        <span id="admModalDate">03 Sep 2026</span>
                    </div>
                    <span class="text-slate-300">&bull;</span>
                    <span id="admModalCode" class="font-mono font-bold text-[#2563EB] bg-[#EFF6FF] border border-[#BFDBFE] px-2 py-0.5 rounded-full text-[10.5px] sm:text-[11px]">#ADU-0000</span>
                </div>
            </div>

            <button type="button" onclick="closeAdminDetailModal()" class="w-8 h-8 sm:w-9 sm:h-9 rounded-[10px] bg-[#F8FAFC] hover:bg-slate-100 text-[#475569] hover:text-[#0F172A] border border-[#E2E8F0] flex items-center justify-center transition cursor-pointer shrink-0" title="Tutup Detail" aria-label="Tutup Rincian">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Modal Scrollable Body -->
        <div class="px-5 py-4 sm:px-7 sm:py-5 overflow-y-auto space-y-4 flex-1 overscroll-contain bg-white" style="-webkit-overflow-scrolling: touch;">

            <!-- Judul Aduan -->
            <h3 id="admModalTitle" class="font-outfit font-bold text-lg sm:text-xl text-[#0F172A] leading-snug">
                Judul Laporan
            </h3>

            <!-- 1. Galeri Foto Berdampingan 2 Kolom (Rasio 16:10 Side-by-Side) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3.5">
                <!-- Kiri: Foto Kondisi Lokasi -->
                <div id="admModalPhotoContainer" class="space-y-1.5">
                    <div class="aspect-[16/10] rounded-xl sm:rounded-2xl overflow-hidden border border-[#E2E8F0] bg-[#1E293B] relative flex items-center justify-center">
                        <img id="admModalPhoto" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="Foto Kondisi Lokasi" class="w-full h-full object-cover">
                        <div class="absolute bottom-2 left-2 sm:bottom-3 sm:left-3 bg-[#0F172A]/85 text-white text-[10px] sm:text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1.5 pointer-events-none">
                            <i data-lucide="camera" class="w-3 h-3 shrink-0 hidden sm:inline"></i>
                            <span id="admModalPhotoLabel">Foto Kondisi Lokasi</span>
                        </div>
                    </div>
                    <div id="admModalPhotoGallery" class="flex items-center gap-1.5 overflow-x-auto pb-0.5 hidden"></div>
                </div>

                <!-- Kanan: Foto Hasil Pengerjaan (Selalu tampil berdampingan, placeholder bila belum ada) -->
                <div id="admModalCompletionPhotoContainer" class="space-y-1.5">
                    <div class="aspect-[16/10] rounded-xl sm:rounded-2xl overflow-hidden border border-[#A7F3D0] bg-[#ECFDF5] relative flex items-center justify-center">
                        <img id="admModalCompletionPhoto" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="Foto Hasil Pengerjaan" class="w-full h-full object-cover">
                        <div id="admModalCompletionPlaceholder" class="hidden inset-0 absolute flex flex-col items-center justify-center text-center p-3 text-[#047857] bg-[#ECFDF5]">
                            <i data-lucide="clock" class="w-5 h-5 mb-1 text-[#10B981]"></i>
                            <span class="text-[11px] sm:text-xs font-semibold leading-tight">Menunggu Foto Hasil Pengerjaan</span>
                        </div>
                        <div class="absolute bottom-2 left-2 sm:bottom-3 sm:left-3 bg-[#047857]/90 text-white text-[10px] sm:text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1.5 pointer-events-none">
                            <i data-lucide="badge-check" class="w-3 h-3 shrink-0 hidden sm:inline"></i>
                            <span id="admModalCompletionPhotoLabel">Foto Hasil Pengerjaan</span>
                        </div>
                    </div>
                    <div id="admModalOfficerGallery" class="flex items-center gap-1.5 overflow-x-auto pb-0.5 hidden"></div>
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
                        <span id="admModalCitizenName" class="font-outfit font-bold text-sm sm:text-base text-[#0F172A] truncate">Nama Pelapor</span>
                        <span id="admModalAnonymousBadge" class="hidden text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-200 text-slate-700">Anonim</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs font-bold text-[#047857]">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#10B981] shrink-0"></i>
                        <a id="admModalCitizenPhone" href="#" class="font-mono hover:underline text-[#047857]" title="Hubungi / Kirim WhatsApp">-</a>
                    </div>
                </div>

                <!-- Kartu Kanan: Lokasi Kerusakan -->
                <div class="p-4 rounded-2xl bg-[#F8FAFC] border border-[#E2E8F0] space-y-2 flex flex-col justify-between">
                    <div class="space-y-1">
                        <div class="flex items-center gap-1.5 text-[11px] font-bold text-[#475569] uppercase tracking-wider">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#2563EB]"></i>
                            <span>Lokasi Kerusakan</span>
                        </div>
                        <p id="admModalAddress" class="text-xs sm:text-[13px] font-semibold text-[#0F172A] leading-relaxed">
                            Alamat lokasi kerusakan...
                        </p>
                        <span id="admModalRoad" class="hidden"></span>
                        <span id="admModalVillage" class="hidden"></span>
                        <span id="admModalDistrict" class="hidden"></span>
                        <span id="admModalRegency" class="hidden"></span>
                    </div>
                    <div class="flex items-center justify-between gap-2 pt-0.5">
                        <span id="admModalCoords" class="hidden">-</span>
                        <button type="button" onclick="const c = (document.getElementById('admModalCoords')?.textContent || '').trim(); if(c && c !== '-') window.open('https://www.google.com/maps?q=' + encodeURIComponent(c.replace(/\s+/g,'')), '_blank', 'noopener');"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#EFF6FF] hover:bg-[#DBEAFE] text-[#2563EB] border border-[#BFDBFE] text-xs font-bold transition cursor-pointer">
                            <i data-lucide="map" class="w-3.5 h-3.5"></i>
                            <span>Buka di Peta</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 3. Kartu Regu Lapangan Pelaksana -->
            <div id="admModalOfficerCard" class="hidden p-3.5 sm:p-4 rounded-2xl bg-[#EFF6FF]/60 border border-[#BFDBFE] space-y-2.5">
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2.5">
                    <div class="flex items-start gap-2.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-[#2563EB] text-white flex items-center justify-center font-bold text-xs shrink-0">
                            <i data-lucide="hard-hat" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] text-[#2563EB] font-extrabold uppercase tracking-wider block">Regu Lapangan Pelaksana</span>
                            <h4 id="admModalTeamName" class="font-outfit font-bold text-xs sm:text-sm text-[#0F172A] break-words leading-snug">
                                Regu Aspal 01 - Lasusua
                            </h4>
                            <span id="admModalOfficerDept" class="text-[11px] font-medium text-[#475569] block break-words mt-0.5">
                                Dinas Pekerjaan Umum dan Penataan Ruang (PUPR)
                            </span>
                        </div>
                    </div>
                    <div id="admModalDeadlineBox" class="flex sm:block items-center justify-between sm:text-right gap-2 pt-2 sm:pt-0 border-t sm:border-t-0 border-[#BFDBFE]/50 shrink-0">
                        <span class="text-[10px] text-[#64748B] font-medium sm:block">Batas Waktu:</span>
                        <span id="admModalOfficerDeadline" class="text-xs font-bold text-[#2563EB] bg-white px-2.5 py-0.5 rounded-lg border border-[#BFDBFE] inline-block sm:mt-0.5">-</span>
                    </div>
                </div>

                <div class="p-2.5 rounded-xl bg-white border border-[#E2E8F0] flex flex-wrap items-center justify-between gap-2 text-xs">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-6 h-6 rounded-lg bg-[#EFF6FF] text-[#2563EB] flex items-center justify-center shrink-0">
                            <i data-lucide="crown" class="w-3.5 h-3.5"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] text-[#64748B] font-bold block leading-none">Ketua Regu:</span>
                            <span id="admModalTeamLeader" class="font-bold text-[#0F172A] break-words block mt-0.5">Nama Ketua Regu</span>
                        </div>
                    </div>
                    <a id="admModalTeamPhone" href="#" class="px-2.5 py-1 rounded-lg bg-[#EFF6FF] hover:bg-[#DBEAFE] text-[#2563EB] font-bold font-mono text-xs flex items-center gap-1.5 transition shrink-0" title="Hubungi Petugas">
                        <i data-lucide="phone-call" class="w-3 h-3"></i>
                        <span id="admModalTeamPhoneText">-</span>
                    </a>
                </div>

                <span id="admModalOfficerName" class="hidden"></span>
                <span id="admModalOfficerPhone" class="hidden"></span>

                <div id="admModalInstructionRow" class="hidden text-xs text-[#475569] flex items-start gap-1.5 pt-1.5 border-t border-[#BFDBFE]/60">
                    <i data-lucide="clipboard-list" class="w-3.5 h-3.5 text-[#2563EB] shrink-0 mt-0.5"></i>
                    <span>Instruksi: <span id="admModalOfficerInstruction" class="text-[#0F172A] font-medium italic">Lakukan perbaikan</span></span>
                </div>
            </div>

            <!-- 4. Detail Kerusakan Ruas Panjang (Tipe Major) -->
            <div id="admModalMajorRoadBox" class="hidden p-4 rounded-2xl bg-[#EFF6FF]/60 border border-[#BFDBFE] space-y-2">
                <div class="flex items-center justify-between text-xs font-bold text-[#2563EB]">
                    <span class="flex items-center gap-1.5">
                        <i data-lucide="route" class="w-4 h-4 text-[#2563EB]"></i>
                        <span>Detail Kerusakan Ruas Jalan</span>
                    </span>
                    <span id="admModalRoadLengthBadge" class="text-[10px] text-white bg-[#2563EB] px-2.5 py-0.5 rounded-full font-bold">
                        ≥ 500 Meter
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="p-2.5 rounded-xl bg-white border border-[#E2E8F0]">
                        <span class="text-[10px] text-[#64748B] block font-semibold">Estimasi Panjang Ruas:</span>
                        <span id="admModalRoadLength" class="font-bold text-[#0F172A]">-</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-white border border-[#E2E8F0]">
                        <span class="text-[10px] text-[#64748B] block font-semibold">Kondisi Permukaan:</span>
                        <span id="admModalRoadCondition" class="font-bold text-[#0F172A]">-</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-white border border-[#E2E8F0] col-span-2">
                        <span class="text-[10px] text-[#64748B] block font-semibold">Koordinat Titik Awal &amp; Akhir:</span>
                        <div class="font-mono text-[11px] text-[#334155] flex flex-col gap-0.5 mt-1">
                            <div><span class="font-bold text-[#2563EB]">Titik Awal:</span> <span id="admModalStartCoords">-</span></div>
                            <div><span class="font-bold text-[#0F172A]">Titik Akhir:</span> <span id="admModalEndCoords">-</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. Deskripsi Lengkap -->
            <div class="space-y-1.5">
                <h4 class="text-[11px] font-bold text-[#475569] uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="file-text" class="w-3.5 h-3.5 text-[#64748B]"></i>
                    <span>Deskripsi Kondisi Kerusakan</span>
                </h4>
                <div id="admModalDescription" class="p-4 rounded-2xl bg-[#F8FAFC] border border-[#E2E8F0] text-xs sm:text-[13px] text-[#334155] leading-relaxed whitespace-pre-line font-sans">
                    Deskripsi lengkap...
                </div>
            </div>

        </div>

        <!-- Modal Footer -->
        <div class="px-5 py-3.5 sm:px-7 border-t border-[#E2E8F0] bg-white flex items-center justify-end gap-2 shrink-0">
            <button type="button" onclick="closeAdminDetailModal()" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs shadow-xs transition cursor-pointer">
                Tutup Rincian
            </button>
        </div>

    </div>
</div>
