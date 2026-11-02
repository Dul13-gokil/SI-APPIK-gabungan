class PhoneValidationResult {
  final bool isValid;
  final String? message;
  final String normalized;
  final int digitCount;

  const PhoneValidationResult({
    required this.isValid,
    this.message,
    required this.normalized,
    required this.digitCount,
  });
}

class PhoneValidator {
  /// Validasi nomor telepon Indonesia:
  /// Panjang 10 - 13 digit
  /// Diawali 08, 628, atau +628
  static PhoneValidationResult validate(String? input, {bool isRequired = true}) {
    if (input == null || input.trim().isEmpty) {
      if (isRequired) {
        return const PhoneValidationResult(
          isValid: false,
          message: 'Nomor telepon wajib diisi',
          normalized: '',
          digitCount: 0,
        );
      }
      return const PhoneValidationResult(
        isValid: true,
        message: null,
        normalized: '',
        digitCount: 0,
      );
    }

    final trimmed = input.trim();
    // Karakter yang diizinkan hanya digit dan opsional diawali '+'
    final cleanDigits = trimmed.replaceAll(RegExp(r'[^0-9+]'), '');
    final hasInvalidChars = !RegExp(r'^\+?[0-9]+$').hasMatch(cleanDigits);
    if (hasInvalidChars) {
      return const PhoneValidationResult(
        isValid: false,
        message: 'Nomor telepon hanya boleh berisi angka.',
        normalized: '',
        digitCount: 0,
      );
    }

    // Normalisasi ke format 08...
    String normalized = cleanDigits;
    if (normalized.startsWith('+62')) {
      normalized = '0${normalized.substring(3)}';
    } else if (normalized.startsWith('62')) {
      normalized = '0${normalized.substring(2)}';
    }

    if (!normalized.startsWith('08')) {
      return PhoneValidationResult(
        isValid: false,
        message: 'Nomor HP Indonesia harus diawali 08 atau +628',
        normalized: normalized,
        digitCount: normalized.length,
      );
    }

    final len = normalized.length;
    if (len < 10) {
      return PhoneValidationResult(
        isValid: false,
        message: 'Nomor terlalu pendek ($len/10 digit)',
        normalized: normalized,
        digitCount: len,
      );
    }

    if (len > 13) {
      return PhoneValidationResult(
        isValid: false,
        message: 'Nomor terlalu panjang ($len/13 digit)',
        normalized: normalized,
        digitCount: len,
      );
    }

    return PhoneValidationResult(
      isValid: true,
      message: '✓ Format nomor valid ($len digit)',
      normalized: normalized,
      digitCount: len,
    );
  }
}
