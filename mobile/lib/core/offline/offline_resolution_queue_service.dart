import 'dart:convert';
import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:shared_preferences/shared_preferences.dart';
import '../network/api_client.dart';

class OfflineResolutionItem {
  final int complaintId;
  final String publicCode;
  final String title;
  final String note;
  final String photoPath;
  final String queuedAt;
  /// Titik GPS saat foto diambil; dipakai server untuk memeriksa radius pengerjaan.
  final double? latitude;
  final double? longitude;

  OfflineResolutionItem({
    required this.complaintId,
    required this.publicCode,
    required this.title,
    required this.note,
    required this.photoPath,
    required this.queuedAt,
    this.latitude,
    this.longitude,
  });

  Map<String, dynamic> toJson() => {
        'complaint_id': complaintId,
        'public_code': publicCode,
        'title': title,
        'note': note,
        'photo_path': photoPath,
        'queued_at': queuedAt,
        'latitude': latitude,
        'longitude': longitude,
      };

  factory OfflineResolutionItem.fromJson(Map<String, dynamic> json) {
    return OfflineResolutionItem(
      complaintId: json['complaint_id'] as int,
      publicCode: (json['public_code'] ?? '').toString(),
      title: (json['title'] ?? '').toString(),
      note: (json['note'] ?? '').toString(),
      photoPath: (json['photo_path'] ?? '').toString(),
      queuedAt: (json['queued_at'] ?? '').toString(),
      latitude: (json['latitude'] as num?)?.toDouble(),
      longitude: (json['longitude'] as num?)?.toDouble(),
    );
  }
}

class OfflineResolutionQueueService {
  static const _storageKey = 'siappik_offline_resolution_queue_v1';

  Future<List<OfflineResolutionItem>> getQueue() async {
    if (kIsWeb) return [];
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString(_storageKey);
    if (raw == null || raw.isEmpty) return [];
    try {
      final list = jsonDecode(raw) as List;
      return list
          .map((e) => OfflineResolutionItem.fromJson(Map<String, dynamic>.from(e as Map)))
          .where((item) => File(item.photoPath).existsSync())
          .toList();
    } catch (_) {
      return [];
    }
  }

  Future<void> enqueue(OfflineResolutionItem item) async {
    final queue = await getQueue();
    queue.removeWhere((e) => e.complaintId == item.complaintId);
    queue.add(item);
    await _saveQueue(queue);
  }

  Future<void> _saveQueue(List<OfflineResolutionItem> queue) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(
      _storageKey,
      jsonEncode(queue.map((e) => e.toJson()).toList()),
    );
  }

  /// Coba unggah seluruh antrean bukti perbaikan yang tersimpan saat offline.
  /// Mengembalikan jumlah item yang berhasil tersinkronisasi.
  Future<int> syncAll(ApiClient apiClient) async {
    final queue = await getQueue();
    if (queue.isEmpty) return 0;

    int syncedCount = 0;
    final remaining = <OfflineResolutionItem>[];

    for (final item in queue) {
      final file = File(item.photoPath);
      if (!file.existsSync()) continue;

      try {
        final formData = FormData();
        formData.fields.add(MapEntry('note', item.note));
        if (item.latitude != null && item.longitude != null) {
          formData.fields.add(MapEntry('latitude', item.latitude!.toStringAsFixed(6)));
          formData.fields.add(MapEntry('longitude', item.longitude!.toStringAsFixed(6)));
        }
        formData.files.add(MapEntry(
          'photos[]',
          await MultipartFile.fromFile(file.path, filename: 'resolution_offline.jpg'),
        ));

        final res = await apiClient.dio.post(
          '/complaints/${item.complaintId}/finish-work',
          data: formData,
        );
        if (res.statusCode == 200) {
          syncedCount++;
        } else {
          remaining.add(item);
        }
      } catch (_) {
        remaining.add(item);
      }
    }

    await _saveQueue(remaining);
    return syncedCount;
  }
}
