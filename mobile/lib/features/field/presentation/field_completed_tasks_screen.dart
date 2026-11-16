import 'package:flutter/material.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';
import '../../../core/network/api_client.dart';
import '../../../shared/models/complaint_model.dart';
import '../../../shared/theme/app_colors.dart';
import '../../../shared/widgets/status_badge.dart';
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
      appBar: AppBar(
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Tugas Selesai',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: AppColors.textPrimary),
            ),
            Text(
              'Pekerjaan lapangan yang telah tuntas & divalidasi Operator',
              style: TextStyle(fontSize: 11, color: AppColors.textSecondary),
            ),
          ],
        ),
        backgroundColor: Colors.white,
        elevation: 0,
        foregroundColor: AppColors.textPrimary,
        toolbarHeight: 64,
        actions: [
          IconButton(
            icon: const Icon(LucideIcons.refreshCw, size: 20, color: AppColors.primary),
            tooltip: 'Segarkan Riwayat',
            onPressed: _fetchCompletedTasks,
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: Column(
        children: [
          // Search Bar
          Container(
            color: Colors.white,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            child: TextField(
              decoration: InputDecoration(
                hintText: 'Cari riwayat kode, judul, atau jalan...',
                prefixIcon: const Icon(LucideIcons.search, size: 18, color: AppColors.textSecondary),
                filled: true,
                fillColor: AppColors.background,
                contentPadding: const EdgeInsets.symmetric(vertical: 0, horizontal: 16),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: AppColors.border),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
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
    );
  }

  Widget _buildCompletedCard(ComplaintModel task) {
    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
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
                  // Baris Atas: Kode Aduan, Tipe Kerusakan, Regu, dan Status
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      Expanded(
                        child: Wrap(
                          spacing: 6,
                          runSpacing: 4,
                          crossAxisAlignment: WrapCrossAlignment.center,
                          children: [
                            Text(
                              task.publicCode,
                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 11.5, color: AppColors.primary),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(
                                color: task.damageType == 'major' ? const Color(0xFFEFF6FF) : const Color(0xFFF1F5F9),
                                borderRadius: BorderRadius.circular(6),
                                border: Border.all(
                                  color: task.damageType == 'major' ? const Color(0xFFBFDBFE) : const Color(0xFFE2E8F0),
                                ),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(
                                    task.damageType == 'major' ? LucideIcons.route : LucideIcons.circleDot,
                                    size: 10,
                                    color: task.damageType == 'major' ? const Color(0xFF1E40AF) : const Color(0xFF475569),
                                  ),
                                  const SizedBox(width: 3),
                                  Text(
                                    task.damageType == 'major' ? 'Kerusakan Berat' : 'Kerusakan Ringan',
                                    style: TextStyle(
                                      fontSize: 9.5,
                                      fontWeight: FontWeight.bold,
                                      color: task.damageType == 'major' ? const Color(0xFF1E40AF) : const Color(0xFF475569),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            if (task.damageType == 'major' && task.roadLength != null)
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                decoration: BoxDecoration(
                                  color: Colors.grey.shade50,
                                  borderRadius: BorderRadius.circular(6),
                                  border: Border.all(color: AppColors.border),
                                ),
                                child: Text(
                                  '${task.roadLength}m',
                                  style: const TextStyle(fontSize: 9.5, fontWeight: FontWeight.bold, color: AppColors.textSecondary),
                                ),
                              ),
                            if (task.teamName != null)
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFF1F5F9),
                                  borderRadius: BorderRadius.circular(6),
                                  border: Border.all(color: const Color(0xFFE2E8F0)),
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    const Icon(LucideIcons.hardHat, size: 10, color: Color(0xFF475569)),
                                    const SizedBox(width: 3),
                                    Text(
                                      task.teamName!,
                                      style: const TextStyle(fontSize: 9.5, fontWeight: FontWeight.bold, color: Color(0xFF334155)),
                                    ),
                                  ],
                                ),
                              ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 6),
                      const StatusBadge(status: 'COMPLETED'),
                    ],
                  ),
                  const SizedBox(height: 8),

                  // Judul Tugas
                  Text(
                    task.title,
                    style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                  ),
                  const SizedBox(height: 4),

                  // Alamat Lokasi
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Icon(LucideIcons.mapPin, size: 12, color: AppColors.textSecondary),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          task.address,
                          style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 8),
                  // Kategori & Label Tuntas
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

          // Divider & Banner Validasi Resmi
          const Divider(height: 1),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
            child: Row(
              children: [
                Expanded(
                  child: Container(
                    padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 12),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF0FDF4),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: const Color(0xFFA7F3D0)),
                    ),
                    child: const Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(LucideIcons.checkCheck, size: 15, color: AppColors.statusCompleted),
                        SizedBox(width: 6),
                        Flexible(
                          child: Text(
                            'Telah divalidasi & dinyatakan tuntas oleh operator',
                            style: TextStyle(fontSize: 11.5, color: Color(0xFF166534), fontWeight: FontWeight.bold),
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

