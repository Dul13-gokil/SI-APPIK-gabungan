import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';
import '../../../core/network/api_client.dart';
import '../../../shared/models/complaint_model.dart';
import '../../../shared/theme/app_colors.dart';
import '../../../shared/widgets/status_badge.dart';
import '../../../shared/widgets/v2_components.dart';
import '../../complaints/presentation/complaint_detail_screen.dart';

class FieldCompletedTasksScreen extends StatefulWidget {
  const FieldCompletedTasksScreen({super.key});

  @override
  State<FieldCompletedTasksScreen> createState() => _FieldCompletedTasksScreenState();
}

class _FieldCompletedTasksScreenState extends State<FieldCompletedTasksScreen> {
  final _apiClient = ApiClient();
  List<ComplaintModel> _tasks = [];
  bool _isLoading = true;
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    _fetchCompletedTasks();
  }

  Future<void> _fetchCompletedTasks() async {
    setState(() => _isLoading = true);
    try {
      final res = await _apiClient.dio.get('/field-tasks', queryParameters: {
        'status': 'COMPLETED',
      });
      if (res.statusCode == 200 && res.data['data']?['items'] != null) {
        final List items = res.data['data']['items'];
        if (mounted) {
          setState(() {
            _tasks = items.map((e) => ComplaintModel.fromJson(e)).toList();
          });
        }
      }
    } catch (_) {}
    if (mounted) setState(() => _isLoading = false);
  }

  List<ComplaintModel> get _filteredTasks {
    if (_searchQuery.trim().isEmpty) return _tasks;
    final q = _searchQuery.toLowerCase();
    return _tasks.where((t) {
      return t.publicCode.toLowerCase().contains(q) ||
          t.title.toLowerCase().contains(q) ||
          t.address.toLowerCase().contains(q);
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        bottom: false,
        child: Column(
          children: [
            V2FloatingHeader(
              title: 'Tugas Selesai',
              subtitle: 'Pekerjaan lapangan yang telah tuntas & divalidasi Operator',
              leading: Container(
                width: 36,
                height: 36,
                decoration: BoxDecoration(
                  color: AppColors.primaryContainer,
                  borderRadius: BorderRadius.circular(11),
                  border: Border.all(color: AppColors.primaryBorder),
                ),
                child: const Icon(LucideIcons.checkCircle2, size: 18, color: AppColors.primary),
              ),
              actions: [
                GestureDetector(
                  onTap: _fetchCompletedTasks,
                  child: Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      color: AppColors.background,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: AppColors.border),
                    ),
                    child: const Icon(LucideIcons.refreshCw, size: 18, color: AppColors.primary),
                  ),
                ),
              ],
            ),
            // Search Bar (Seragam dengan Halaman Warga)
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 10, 16, 10),
              child: TextField(
                style: GoogleFonts.inter(fontSize: 13, color: AppColors.textPrimary),
                decoration: InputDecoration(
                  hintText: 'Cari riwayat kode (#0002/ringan/2026), judul, atau lokasi...',
                  hintStyle: GoogleFonts.inter(fontSize: 12.5, color: AppColors.textSecondary),
                  prefixIcon: const Icon(LucideIcons.search, size: 18, color: AppColors.textSecondary),
                  filled: true,
                  fillColor: Colors.white,
                  contentPadding: const EdgeInsets.symmetric(vertical: 0, horizontal: 16),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide: const BorderSide(color: AppColors.border),
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide: const BorderSide(color: AppColors.border),
                  ),
                ),
                onChanged: (v) => setState(() => _searchQuery = v),
              ),
            ),

          // Total Count Banner
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            color: AppColors.primary.withValues(alpha: 0.05),
            child: Row(
              children: [
                const Icon(LucideIcons.badgeCheck, size: 16, color: AppColors.primary),
                const SizedBox(width: 8),
                Text(
                  'Total ${_filteredTasks.length} Pekerjaan Fisik Tuntas',
                  style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.primary),
                ),
              ],
            ),
          ),

          // Task List
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : RefreshIndicator(
                    onRefresh: _fetchCompletedTasks,
                    child: _filteredTasks.isEmpty
                        ? ListView(
                            children: const [
                              SizedBox(height: 120),
                              Center(
                                child: Column(
                                  children: [
                                    Icon(LucideIcons.checkCheck, size: 56, color: AppColors.statusCompleted),
                                    SizedBox(height: 16),
                                    Text(
                                      'Belum Ada Tugas Selesai',
                                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: AppColors.textPrimary),
                                    ),
                                    SizedBox(height: 6),
                                    Padding(
                                      padding: EdgeInsets.symmetric(horizontal: 32),
                                      child: Text(
                                        'Pekerjaan lapangan yang telah Anda selesaikan dan disetujui oleh Operator Dinas akan tercatat secara resmi di sini.',
                                        textAlign: TextAlign.center,
                                        style: TextStyle(color: AppColors.textSecondary, fontSize: 13),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          )
                        : ListView.builder(
                            padding: const EdgeInsets.all(16),
                            itemCount: _filteredTasks.length,
                            itemBuilder: (context, index) => _buildCompletedCard(_filteredTasks[index]),
                          ),
                  ),
          ),
        ],
      ),
      ),
    );
  }

  Widget _buildCompletedCard(ComplaintModel task) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          InkWell(
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => ComplaintDetailScreen(complaintId: task.id)),
            ),
            borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
            child: Padding(
              padding: const EdgeInsets.all(14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        task.publicCode.startsWith('#') ? task.publicCode : '#${task.publicCode}',
                        style: GoogleFonts.inter(
                          fontWeight: FontWeight.bold,
                          fontSize: 11.5,
                          color: AppColors.textPrimary,
                        ),
                      ),
                      const StatusBadge(status: 'COMPLETED'),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    task.title,
                    style: GoogleFonts.outfit(
                      fontSize: 15,
                      fontWeight: FontWeight.bold,
                      color: AppColors.textPrimary,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    task.address,
                    style: GoogleFonts.inter(fontSize: 12, color: AppColors.textSecondary),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      const Icon(LucideIcons.user, size: 12, color: AppColors.primary),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          'Pelapor: ${task.isAnonymous ? 'Anonim' : task.reporterName}${task.teamName != null ? ' • ${task.teamName}' : ''}',
                          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.textPrimary),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      if (task.categoryName != null)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: AppColors.background,
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(color: AppColors.border),
                          ),
                          child: Text(
                            task.categoryName!,
                            style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                          ),
                        )
                      else
                        const SizedBox.shrink(),
                      const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(LucideIcons.badgeCheck, size: 14, color: AppColors.statusCompleted),
                          SizedBox(width: 4),
                          Text(
                            'Tuntas 100%',
                            style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.statusCompleted),
                          ),
                        ],
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),

          // Divider & Banner Validasi Resmi (Clean & Modern)
          const Divider(height: 1, color: AppColors.border),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            child: Row(
              children: [
                Expanded(
                  child: Container(
                    padding: const EdgeInsets.symmetric(vertical: 9, horizontal: 12),
                    decoration: BoxDecoration(
                      color: AppColors.statusCompleted.withValues(alpha: 0.08),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(LucideIcons.checkCheck, size: 15, color: AppColors.statusCompleted),
                        const SizedBox(width: 6),
                        Flexible(
                          child: Text(
                            'Tuntas & Divalidasi Operator',
                            style: GoogleFonts.inter(
                              fontSize: 11.5,
                              color: AppColors.statusCompleted,
                              fontWeight: FontWeight.w600,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

}

