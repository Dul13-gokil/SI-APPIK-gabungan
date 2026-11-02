import 'package:flutter/material.dart';
import '../theme/app_colors.dart';

class StatusBadge extends StatelessWidget {
  final String status;

  const StatusBadge({super.key, required this.status});

  Color _getStatusColor() {
    switch (status.toUpperCase()) {
      case 'DRAFT':
      case 'SUBMITTED':
        return AppColors.statusSubmitted;
      case 'VERIFICATION':
        return AppColors.statusVerification;
      case 'VERIFIED':
      case 'DISPATCHED':
        return AppColors.statusDispatched;
      case 'IN_PROGRESS':
        return AppColors.statusInProgress;
      case 'WAITING_VERIFICATION':
        return AppColors.statusWaitingVerification;
      case 'COMPLETED':
      case 'CLOSED':
        return AppColors.statusCompleted;
      case 'REJECTED':
        return AppColors.statusRejected;
      case 'REVISION_REQUIRED':
        return AppColors.accent;
      default:
        return AppColors.textSecondary;
    }
  }

  String _getStatusLabel() {
    switch (status.toUpperCase()) {
      case 'DRAFT':
        return 'Draf';
      case 'SUBMITTED':
      case 'VERIFICATION':
        return 'Verifikasi';
      case 'VERIFIED':
        return 'Terverifikasi';
      case 'DISPATCHED':
        return 'Ditugaskan';
      case 'IN_PROGRESS':
        return 'Dikerjakan';
      case 'WAITING_VERIFICATION':
        return 'Validasi';
      case 'REVISION_REQUIRED':
        return 'Perlu Revisi';
      case 'COMPLETED':
        return 'Selesai';
      case 'CLOSED':
        return 'Ditutup';
      case 'REJECTED':
        return 'Ditolak';
      default:
        return status;
    }
  }

  @override
  Widget build(BuildContext context) {
    final color = _getStatusColor();
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 5,
            height: 5,
            decoration: BoxDecoration(
              color: color,
              shape: BoxShape.circle,
            ),
          ),
          const SizedBox(width: 5),
          Text(
            _getStatusLabel(),
            style: TextStyle(
              color: color,
              fontSize: 11,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }
}
