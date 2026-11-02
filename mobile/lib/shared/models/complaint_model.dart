class ComplaintModel {
  final int id;
  final int? userId;
  final String publicCode;
  final String title;
  final String description;
  final String address;
  final double latitude;
  final double longitude;
  final String status;
  final int categoryId;
  final String? categoryName;
  final double? priorityScore;
  final String? priorityLevel;
  final String? aiCategory;
  final double? aiConfidence;
  final int supportsCount;
  final bool hasSupported;
  final List<String> photos;
  final List<String> completionPhotos;
  final String reporterName;
  final String? reporterPhone;
  final bool isAnonymous;
  final Map<String, dynamic>? latestDisposition;
  final int? assignedOfficerId;
  final String? assigneePhone;
  final int? teamId;
  final String? teamName;
  final String? teamCode;
  final String? teamLeaderName;
  final String? teamLeaderPhone;
  final int? teamMembersCount;
  final String? damageType;
  final String? damageTypeLabel;
  final String? roadLength;
  final String? roadCondition;
  final double? endLatitude;
  final double? endLongitude;
  final List<List<double>>? routeCoordinates;
  final DateTime createdAt;

  ComplaintModel({
    required this.id,
    this.userId,
    required this.publicCode,
    required this.title,
    required this.description,
    required this.address,
    required this.latitude,
    required this.longitude,
    required this.status,
    required this.categoryId,
    this.categoryName,
    this.priorityScore,
    this.priorityLevel,
    this.aiCategory,
    this.aiConfidence,
    this.supportsCount = 0,
    this.hasSupported = false,
    this.photos = const [],
    this.completionPhotos = const [],
    this.reporterName = 'Warga',
    this.reporterPhone,
    this.isAnonymous = false,
    this.latestDisposition,
    this.assignedOfficerId,
    this.assigneePhone,
    this.teamId,
    this.teamName,
    this.teamCode,
    this.teamLeaderName,
    this.teamLeaderPhone,
    this.teamMembersCount,
    this.damageType,
    this.damageTypeLabel,
    this.roadLength,
    this.roadCondition,
    this.endLatitude,
    this.endLongitude,
    this.routeCoordinates,
    required this.createdAt,
  });

  bool get isOverdue => latestDisposition?['is_overdue'] == true;
  String? get deadlineHuman => latestDisposition?['deadline_human'];
  String? get deadline => latestDisposition?['deadline'];
  String? get assigneeName => latestDisposition?['assignee_name'];
  String? get effectiveAssigneePhone => assigneePhone ?? latestDisposition?['assignee_phone'] ?? latestDisposition?['assignee']?['phone'];
  String? get revisionNotes => latestDisposition?['revision_notes'] ?? latestDisposition?['notes'] ?? latestDisposition?['instruction'];

  ComplaintModel copyWith({
    int? id,
    int? userId,
    String? publicCode,
    String? title,
    String? description,
    String? address,
    double? latitude,
    double? longitude,
    String? status,
    int? categoryId,
    String? categoryName,
    double? priorityScore,
    String? priorityLevel,
    String? aiCategory,
    double? aiConfidence,
    int? supportsCount,
    bool? hasSupported,
    List<String>? photos,
    List<String>? completionPhotos,
    String? reporterName,
    String? reporterPhone,
    bool? isAnonymous,
    Map<String, dynamic>? latestDisposition,
    int? assignedOfficerId,
    String? assigneePhone,
    int? teamId,
    String? teamName,
    String? teamCode,
    String? teamLeaderName,
    String? teamLeaderPhone,
    int? teamMembersCount,
    String? damageType,
    String? damageTypeLabel,
    String? roadLength,
    String? roadCondition,
    double? endLatitude,
    double? endLongitude,
    List<List<double>>? routeCoordinates,
    DateTime? createdAt,
  }) {
    return ComplaintModel(
      id: id ?? this.id,
      userId: userId ?? this.userId,
      publicCode: publicCode ?? this.publicCode,
      title: title ?? this.title,
      description: description ?? this.description,
      address: address ?? this.address,
      latitude: latitude ?? this.latitude,
      longitude: longitude ?? this.longitude,
      status: status ?? this.status,
      categoryId: categoryId ?? this.categoryId,
      categoryName: categoryName ?? this.categoryName,
      priorityScore: priorityScore ?? this.priorityScore,
      priorityLevel: priorityLevel ?? this.priorityLevel,
      aiCategory: aiCategory ?? this.aiCategory,
      aiConfidence: aiConfidence ?? this.aiConfidence,
      supportsCount: supportsCount ?? this.supportsCount,
      hasSupported: hasSupported ?? this.hasSupported,
      photos: photos ?? this.photos,
      completionPhotos: completionPhotos ?? this.completionPhotos,
      reporterName: reporterName ?? this.reporterName,
      reporterPhone: reporterPhone ?? this.reporterPhone,
      isAnonymous: isAnonymous ?? this.isAnonymous,
      latestDisposition: latestDisposition ?? this.latestDisposition,
      assignedOfficerId: assignedOfficerId ?? this.assignedOfficerId,
      assigneePhone: assigneePhone ?? this.assigneePhone,
      teamId: teamId ?? this.teamId,
      teamName: teamName ?? this.teamName,
      teamCode: teamCode ?? this.teamCode,
      teamLeaderName: teamLeaderName ?? this.teamLeaderName,
      teamLeaderPhone: teamLeaderPhone ?? this.teamLeaderPhone,
      teamMembersCount: teamMembersCount ?? this.teamMembersCount,
      damageType: damageType ?? this.damageType,
      damageTypeLabel: damageTypeLabel ?? this.damageTypeLabel,
      roadLength: roadLength ?? this.roadLength,
      roadCondition: roadCondition ?? this.roadCondition,
      endLatitude: endLatitude ?? this.endLatitude,
      endLongitude: endLongitude ?? this.endLongitude,
      routeCoordinates: routeCoordinates ?? this.routeCoordinates,
      createdAt: createdAt ?? this.createdAt,
    );
  }

  factory ComplaintModel.fromJson(Map<String, dynamic> json) {
    List<List<double>>? parsedRoute;
    if (json['route_coordinates'] != null && json['route_coordinates'] is List) {
      try {
        parsedRoute = (json['route_coordinates'] as List)
            .map((e) => (e as List).map((c) => (c as num).toDouble()).toList())
            .toList();
      } catch (_) {}
    }

    return ComplaintModel(
      id: json['id'] ?? 0,
      userId: json['user_id'] ?? json['reporter']?['id'],
      publicCode: json['public_code'] ?? '',
      title: json['title'] ?? '',
      description: json['description'] ?? '',
      address: json['address'] ?? '',
      latitude: (json['latitude'] as num?)?.toDouble() ?? 0.0,
      longitude: (json['longitude'] as num?)?.toDouble() ?? 0.0,
      status: json['status'] ?? 'SUBMITTED',
      categoryId: json['category_id'] ?? (json['category'] is Map ? json['category']['id'] : null) ?? 0,
      categoryName: json['category_name'] ?? (json['category'] is Map ? json['category']['name'] : null),
      priorityScore: (json['priority_score'] as num?)?.toDouble(),
      priorityLevel: json['priority_level'],
      aiCategory: json['ai_category'],
      aiConfidence: (json['ai_confidence'] as num?)?.toDouble(),
      supportsCount: json['supports_count'] ?? 0,
      hasSupported: json['has_supported'] ?? false,
      photos: (json['photos'] as List<dynamic>?)
              ?.map((p) => p is Map ? (p['url']?.toString() ?? '') : p.toString())
              .where((url) => url.isNotEmpty)
              .toSet()
              .toList() ??
          [],
      completionPhotos: (json['completion_photos'] as List<dynamic>?)
              ?.map((p) => p is Map ? (p['url']?.toString() ?? '') : p.toString())
              .where((url) => url.isNotEmpty)
              .toSet()
              .toList() ??
          [],
      reporterName: json['reporter_name'] ??
          json['reporter']?['name'] ??
          (json['is_anonymous'] == true ? 'Warga Anonim' : 'Warga'),
      reporterPhone: json['reporter_phone'] ??
          json['reporter']?['phone'] ??
          json['user']?['phone'],
      isAnonymous: json['is_anonymous'] == true,
      latestDisposition: json['latest_disposition'],
      assignedOfficerId: json['assigned_officer_id'] ??
          json['assigned_to'] ??
          (json['latest_disposition'] is Map ? json['latest_disposition']['assigned_to'] : null),
      assigneePhone: json['assignee_phone'] ??
          json['latest_disposition']?['assignee_phone'] ??
          json['latest_disposition']?['assignee']?['phone'],
      teamId: json['team_id'] ??
          (json['latest_disposition'] is Map ? json['latest_disposition']['team_id'] : null) ??
          (json['team'] is Map ? json['team']['id'] : null),
      teamName: json['team_name'] ??
          (json['latest_disposition'] is Map ? json['latest_disposition']['team_name'] : null) ??
          (json['team'] is Map ? json['team']['name'] : null),
      teamCode: json['team_code'] ?? (json['team'] is Map ? json['team']['code'] : null),
      teamLeaderName: json['team_leader_name'] ?? (json['team'] is Map ? json['team']['leader_name'] : null),
      teamLeaderPhone: json['team_leader_phone'] ?? (json['team'] is Map ? json['team']['leader_phone'] : null) ?? json['latest_disposition']?['team_leader_phone'],
      teamMembersCount: json['team_members_count'] ?? (json['team'] is Map ? json['team']['members_count'] : null),
      damageType: (json['damage_type'] ?? 'minor').toString().toLowerCase(),
      damageTypeLabel: json['damage_type_label'],
      roadLength: json['road_length'],
      roadCondition: json['road_condition'],
      endLatitude: (json['end_latitude'] as num?)?.toDouble(),
      endLongitude: (json['end_longitude'] as num?)?.toDouble(),
      routeCoordinates: parsedRoute,
      createdAt: DateTime.tryParse(json['created_at'] ?? '') ?? DateTime.now(),
    );
  }
}
