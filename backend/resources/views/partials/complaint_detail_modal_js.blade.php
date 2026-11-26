<script>
/**
 * Logika JavaScript Pop-Up Detail Terpadu (Shared Component)
 * Menangani rendering detail untuk Public, Operator, Bupati, Petugas, & Super Admin.
 */
(function() {
    window.openSharedDetailModal = function(dataOrId) {
        let item = null;

        if (typeof dataOrId === 'object' && dataOrId !== null) {
            item = dataOrId;
        } else if (typeof dataOrId === 'number' || typeof dataOrId === 'string') {
            const id = parseInt(dataOrId, 10);
            if (window.bupatiComplaintsMap && window.bupatiComplaintsMap[id]) {
                item = window.bupatiComplaintsMap[id];
            } else if (window.petugasComplaintsMap && window.petugasComplaintsMap[id]) {
                item = window.petugasComplaintsMap[id];
            } else if (window.operatorComplaintsMap && window.operatorComplaintsMap[id]) {
                item = window.operatorComplaintsMap[id];
            } else if (window.allComplaintsMap && window.allComplaintsMap[id]) {
                item = window.allComplaintsMap[id];
            } else if (window.complaintList && Array.isArray(window.complaintList)) {
                item = window.complaintList.find(c => c.id === id);
            }
        }

        if (!item) {
            console.warn('[SharedDetailModal] Data pengaduan tidak ditemukan untuk:', dataOrId);
            return;
        }

        const modal = document.getElementById('complaintDetailModal');
        if (!modal) return;

        // 1. Kode Tiket & Judul
        const codeEl = document.getElementById('sharedModalCode');
        if (codeEl) codeEl.innerText = item.public_code ? (item.public_code.startsWith('#') ? item.public_code : '#' + item.public_code) : '#ADU';

        const titleEl = document.getElementById('sharedModalTitle');
        if (titleEl) titleEl.innerText = item.title || 'Laporan Kerusakan Infrastruktur';

        // 2. Waktu Laporan
        const dateEl = document.getElementById('sharedModalDate');
        if (dateEl) {
            dateEl.innerText = item.created_at_full || item.created_at_formatted || (item.time_ago ? `${item.created_at} (${item.time_ago})` : item.created_at) || '-';
        }

        // 3. Status Badge (Harmonisasi Warna Standar Landing Page)
        const statusBadge = document.getElementById('sharedModalStatusBadge');
        if (statusBadge) {
            const s = (item.status || '').toUpperCase();
            if (s === 'COMPLETED' || s === 'CLOSED') {
                statusBadge.className = 'shrink-0 px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 whitespace-nowrap';
                statusBadge.innerText = 'SELESAI';
            } else if (s === 'WAITING_VERIFICATION') {
                statusBadge.className = 'shrink-0 px-2.5 py-1 rounded-md text-xs font-bold bg-amber-50 text-amber-700 border border-amber-300 whitespace-nowrap';
                statusBadge.innerText = 'DIPROSES';
            } else if (s === 'IN_PROGRESS') {
                statusBadge.className = 'shrink-0 px-2.5 py-1 rounded-md text-xs font-bold bg-amber-50 text-amber-800 border border-amber-300 whitespace-nowrap';
                statusBadge.innerText = 'DIKERJAKAN';
            } else if (s === 'REVISION_REQUIRED') {
                statusBadge.className = 'shrink-0 px-2.5 py-1 rounded-md text-xs font-bold bg-amber-50 text-amber-800 border border-amber-300 whitespace-nowrap';
                statusBadge.innerText = 'PERLU REVISI';
            } else if (s === 'DISPATCHED') {
                statusBadge.className = 'shrink-0 px-2.5 py-1 rounded-md text-xs font-bold bg-blue-50 text-[#2563EB] border border-blue-200 whitespace-nowrap';
                statusBadge.innerText = 'DITUGASKAN';
            } else if (s === 'REJECTED') {
                statusBadge.className = 'shrink-0 px-2.5 py-1 rounded-md text-xs font-bold bg-red-50 text-red-700 border border-red-200 whitespace-nowrap';
                statusBadge.innerText = 'DITOLAK';
            } else if (s === 'CANCELLED') {
                statusBadge.className = 'shrink-0 px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200 whitespace-nowrap';
                statusBadge.innerText = 'DIBATALKAN';
            } else {
                statusBadge.className = 'shrink-0 px-2.5 py-1 rounded-md text-xs font-bold bg-red-50 text-red-700 border border-red-200 whitespace-nowrap';
                statusBadge.innerText = 'VERIFIKASI';
            }
        }

        // Tombol Cetak BAP (khusus laporan COMPLETED / CLOSED)
        const bapBtn = document.getElementById('sharedModalBapBtn');
        if (bapBtn) {
            const st = String(item.status || '').toUpperCase();
            if ((st === 'COMPLETED' || st === 'CLOSED') && item.id) {
                bapBtn.href = `/complaints/${item.id}/bap`;
                bapBtn.classList.remove('hidden');
                bapBtn.classList.add('inline-flex');
            } else {
                bapBtn.classList.add('hidden');
                bapBtn.classList.remove('inline-flex');
            }
        }

        // 4. Tingkat Kerusakan Badge
        const typeBadge = document.getElementById('sharedModalTypeBadge');
        if (typeBadge) {
            const isMajor = item.damage_type === 'major';
            typeBadge.className = 'shrink-0 px-2.5 py-1 rounded-md text-xs font-semibold bg-red-50 text-red-700 border border-red-200 inline-flex items-center gap-1.5 whitespace-nowrap';
            typeBadge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-red-600 shrink-0"></span><span>${isMajor ? 'Kerusakan Berat' : 'Kerusakan Ringan'}</span>`;
        }

        // 5. Kategori Infrastruktur
        const catBadge = document.getElementById('sharedModalCategoryBadge');
        if (catBadge) {
            catBadge.innerText = item.category_name || item.ai_category || 'Jalan & Jembatan';
        }

        // 6. Foto Bukti Kerusakan Awal (Warga) & Galeri Thumbnail
        const photoContainer = document.getElementById('sharedModalPhotoContainer');
        const photoImg = document.getElementById('sharedModalPhoto');
        const citizenGallery = document.getElementById('sharedModalCitizenGallery');
        const photoLabel = document.getElementById('sharedModalPhotoCountLabel');

        const citizenPhotos = (item.citizen_photos && item.citizen_photos.length > 0)
            ? item.citizen_photos
            : (item.photo_url ? [item.photo_url] : (item.photos && item.photos.length > 0 ? item.photos.map(p => p.url) : []));

        if (citizenPhotos.length > 0) {
            photoImg.src = citizenPhotos[0];
            photoContainer.classList.remove('hidden');
            if (photoLabel) photoLabel.innerText = citizenPhotos.length > 1 ? `Foto Bukti 1 dari ${citizenPhotos.length}` : 'Foto Bukti Kerusakan';

            if (citizenGallery) {
                if (citizenPhotos.length > 1) {
                    citizenGallery.classList.remove('hidden');
                    citizenGallery.innerHTML = citizenPhotos.map((url, idx) => `
                        <button type="button" onclick="document.getElementById('sharedModalPhoto').src='${url}'; document.getElementById('sharedModalPhotoCountLabel').innerText='Foto Bukti ${idx + 1} dari ${citizenPhotos.length}';" 
                            class="w-12 h-12 rounded-xl overflow-hidden border border-slate-200 bg-slate-100 shrink-0 hover:ring-2 hover:ring-[#2563EB] transition cursor-pointer">
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
            if (citizenGallery) citizenGallery.classList.add('hidden');
        }

        // 7. Foto Bukti Hasil Pengerjaan Fisik (Selalu tampil berdampingan)
        const compContainer = document.getElementById('sharedModalCompletionPhotoContainer');
        const compImg = document.getElementById('sharedModalCompletionPhoto');
        const compPlaceholder = document.getElementById('sharedModalCompletionPlaceholder');
        const officerGallery = document.getElementById('sharedModalOfficerGallery');

        const officerPhotos = (item.officer_photos && item.officer_photos.length > 0)
            ? item.officer_photos
            : (item.completion_photo_url ? [item.completion_photo_url] : []);

        const compBox = document.getElementById('sharedModalCompletionBox');
        const compPill = document.getElementById('sharedModalCompletionPill');

        if (compContainer) compContainer.classList.remove('hidden');
        if (officerPhotos.length > 0) {
            compImg.src = officerPhotos[0];
            compImg.classList.remove('hidden');
            if (compPlaceholder) compPlaceholder.classList.add('hidden');
            if (compBox) compBox.className = 'aspect-[16/10] rounded-xl sm:rounded-2xl overflow-hidden border border-[#A7F3D0] bg-[#ECFDF5] relative flex items-center justify-center';
            if (compPill) {
                compPill.className = 'absolute bottom-2 left-2 sm:bottom-3 sm:left-3 bg-[#047857]/90 text-white text-[10px] sm:text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1.5 pointer-events-none';
                compPill.innerHTML = '<i data-lucide="badge-check" class="w-3 h-3 shrink-0 hidden sm:inline"></i><span id="sharedModalCompletionPhotoLabel">Foto Hasil Pengerjaan</span>';
            }

            if (officerGallery) {
                if (officerPhotos.length > 1) {
                    officerGallery.classList.remove('hidden');
                    officerGallery.innerHTML = officerPhotos.map((url, idx) => `
                        <button type="button" onclick="document.getElementById('sharedModalCompletionPhoto').src='${url}';" 
                            class="w-12 h-12 rounded-xl overflow-hidden border border-emerald-300 bg-emerald-50 shrink-0 hover:ring-2 hover:ring-emerald-600 transition cursor-pointer">
                            <img src="${url}" class="w-full h-full object-cover">
                        </button>
                    `).join('');
                } else {
                    officerGallery.classList.add('hidden');
                    officerGallery.innerHTML = '';
                }
            }
            if (window.lucide) window.lucide.createIcons();
        } else {
            compImg.classList.add('hidden');
            if (compPlaceholder) compPlaceholder.classList.remove('hidden');
            if (officerGallery) officerGallery.classList.add('hidden');

            const statusUpper = String(item.status || '').toUpperCase();

            if (statusUpper === 'CANCELLED') {
                if (compBox) compBox.className = 'aspect-[16/10] rounded-xl sm:rounded-2xl overflow-hidden border border-slate-200 bg-slate-50 relative flex items-center justify-center';
                if (compPlaceholder) {
                    compPlaceholder.className = 'inset-0 absolute flex flex-col items-center justify-center text-center p-3 text-slate-600 bg-slate-50';
                    compPlaceholder.innerHTML = '<i data-lucide="x-circle" class="w-6 h-6 mb-1 text-slate-400"></i><span id="sharedModalCompletionText" class="text-[11px] sm:text-xs font-semibold leading-tight">Laporan Dibatalkan</span>';
                }
                if (compPill) {
                    compPill.className = 'absolute bottom-2 left-2 sm:bottom-3 sm:left-3 bg-slate-600/90 text-white text-[10px] sm:text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1.5 pointer-events-none';
                    compPill.innerHTML = '<i data-lucide="x-circle" class="w-3 h-3 shrink-0 hidden sm:inline"></i><span id="sharedModalCompletionPhotoLabel">Laporan Dibatalkan</span>';
                }
            } else if (statusUpper === 'REJECTED') {
                if (compBox) compBox.className = 'aspect-[16/10] rounded-xl sm:rounded-2xl overflow-hidden border border-red-200 bg-red-50 relative flex items-center justify-center';
                if (compPlaceholder) {
                    compPlaceholder.className = 'inset-0 absolute flex flex-col items-center justify-center text-center p-3 text-red-700 bg-red-50';
                    compPlaceholder.innerHTML = '<i data-lucide="x-circle" class="w-6 h-6 mb-1 text-red-500"></i><span id="sharedModalCompletionText" class="text-[11px] sm:text-xs font-semibold leading-tight">Laporan Ditolak</span>';
                }
                if (compPill) {
                    compPill.className = 'absolute bottom-2 left-2 sm:bottom-3 sm:left-3 bg-red-600/90 text-white text-[10px] sm:text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1.5 pointer-events-none';
                    compPill.innerHTML = '<i data-lucide="x-circle" class="w-3 h-3 shrink-0 hidden sm:inline"></i><span id="sharedModalCompletionPhotoLabel">Laporan Ditolak</span>';
                }
            } else {
                if (compBox) compBox.className = 'aspect-[16/10] rounded-xl sm:rounded-2xl overflow-hidden border border-[#A7F3D0] bg-[#ECFDF5] relative flex items-center justify-center';
                if (compPlaceholder) {
                    compPlaceholder.className = 'inset-0 absolute flex flex-col items-center justify-center text-center p-3 text-[#047857] bg-[#ECFDF5]';
                    compPlaceholder.innerHTML = '<i data-lucide="clock" class="w-5 h-5 mb-1 text-[#10B981]"></i><span id="sharedModalCompletionText" class="text-[11px] sm:text-xs font-semibold leading-tight">Menunggu Foto Hasil Pengerjaan</span>';
                }
                if (compPill) {
                    compPill.className = 'absolute bottom-2 left-2 sm:bottom-3 sm:left-3 bg-[#047857]/90 text-white text-[10px] sm:text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1.5 pointer-events-none';
                    compPill.innerHTML = '<i data-lucide="badge-check" class="w-3 h-3 shrink-0 hidden sm:inline"></i><span id="sharedModalCompletionPhotoLabel">Foto Hasil Pengerjaan</span>';
                }
            }
            if (window.lucide) window.lucide.createIcons();
        }

        // 8. Identitas Pelapor Aduan
        const reporterEl = document.getElementById('sharedModalReporter');
        const anonBadge = document.getElementById('sharedModalAnonymousBadge');
        if (reporterEl) reporterEl.innerText = item.reporter_name || item.citizen_name || 'Warga Terdaftar';

        if (anonBadge) {
            if (item.is_anonymous) {
                anonBadge.classList.remove('hidden');
            } else {
                anonBadge.classList.add('hidden');
            }
        }

        // Nomor Telepon Pelapor (Hanya tampil jika ada hak internal staf / tidak publik)
        const privacyBadge = document.getElementById('sharedModalPrivacyBadge');
        const phoneLink = document.getElementById('sharedModalReporterPhoneLink');
        const phoneText = document.getElementById('sharedModalReporterPhoneText');
        const citizenPhone = item.reporter_phone || item.citizen_phone;

        if (phoneLink && citizenPhone && citizenPhone !== '-' && !citizenPhone.includes('***')) {
            if (privacyBadge) privacyBadge.classList.add('hidden');
            phoneLink.classList.remove('hidden');
            phoneLink.classList.add('inline-flex');
            phoneLink.href = 'tel:' + citizenPhone.replace(/[^0-9]/g, '');
            if (phoneText) phoneText.innerText = citizenPhone;
        } else {
            if (privacyBadge) privacyBadge.classList.remove('hidden');
            if (phoneLink) phoneLink.classList.add('hidden');
        }

        // 9. Kartu Regu Lapangan Pelaksana
        const officerCard = document.getElementById('sharedModalOfficerCard');
        if (officerCard) {
            const hasOfficer = item.team_name || item.officer_name || item.assignee_name || item.department_name || item.instruction;
            const isInProgressOrHandled = ['IN_PROGRESS', 'DISPATCHED', 'WAITING_VERIFICATION', 'COMPLETED', 'CLOSED'].includes(item.status);

            if (hasOfficer && isInProgressOrHandled) {
                officerCard.classList.remove('hidden');

                const offNameEl = document.getElementById('sharedModalOfficerName');
                const offDeptEl = document.getElementById('sharedModalOfficerDept');
                const leaderEl = document.getElementById('sharedModalTeamLeader');
                const teamPhoneLink = document.getElementById('sharedModalOfficerPhone');
                const teamPhoneText = document.getElementById('sharedModalOfficerPhoneText');
                const deadlineBox = document.getElementById('sharedModalDeadlineBox');
                const deadlineEl = document.getElementById('sharedModalOfficerDeadline');
                const instRow = document.getElementById('sharedModalInstructionRow');
                const instEl = document.getElementById('sharedModalOfficerInstruction');

                const teamNameStr = item.team_name 
                    ? (item.team_code ? `[${item.team_code}] ${item.team_name}` : item.team_name)
                    : (item.assignee_name || 'Regu Lapangan PUPR');
                if (offNameEl) offNameEl.innerText = teamNameStr;

                if (offDeptEl) offDeptEl.innerText = item.department_name || 'Dinas Pekerjaan Umum dan Penataan Ruang (PUPR)';
                if (leaderEl) leaderEl.innerText = item.team_leader || item.assignee_name || item.officer_name || 'Pengawas Lapangan';

                const teamPhone = item.team_leader_phone || item.officer_phone || item.assignee_phone || '081100000004';
                if (teamPhoneText) teamPhoneText.innerText = teamPhone;
                if (teamPhoneLink) teamPhoneLink.href = 'tel:' + teamPhone.replace(/[^0-9]/g, '');

                if (deadlineBox) {
                    if (item.deadline) {
                        deadlineBox.classList.remove('hidden');
                        if (deadlineEl) deadlineEl.innerText = item.deadline;
                    } else {
                        deadlineBox.classList.add('hidden');
                    }
                }

                if (instRow) {
                    if (item.instruction) {
                        instRow.classList.remove('hidden');
                        if (instEl) instEl.innerText = item.instruction;
                    } else {
                        instRow.classList.add('hidden');
                    }
                }
            } else {
                officerCard.classList.add('hidden');
            }
        }

        // 10. Lokasi Kerusakan & GIS
        const coordsEl = document.getElementById('sharedModalCoords');
        if (coordsEl) {
            coordsEl.innerText = (item.latitude && item.longitude) ? `${item.latitude}, ${item.longitude}` : '-';
        }

        const fullAddrEl = document.getElementById('sharedModalFullAddress');
        if (fullAddrEl) {
            fullAddrEl.innerText = item.address || 'Wilayah Kabupaten';
        }

        // Tombol Navigasi Google Maps (khusus Petugas)
        const navBtn = document.getElementById('sharedModalNavBtn');
        if (navBtn) {
            if (item.latitude && item.longitude) {
                navBtn.href = `https://www.google.com/maps/dir/?api=1&destination=${item.latitude},${item.longitude}`;
                navBtn.classList.remove('hidden');
            } else {
                navBtn.classList.add('hidden');
            }
        }

        // 11. Spesifikasi Ruas Jalan Panjang (Major Road)
        const majorBox = document.getElementById('sharedModalMajorRoadBox');
        if (majorBox) {
            if (item.damage_type === 'major') {
                majorBox.classList.remove('hidden');
                const condEl = document.getElementById('sharedModalRoadCondition');
                const lenEl = document.getElementById('sharedModalRoadLength');
                if (condEl) condEl.innerText = item.road_condition || 'Rusak Parah / Berlubang';
                if (lenEl) {
                    const cleanLen = (item.road_length || '').toString().replace(/\s*(meter|m)\s*$/i, '').trim();
                    lenEl.innerText = cleanLen ? cleanLen + ' Meter' : 'Belum diukur';
                }
            } else {
                majorBox.classList.add('hidden');
            }
        }

        // 12. Deskripsi Kerusakan
        const descEl = document.getElementById('sharedModalDescription');
        if (descEl) descEl.innerText = item.description || 'Tidak ada deskripsi rinci.';

        // Buka Modal & Render Lucide Icons
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        if (window.lucide) {
            lucide.createIcons();
        }
    };

    window.closeSharedDetailModal = function() {
        const modal = document.getElementById('complaintDetailModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    };

    // Close on Escape or Backdrop Click
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            window.closeSharedDetailModal();
        }
    });

    document.addEventListener('click', function(e) {
        const modal = document.getElementById('complaintDetailModal');
        if (modal && e.target === modal) {
            window.closeSharedDetailModal();
        }
    });

    // Alias Global untuk Kompatibilitas Seluruh Halaman Eksisting
    window.openDetailModal        = window.openSharedDetailModal;
    window.closeDetailModal       = window.closeSharedDetailModal;
    window.openOperatorModal      = window.openSharedDetailModal;
    window.closeOperatorModal     = window.closeSharedDetailModal;
    window.openBupatiDetailModal  = window.openSharedDetailModal;
    window.closeBupatiDetailModal = window.closeSharedDetailModal;
    window.openPetugasDetailModal = window.openSharedDetailModal;
    window.closePetugasDetailModal= window.closeSharedDetailModal;
})();
</script>
