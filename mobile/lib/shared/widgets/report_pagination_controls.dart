import 'package:flutter/material.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';
import '../theme/app_colors.dart';

/// Segmented toggle button for switching between List (Daftar) and Grid (Petak) layout.
class ReportLayoutToggle extends StatelessWidget {
  final bool isGrid;
  final ValueChanged<bool> onChanged;

  const ReportLayoutToggle({
    super.key,
    required this.isGrid,
    required this.onChanged,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(3),
      decoration: BoxDecoration(
        color: AppColors.background,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: AppColors.border),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          _ToggleOption(
            icon: LucideIcons.list,
            label: 'Daftar',
            isSelected: !isGrid,
            onTap: () => onChanged(false),
          ),
          const SizedBox(width: 2),
          _ToggleOption(
            icon: LucideIcons.layoutGrid,
            label: 'Petak',
            isSelected: isGrid,
            onTap: () => onChanged(true),
          ),
        ],
      ),
    );
  }
}

class _ToggleOption extends StatelessWidget {
  final IconData icon;
  final String label;
  final bool isSelected;
  final VoidCallback onTap;

  const _ToggleOption({
    required this.icon,
    required this.label,
    required this.isSelected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(7),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 160),
        padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 5),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.primary : Colors.transparent,
          borderRadius: BorderRadius.circular(7),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              icon,
              size: 13,
              color: isSelected ? Colors.white : AppColors.textSecondary,
            ),
            const SizedBox(width: 4),
            Text(
              label,
              style: TextStyle(
                fontSize: 11,
                fontWeight: isSelected ? FontWeight.w700 : FontWeight.w600,
                color: isSelected ? Colors.white : AppColors.textSecondary,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

/// Pagination and page-size selector bar for mobile report/task lists.
class ReportPaginationBar extends StatelessWidget {
  final int totalItems;
  final int currentPage;
  final int pageSize;
  final String itemUnitLabel;
  final List<int> pageSizeOptions;
  final ValueChanged<int> onPageChanged;
  final ValueChanged<int> onPageSizeChanged;

  const ReportPaginationBar({
    super.key,
    required this.totalItems,
    required this.currentPage,
    required this.pageSize,
    this.itemUnitLabel = 'laporan',
    this.pageSizeOptions = const [5, 10, 20],
    required this.onPageChanged,
    required this.onPageSizeChanged,
  });

  @override
  Widget build(BuildContext context) {
    if (totalItems <= 0) return const SizedBox.shrink();

    final totalPages = (totalItems / pageSize).ceil().clamp(1, 9999);
    final safePage = currentPage.clamp(1, totalPages);
    final startIdx = (safePage - 1) * pageSize + 1;
    final endIdx = (safePage * pageSize).clamp(1, totalItems);

    return Container(
      margin: const EdgeInsets.only(top: 6),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Baris 1: Tampilkan X item + info Menampilkan A-B dari C
          Row(
            children: [
              const Text(
                'Tampilkan:',
                style: TextStyle(
                  fontSize: 11.5,
                  color: AppColors.textSecondary,
                  fontWeight: FontWeight.w600,
                ),
              ),
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: AppColors.background,
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: AppColors.primary.withValues(alpha: 0.45)),
                ),
                child: DropdownButtonHideUnderline(
                  child: DropdownButton<int>(
                    value: pageSizeOptions.contains(pageSize) ? pageSize : pageSizeOptions.first,
                    isDense: true,
                    icon: const Icon(LucideIcons.chevronDown, size: 13, color: AppColors.textPrimary),
                    style: const TextStyle(
                      fontSize: 11.5,
                      fontWeight: FontWeight.w700,
                      color: AppColors.textPrimary,
                    ),
                    items: pageSizeOptions
                        .map(
                          (val) => DropdownMenuItem<int>(
                            value: val,
                            child: Text('$val $itemUnitLabel'),
                          ),
                        )
                        .toList(),
                    onChanged: (val) {
                      if (val != null) onPageSizeChanged(val);
                    },
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  'Menampilkan $startIdx\u2013$endIdx dari $totalItems $itemUnitLabel',
                  textAlign: TextAlign.right,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    fontSize: 11,
                    color: AppColors.textSecondary,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ),
            ],
          ),

          if (totalPages > 1) ...[
            const SizedBox(height: 10),
            const Divider(height: 1, color: AppColors.border),
            const SizedBox(height: 10),
            // Baris 2: Navigasi Halaman (Sebelumnya, Nomor Halaman, Berikutnya)
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  _NavButton(
                    label: 'Sebelumnya',
                    enabled: safePage > 1,
                    onTap: () => onPageChanged(safePage - 1),
                  ),
                  const SizedBox(width: 6),
                  ..._buildPageNumbers(safePage, totalPages).map((pageItem) {
                    if (pageItem == null) {
                      return const Padding(
                        padding: EdgeInsets.symmetric(horizontal: 4),
                        child: Text(
                          '...',
                          style: TextStyle(fontSize: 11.5, color: AppColors.textSecondary),
                        ),
                      );
                    }
                    final isActive = pageItem == safePage;
                    return Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 2.5),
                      child: InkWell(
                        onTap: isActive ? null : () => onPageChanged(pageItem),
                        borderRadius: BorderRadius.circular(8),
                        child: Container(
                          constraints: const BoxConstraints(minWidth: 32, minHeight: 30),
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 5),
                          alignment: Alignment.center,
                          decoration: BoxDecoration(
                            color: isActive ? AppColors.primary : Colors.white,
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(
                              color: isActive ? AppColors.primary : AppColors.border,
                            ),
                          ),
                          child: Text(
                            '$pageItem',
                            style: TextStyle(
                              fontSize: 11.5,
                              fontWeight: FontWeight.w700,
                              color: isActive ? Colors.white : AppColors.textPrimary,
                            ),
                          ),
                        ),
                      ),
                    );
                  }),
                  const SizedBox(width: 6),
                  _NavButton(
                    label: 'Berikutnya',
                    enabled: safePage < totalPages,
                    onTap: () => onPageChanged(safePage + 1),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }

  List<int?> _buildPageNumbers(int current, int total) {
    if (total <= 5) {
      return List.generate(total, (i) => i + 1);
    }
    final pages = <int?>[1];
    if (current > 3) pages.add(null);
    for (int p = (current - 1).clamp(2, total - 1); p <= (current + 1).clamp(2, total - 1); p++) {
      pages.add(p);
    }
    if (current < total - 2) pages.add(null);
    pages.add(total);
    return pages;
  }
}

class _NavButton extends StatelessWidget {
  final String label;
  final bool enabled;
  final VoidCallback onTap;

  const _NavButton({
    required this.label,
    required this.enabled,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: enabled ? onTap : null,
      borderRadius: BorderRadius.circular(8),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        decoration: BoxDecoration(
          color: enabled ? Colors.white : AppColors.background,
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: AppColors.border),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 11.5,
            fontWeight: FontWeight.w600,
            color: enabled ? AppColors.textPrimary : AppColors.textSecondary.withValues(alpha: 0.55),
          ),
        ),
      ),
    );
  }
}
