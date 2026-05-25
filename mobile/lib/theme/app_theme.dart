// lib/theme/app_theme.dart
import 'package:flutter/material.dart';

class AppTheme {
  // Paleta de colores
  static const Color primary = Color(0xFF6C63FF);       // Violeta principal
  static const Color primaryDark = Color(0xFF4A42D6);
  static const Color secondary = Color(0xFF00D4AA);     // Verde agua (acento)
  static const Color background = Color(0xFF0F0F1A);    // Negro profundo
  static const Color surface = Color(0xFF1A1A2E);       // Superficie oscura
  static const Color surfaceVariant = Color(0xFF252540); // Tarjetas
  static const Color cardBorder = Color(0xFF3D3D6B);    // Borde sutil
  static const Color textPrimary = Color(0xFFF0F0FF);
  static const Color textSecondary = Color(0xFF9898C8);
  static const Color error = Color(0xFFFF6B6B);
  static const Color success = Color(0xFF51CF66);

  static ThemeData get darkTheme {
    return ThemeData(
      useMaterial3: true,
      brightness: Brightness.dark,
      colorScheme: ColorScheme.dark(
        primary: primary,
        secondary: secondary,
        surface: surface,
        error: error,
        onPrimary: Colors.white,
        onSecondary: Colors.black,
        onSurface: textPrimary,
      ),
      scaffoldBackgroundColor: background,
      fontFamily: 'SF Pro Display',
      appBarTheme: const AppBarTheme(
        backgroundColor: Colors.transparent,
        elevation: 0,
        centerTitle: true,
        titleTextStyle: TextStyle(
          color: textPrimary,
          fontSize: 20,
          fontWeight: FontWeight.w700,
          letterSpacing: 0.5,
        ),
        iconTheme: IconThemeData(color: textPrimary),
      ),
      cardTheme: CardTheme(
        color: surfaceVariant,
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(20),
          side: BorderSide(color: cardBorder, width: 1),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: surface,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide(color: cardBorder, width: 1),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide(color: cardBorder, width: 1),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: primary, width: 2),
        ),
        hintStyle: const TextStyle(color: textSecondary),
        contentPadding: const EdgeInsets.all(16),
      ),
    );
  }
}
