// lib/main.dart
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'screens/translator_screen.dart';
import 'theme/app_theme.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  SystemChrome.setPreferredOrientations([
    DeviceOrientation.portraitUp,
    DeviceOrientation.portraitDown,
  ]);
  runApp(const LntIaApp());
}

class LntIaApp extends StatelessWidget {
  const LntIaApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'LNT-IA Traductor Aimara',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.darkTheme,
      home: const TranslatorScreen(),
    );
  }
}
