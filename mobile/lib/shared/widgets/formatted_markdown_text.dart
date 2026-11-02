import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:citizen_complaint_app/shared/theme/app_colors.dart';

/// Widget pembaca teks format Markdown ringan (mendukung **tebal / bold**, baris baru, dan bullet).
/// Menjamin seluruh teks bertanda **teks** dirender tebal (bold) secara proporsional.
class FormattedMarkdownText extends StatelessWidget {
  final String text;
  final TextStyle? style;
  final TextStyle? boldStyle;
  final TextAlign textAlign;
  final int? maxLines;
  final TextOverflow? overflow;

  const FormattedMarkdownText(
    this.text, {
    super.key,
    this.style,
    this.boldStyle,
    this.textAlign = TextAlign.start,
    this.maxLines,
    this.overflow,
  });

  @override
  Widget build(BuildContext context) {
    final defaultStyle = style ??
        GoogleFonts.inter(
          fontSize: 12.5,
          color: AppColors.textPrimary,
          height: 1.45,
        );

    final resolvedBoldStyle = boldStyle ??
        defaultStyle.copyWith(
          fontWeight: FontWeight.w700,
          color: defaultStyle.color ?? AppColors.textPrimary,
        );

    final spans = <InlineSpan>[];
    // Regex menangkap pola **teks tebal** bahkan jika memuat jeda baris (dotAll: true)
    final regex = RegExp(r'\*\*(.*?)\*\*', dotAll: true);
    int lastIndex = 0;

    for (final match in regex.allMatches(text)) {
      if (match.start > lastIndex) {
        spans.add(TextSpan(
          text: text.substring(lastIndex, match.start),
          style: defaultStyle,
        ));
      }

      final boldContent = match.group(1);
      if (boldContent != null && boldContent.isNotEmpty) {
        spans.add(TextSpan(
          text: boldContent,
          style: resolvedBoldStyle,
        ));
      }

      lastIndex = match.end;
    }

    if (lastIndex < text.length) {
      spans.add(TextSpan(
        text: text.substring(lastIndex),
        style: defaultStyle,
      ));
    }

    if (spans.isEmpty) {
      return Text(
        text,
        style: defaultStyle,
        textAlign: textAlign,
        maxLines: maxLines,
        overflow: overflow,
      );
    }

    return Text.rich(
      TextSpan(children: spans),
      textAlign: textAlign,
      maxLines: maxLines,
      overflow: overflow,
    );
  }
}
