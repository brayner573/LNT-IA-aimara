// lib/models/translation_entry.dart

class TranslationEntry {
  final String originalText;
  final String translatedText;
  final DateTime timestamp;
  final String type; // 'text' | 'voice'

  const TranslationEntry({
    required this.originalText,
    required this.translatedText,
    required this.timestamp,
    required this.type,
  });

  Map<String, dynamic> toJson() => {
        'originalText': originalText,
        'translatedText': translatedText,
        'timestamp': timestamp.toIso8601String(),
        'type': type,
      };

  factory TranslationEntry.fromJson(Map<String, dynamic> json) =>
      TranslationEntry(
        originalText: json['originalText'] as String,
        translatedText: json['translatedText'] as String,
        timestamp: DateTime.parse(json['timestamp'] as String),
        type: json['type'] as String,
      );
}
