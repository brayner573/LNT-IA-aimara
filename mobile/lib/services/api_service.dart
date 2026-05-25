// lib/services/api_service.dart
import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;

class ApiService {
  // ⚠️ Cambia esta IP por la IP local de tu PC donde corre el servidor FastAPI
  // En el emulador de Android usa: 10.0.2.2
  // En un dispositivo físico usa: la IP de tu PC en la red local (ej: 192.168.1.100)
  static const String _baseUrl = 'http://10.0.2.2:8000';

  /// Traduce texto de Español a Aimara usando NLLB-200 + LoRA
  static Future<Map<String, dynamic>> translateText({
    required String text,
    String sourceLang = 'spa_Latn',
    String targetLang = 'ayr_Latn',
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$_baseUrl/api/translate'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'text': text,
          'source_lang': sourceLang,
          'target_lang': targetLang,
        }),
      ).timeout(const Duration(seconds: 30));

      if (response.statusCode == 200) {
        return jsonDecode(utf8.decode(response.bodyBytes));
      } else {
        throw Exception('Error del servidor: ${response.statusCode}');
      }
    } on SocketException {
      throw Exception('Sin conexión al servidor. Verifica que el backend esté corriendo.');
    } on Exception catch (e) {
      throw Exception('Error de traducción: $e');
    }
  }

  /// Envía audio WAV y recibe transcripción + traducción
  static Future<Map<String, dynamic>> speechToText(File audioFile) async {
    try {
      final request = http.MultipartRequest(
        'POST',
        Uri.parse('$_baseUrl/api/speech-to-text'),
      );
      request.files.add(
        await http.MultipartFile.fromPath('file', audioFile.path),
      );

      final streamedResponse = await request.send()
          .timeout(const Duration(seconds: 60));
      final response = await http.Response.fromStream(streamedResponse);

      if (response.statusCode == 200) {
        return jsonDecode(utf8.decode(response.bodyBytes));
      } else {
        throw Exception('Error del servidor: ${response.statusCode}');
      }
    } on SocketException {
      throw Exception('Sin conexión al servidor.');
    }
  }

  /// Convierte texto en Aimara a audio WAV (TTS)
  static Future<List<int>> textToSpeech(String text) async {
    try {
      final response = await http.post(
        Uri.parse('$_baseUrl/api/text-to-speech'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'text': text}),
      ).timeout(const Duration(seconds: 30));

      if (response.statusCode == 200) {
        return response.bodyBytes;
      } else {
        throw Exception('Error TTS: ${response.statusCode}');
      }
    } on SocketException {
      throw Exception('Sin conexión al servidor.');
    }
  }

  /// Verifica si el servidor está disponible
  static Future<bool> checkServerHealth() async {
    try {
      final response = await http.get(
        Uri.parse('$_baseUrl/'),
      ).timeout(const Duration(seconds: 5));
      return response.statusCode == 200;
    } catch (_) {
      return false;
    }
  }
}
