class ApiEndpoints {
  // Auth
  static const String register = '/auth/register';
  static const String login = '/auth/login';
  static const String logout = '/auth/logout';
  static const String me = '/me';

  // Complaints
  static const String complaints = '/complaints';
  static String complaintDetail(int id) => '/complaints/$id';
  static String complaintSupports(int id) => '/complaints/$id/supports';
  static String complaintTimeline(int id) => '/complaints/$id/timeline';

  // AI & Duplicate
  static const String aiClassify = '/ai/classify-image';
  static String duplicateCandidates(int id) => '/complaints/$id/duplicate-candidates';

  // Dashboard & GIS
  static const String dashboardSummary = '/dashboard/summary';
  static const String dashboardMap = '/dashboard/map';

  // Chatbot
  static const String chatSessions = '/chat/sessions';
  static String chatMessages(String sessionId) => '/chat/sessions/$sessionId/messages';
}
