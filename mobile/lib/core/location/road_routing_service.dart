import 'dart:math' as math;
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:latlong2/latlong.dart';

class RoadRouteResult {
  final List<LatLng> points;
  final double distanceMeters;
  final String formattedDistance;
  final bool isSnappedToRoad;

  const RoadRouteResult({
    required this.points,
    required this.distanceMeters,
    required this.formattedDistance,
    required this.isSnappedToRoad,
  });

  factory RoadRouteResult.direct({
    required LatLng start,
    required LatLng end,
  }) {
    final distanceMeters = _calculateHaversineDistance(start, end) * 1.20; // Tortuosity factor jalan berkelok
    return RoadRouteResult(
      points: [start, end],
      distanceMeters: distanceMeters,
      formattedDistance: _formatDistance(distanceMeters),
      isSnappedToRoad: false,
    );
  }

  static double _calculateHaversineDistance(LatLng p1, LatLng p2) {
    const r = 6371000.0; // Radius bumi dalam meter
    final dLat = _degToRad(p2.latitude - p1.latitude);
    final dLon = _degToRad(p2.longitude - p1.longitude);
    final lat1 = _degToRad(p1.latitude);
    final lat2 = _degToRad(p2.latitude);

    final a = math.sin(dLat / 2) * math.sin(dLat / 2) +
        math.sin(dLon / 2) * math.sin(dLon / 2) * math.cos(lat1) * math.cos(lat2);
    final c = 2 * math.atan2(math.sqrt(a), math.sqrt(1 - a));
    return r * c;
  }

  static double _degToRad(double deg) => deg * (math.pi / 180.0);

  static String _formatDistance(double meters) {
    if (meters < 1000) {
      return '${meters.round()} Meter';
    } else {
      final km = meters / 1000.0;
      return '${km.toStringAsFixed(1)} km';
    }
  }
}

class RoadRoutingService {
  final Dio _dio;

  RoadRoutingService({Dio? dio})
      : _dio = dio ??
            Dio(
              BaseOptions(
                connectTimeout: const Duration(seconds: 6),
                receiveTimeout: const Duration(seconds: 6),
              ),
            );

  /// Mengambil rute jalan berkelok yang akurat dari OSRM Driving Engine.
  /// Otomatis mengikuti lekukan dan tikungan jalan raya (snap to road curve)
  /// dan menghasilkan estimasi panjang fisik jalan yang presisi.
  Future<RoadRouteResult> getRoadRoute(LatLng start, LatLng end) async {
    try {
      final url =
          'https://router.project-osrm.org/route/v1/driving/'
          '${start.longitude},${start.latitude};${end.longitude},${end.latitude}'
          '?overview=full&geometries=geojson';

      final response = await _dio.get(
        url,
        options: Options(
          headers: {'User-Agent': 'AduanKolakaUtaraApp/1.0'},
        ),
      );

      if (response.statusCode == 200 && response.data != null) {
        final data = response.data;
        if (data['code'] == 'Ok' &&
            data['routes'] != null &&
            (data['routes'] as List).isNotEmpty) {
          final firstRoute = data['routes'][0];
          final double distance = (firstRoute['distance'] as num?)?.toDouble() ?? 0.0;
          final geometry = firstRoute['geometry'];

          if (geometry != null && geometry['coordinates'] != null) {
            final List rawCoords = geometry['coordinates'];
            final List<LatLng> routePoints = rawCoords.map((coord) {
              final double lon = (coord[0] as num).toDouble();
              final double lat = (coord[1] as num).toDouble();
              return LatLng(lat, lon);
            }).toList();

            if (routePoints.length >= 2) {
              return RoadRouteResult(
                points: routePoints,
                distanceMeters: distance,
                formattedDistance: RoadRouteResult._formatDistance(distance),
                isSnappedToRoad: true,
              );
            }
          }
        }
      }
    } catch (e) {
      debugPrint('RoadRoutingService OSRM error or offline: $e');
    }

    // Fallback: Haversine dengan faktor kelokan (winding road tortuosity factor)
    return RoadRouteResult.direct(start: start, end: end);
  }
}
