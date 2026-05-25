// lib/services/history_service.dart
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/translation_entry.dart';

class HistoryService {
  static const String _key = 'translation_history';
  static const int _maxEntries = 100;

  /// Guarda una traducción en el historial local
  static Future<void> saveEntry(TranslationEntry entry) async {
    final prefs = await SharedPreferences.getInstance();
    final List<String> raw = prefs.getStringList(_key) ?? [];

    // Agregar al inicio (más reciente primero)
    raw.insert(0, jsonEncode(entry.toJson()));

    // Limitar a 100 entradas
    final trimmed = raw.take(_maxEntries).toList();
    await prefs.setStringList(_key, trimmed);
  }

  /// Obtiene todo el historial
  static Future<List<TranslationEntry>> getHistory() async {
    final prefs = await SharedPreferences.getInstance();
    final List<String> raw = prefs.getStringList(_key) ?? [];
    return raw
        .map((e) => TranslationEntry.fromJson(jsonDecode(e)))
        .toList();
  }

  /// Elimina una entrada específica por índice
  static Future<void> deleteEntry(int index) async {
    final prefs = await SharedPreferences.getInstance();
    final List<String> raw = prefs.getStringList(_key) ?? [];
    if (index >= 0 && index < raw.length) {
      raw.removeAt(index);
      await prefs.setStringList(_key, raw);
    }
  }

  /// Borra todo el historial
  static Future<void> clearHistory() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_key);
  }
}
