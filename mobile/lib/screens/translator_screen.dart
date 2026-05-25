// lib/screens/translator_screen.dart
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:path_provider/path_provider.dart';
import 'package:audioplayers/audioplayers.dart';
import 'package:record/record.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:animate_do/animate_do.dart';
import '../theme/app_theme.dart';
import '../services/api_service.dart';
import '../services/history_service.dart';
import '../models/translation_entry.dart';
import 'history_screen.dart';

class TranslatorScreen extends StatefulWidget {
  const TranslatorScreen({super.key});

  @override
  State<TranslatorScreen> createState() => _TranslatorScreenState();
}

class _TranslatorScreenState extends State<TranslatorScreen>
    with TickerProviderStateMixin {
  // Controllers
  final TextEditingController _inputController = TextEditingController();
  final AudioPlayer _audioPlayer = AudioPlayer();
  final AudioRecorder _recorder = AudioRecorder();

  // State
  String _translatedText = '';
  bool _isTranslating = false;
  bool _isRecording = false;
  bool _isPlayingTTS = false;
  bool _isLoadingTTS = false;
  String? _errorMessage;
  bool _serverOnline = false;

  // Animation controllers
  late AnimationController _pulseController;
  late AnimationController _waveController;

  @override
  void initState() {
    super.initState();
    _pulseController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1000),
    )..repeat(reverse: true);
    _waveController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1500),
    );
    _checkServer();
  }

  @override
  void dispose() {
    _inputController.dispose();
    _audioPlayer.dispose();
    _recorder.dispose();
    _pulseController.dispose();
    _waveController.dispose();
    super.dispose();
  }

  Future<void> _checkServer() async {
    final online = await ApiService.checkServerHealth();
    if (mounted) setState(() => _serverOnline = online);
  }

  // ─────────────────────────────────────────────
  // TRADUCCIÓN DE TEXTO
  // ─────────────────────────────────────────────
  Future<void> _translateText() async {
    final text = _inputController.text.trim();
    if (text.isEmpty) return;

    setState(() {
      _isTranslating = true;
      _errorMessage = null;
      _translatedText = '';
    });

    try {
      final result = await ApiService.translateText(text: text);
      final translated = result['translated_text'] as String;

      setState(() => _translatedText = translated);

      // Guardar en historial
      await HistoryService.saveEntry(TranslationEntry(
        originalText: text,
        translatedText: translated,
        timestamp: DateTime.now(),
        type: 'text',
      ));
    } catch (e) {
      setState(() => _errorMessage = e.toString().replaceAll('Exception: ', ''));
    } finally {
      setState(() => _isTranslating = false);
    }
  }

  // ─────────────────────────────────────────────
  // RECONOCIMIENTO DE VOZ
  // ─────────────────────────────────────────────
  Future<void> _toggleRecording() async {
    if (_isRecording) {
      await _stopRecordingAndTranslate();
    } else {
      await _startRecording();
    }
  }

  Future<void> _startRecording() async {
    final status = await Permission.microphone.request();
    if (!status.isGranted) {
      setState(() => _errorMessage = 'Se necesita permiso de micrófono.');
      return;
    }

    final dir = await getTemporaryDirectory();
    final path = '${dir.path}/rec_${DateTime.now().millisecondsSinceEpoch}.wav';

    await _recorder.start(
      const RecordConfig(encoder: AudioEncoder.wav, sampleRate: 16000),
      path: path,
    );

    _waveController.repeat();
    setState(() {
      _isRecording = true;
      _errorMessage = null;
    });
  }

  Future<void> _stopRecordingAndTranslate() async {
    final path = await _recorder.stop();
    _waveController.stop();
    setState(() => _isRecording = false);

    if (path == null) return;

    setState(() {
      _isTranslating = true;
      _translatedText = '';
      _errorMessage = null;
    });

    try {
      final audioFile = File(path);
      final result = await ApiService.speechToText(audioFile);

      final transcription = result['transcription'] as String;
      final translation = result['translation'] as String;

      setState(() {
        _inputController.text = transcription;
        _translatedText = translation;
      });

      await HistoryService.saveEntry(TranslationEntry(
        originalText: transcription,
        translatedText: translation,
        timestamp: DateTime.now(),
        type: 'voice',
      ));

      // Limpiar archivo temporal
      if (audioFile.existsSync()) audioFile.deleteSync();
    } catch (e) {
      setState(() => _errorMessage = e.toString().replaceAll('Exception: ', ''));
    } finally {
      setState(() => _isTranslating = false);
    }
  }

  // ─────────────────────────────────────────────
  // SÍNTESIS DE VOZ (TTS)
  // ─────────────────────────────────────────────
  Future<void> _speakTranslation() async {
    if (_translatedText.isEmpty || _isLoadingTTS) return;

    setState(() {
      _isLoadingTTS = true;
      _errorMessage = null;
    });

    try {
      final bytes = await ApiService.textToSpeech(_translatedText);
      final dir = await getTemporaryDirectory();
      final file = File('${dir.path}/tts_output.wav');
      await file.writeAsBytes(bytes);

      setState(() {
        _isPlayingTTS = true;
        _isLoadingTTS = false;
      });

      await _audioPlayer.play(DeviceFileSource(file.path));
      _audioPlayer.onPlayerComplete.listen((_) {
        if (mounted) setState(() => _isPlayingTTS = false);
      });
    } catch (e) {
      setState(() {
        _errorMessage = e.toString().replaceAll('Exception: ', '');
        _isLoadingTTS = false;
      });
    }
  }

  void _clearAll() {
    _inputController.clear();
    setState(() {
      _translatedText = '';
      _errorMessage = null;
    });
  }

  // ─────────────────────────────────────────────
  // UI
  // ─────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.background,
      body: SafeArea(
        child: CustomScrollView(
          slivers: [
            _buildAppBar(),
            SliverPadding(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              sliver: SliverList(
                delegate: SliverChildListDelegate([
                  const SizedBox(height: 8),
                  _buildServerStatus(),
                  const SizedBox(height: 20),
                  _buildLanguageBar(),
                  const SizedBox(height: 20),
                  _buildInputCard(),
                  const SizedBox(height: 16),
                  _buildTranslateButton(),
                  const SizedBox(height: 16),
                  if (_isTranslating) _buildLoadingIndicator(),
                  if (_errorMessage != null) _buildErrorCard(),
                  if (_translatedText.isNotEmpty && !_isTranslating)
                    _buildResultCard(),
                  const SizedBox(height: 30),
                ]),
              ),
            ),
          ],
        ),
      ),
      floatingActionButton: _buildMicFAB(),
    );
  }

  Widget _buildAppBar() {
    return SliverAppBar(
      backgroundColor: AppTheme.background,
      pinned: true,
      expandedHeight: 80,
      flexibleSpace: FlexibleSpaceBar(
        centerTitle: true,
        title: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 32,
              height: 32,
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [AppTheme.primary, AppTheme.secondary],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(Icons.translate, size: 18, color: Colors.white),
            ),
            const SizedBox(width: 10),
            const Text(
              'LNT-IA Aimara',
              style: TextStyle(
                color: AppTheme.textPrimary,
                fontSize: 18,
                fontWeight: FontWeight.w700,
              ),
            ),
          ],
        ),
      ),
      actions: [
        IconButton(
          icon: const Icon(Icons.history_rounded, color: AppTheme.textPrimary),
          onPressed: () => Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const HistoryScreen()),
          ),
        ),
      ],
    );
  }

  Widget _buildServerStatus() {
    return GestureDetector(
      onTap: _checkServer,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 300),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
        decoration: BoxDecoration(
          color: _serverOnline
              ? AppTheme.success.withOpacity(0.15)
              : AppTheme.error.withOpacity(0.15),
          borderRadius: BorderRadius.circular(30),
          border: Border.all(
            color: _serverOnline ? AppTheme.success : AppTheme.error,
            width: 1,
          ),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            AnimatedBuilder(
              animation: _pulseController,
              builder: (_, __) => Container(
                width: 8,
                height: 8,
                decoration: BoxDecoration(
                  color: _serverOnline
                      ? AppTheme.success
                          .withOpacity(0.5 + 0.5 * _pulseController.value)
                      : AppTheme.error,
                  shape: BoxShape.circle,
                ),
              ),
            ),
            const SizedBox(width: 8),
            Text(
              _serverOnline ? 'Servidor IA conectado' : 'Servidor desconectado — toca para reintentar',
              style: TextStyle(
                color: _serverOnline ? AppTheme.success : AppTheme.error,
                fontSize: 12,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildLanguageBar() {
    return FadeInDown(
      duration: const Duration(milliseconds: 500),
      child: Container(
        padding: const EdgeInsets.all(4),
        decoration: BoxDecoration(
          color: AppTheme.surfaceVariant,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppTheme.cardBorder),
        ),
        child: Row(
          children: [
            _buildLangChip('🇪🇸', 'Español', true),
            const Expanded(
              child: Center(
                child: Icon(Icons.arrow_forward_rounded,
                    color: AppTheme.primary, size: 20),
              ),
            ),
            _buildLangChip('🏔️', 'Aimara', false),
          ],
        ),
      ),
    );
  }

  Widget _buildLangChip(String flag, String name, bool isSource) {
    return Expanded(
      flex: 2,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
        decoration: BoxDecoration(
          gradient: isSource
              ? const LinearGradient(
                  colors: [AppTheme.primary, AppTheme.primaryDark],
                )
              : const LinearGradient(
                  colors: [Color(0xFF00A080), AppTheme.secondary],
                ),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(flag, style: const TextStyle(fontSize: 18)),
            const SizedBox(width: 6),
            Text(
              name,
              style: const TextStyle(
                color: Colors.white,
                fontWeight: FontWeight.w600,
                fontSize: 14,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInputCard() {
    return FadeInUp(
      duration: const Duration(milliseconds: 500),
      child: Container(
        decoration: BoxDecoration(
          color: AppTheme.surfaceVariant,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: AppTheme.cardBorder),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 0),
              child: Row(
                children: [
                  const Text(
                    'Texto en Español',
                    style: TextStyle(
                        color: AppTheme.textSecondary,
                        fontSize: 12,
                        fontWeight: FontWeight.w600),
                  ),
                  const Spacer(),
                  if (_inputController.text.isNotEmpty)
                    GestureDetector(
                      onTap: _clearAll,
                      child: const Icon(Icons.close_rounded,
                          color: AppTheme.textSecondary, size: 18),
                    ),
                ],
              ),
            ),
            TextField(
              controller: _inputController,
              maxLines: 5,
              minLines: 3,
              style: const TextStyle(
                color: AppTheme.textPrimary,
                fontSize: 16,
                height: 1.5,
              ),
              decoration: const InputDecoration(
                hintText: 'Escribe aquí en español...',
                border: InputBorder.none,
                enabledBorder: InputBorder.none,
                focusedBorder: InputBorder.none,
                contentPadding:
                    EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              ),
              onChanged: (_) => setState(() {}),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(12, 0, 12, 12),
              child: Row(
                children: [
                  Text(
                    '${_inputController.text.length} caracteres',
                    style: const TextStyle(
                        color: AppTheme.textSecondary, fontSize: 11),
                  ),
                  const Spacer(),
                  // Pegar desde portapapeles
                  IconButton(
                    onPressed: () async {
                      final data = await Clipboard.getData('text/plain');
                      if (data?.text != null) {
                        _inputController.text = data!.text!;
                        setState(() {});
                      }
                    },
                    icon: const Icon(Icons.content_paste_rounded,
                        color: AppTheme.textSecondary, size: 18),
                    tooltip: 'Pegar',
                    constraints: const BoxConstraints(),
                    padding: EdgeInsets.zero,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTranslateButton() {
    final hasText = _inputController.text.trim().isNotEmpty;
    return FadeInUp(
      duration: const Duration(milliseconds: 600),
      child: SizedBox(
        width: double.infinity,
        height: 54,
        child: DecoratedBox(
          decoration: BoxDecoration(
            gradient: hasText
                ? const LinearGradient(
                    colors: [AppTheme.primary, AppTheme.primaryDark],
                    begin: Alignment.centerLeft,
                    end: Alignment.centerRight,
                  )
                : LinearGradient(
                    colors: [
                      AppTheme.surfaceVariant,
                      AppTheme.surfaceVariant,
                    ],
                  ),
            borderRadius: BorderRadius.circular(16),
            boxShadow: hasText
                ? [
                    BoxShadow(
                      color: AppTheme.primary.withOpacity(0.4),
                      blurRadius: 20,
                      offset: const Offset(0, 8),
                    )
                  ]
                : [],
          ),
          child: ElevatedButton.icon(
            onPressed: hasText && !_isTranslating ? _translateText : null,
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.transparent,
              shadowColor: Colors.transparent,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16)),
            ),
            icon: const Icon(Icons.translate_rounded, color: Colors.white),
            label: const Text(
              'Traducir al Aimara',
              style: TextStyle(
                color: Colors.white,
                fontSize: 16,
                fontWeight: FontWeight.w700,
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildLoadingIndicator() {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 20),
      child: Column(
        children: [
          const CircularProgressIndicator(
            color: AppTheme.primary,
            strokeWidth: 3,
          ),
          const SizedBox(height: 12),
          Text(
            _isRecording ? 'Procesando audio...' : 'Traduciendo con IA...',
            style: const TextStyle(color: AppTheme.textSecondary, fontSize: 14),
          ),
        ],
      ),
    );
  }

  Widget _buildErrorCard() {
    return FadeInUp(
      child: Container(
        margin: const EdgeInsets.only(bottom: 16),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: AppTheme.error.withOpacity(0.1),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppTheme.error.withOpacity(0.5)),
        ),
        child: Row(
          children: [
            const Icon(Icons.error_outline_rounded,
                color: AppTheme.error, size: 20),
            const SizedBox(width: 10),
            Expanded(
              child: Text(
                _errorMessage!,
                style:
                    const TextStyle(color: AppTheme.error, fontSize: 13),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildResultCard() {
    return FadeInUp(
      duration: const Duration(milliseconds: 400),
      child: Container(
        decoration: BoxDecoration(
          color: AppTheme.surfaceVariant,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: AppTheme.secondary.withOpacity(0.4)),
          boxShadow: [
            BoxShadow(
              color: AppTheme.secondary.withOpacity(0.1),
              blurRadius: 20,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header del resultado
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 14, 12, 8),
              child: Row(
                children: [
                  Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: AppTheme.secondary.withOpacity(0.2),
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(
                          color: AppTheme.secondary.withOpacity(0.5)),
                    ),
                    child: const Text(
                      '🏔️ Aimara',
                      style: TextStyle(
                        color: AppTheme.secondary,
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ),
                  const Spacer(),
                  // Copiar
                  IconButton(
                    onPressed: () {
                      Clipboard.setData(
                          ClipboardData(text: _translatedText));
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                          content: Text('Copiado al portapapeles'),
                          duration: Duration(seconds: 2),
                          behavior: SnackBarBehavior.floating,
                        ),
                      );
                    },
                    icon: const Icon(Icons.copy_rounded,
                        color: AppTheme.textSecondary, size: 18),
                    tooltip: 'Copiar',
                    constraints: const BoxConstraints(),
                    padding: EdgeInsets.zero,
                  ),
                  const SizedBox(width: 8),
                  // Reproducir TTS
                  GestureDetector(
                    onTap: _speakTranslation,
                    child: AnimatedContainer(
                      duration: const Duration(milliseconds: 200),
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: _isPlayingTTS || _isLoadingTTS
                            ? AppTheme.primary.withOpacity(0.3)
                            : AppTheme.primary.withOpacity(0.1),
                        shape: BoxShape.circle,
                        border: Border.all(
                          color: AppTheme.primary.withOpacity(0.5),
                        ),
                      ),
                      child: _isLoadingTTS
                          ? const SizedBox(
                              width: 18,
                              height: 18,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                color: AppTheme.primary,
                              ),
                            )
                          : Icon(
                              _isPlayingTTS
                                  ? Icons.stop_rounded
                                  : Icons.volume_up_rounded,
                              color: AppTheme.primary,
                              size: 18,
                            ),
                    ),
                  ),
                ],
              ),
            ),
            // Texto traducido
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 4, 16, 16),
              child: SelectableText(
                _translatedText,
                style: const TextStyle(
                  color: AppTheme.textPrimary,
                  fontSize: 18,
                  fontWeight: FontWeight.w500,
                  height: 1.6,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMicFAB() {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: GestureDetector(
        onTap: _toggleRecording,
        child: AnimatedBuilder(
          animation: _pulseController,
          builder: (_, child) => Container(
            width: 70,
            height: 70,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              gradient: _isRecording
                  ? LinearGradient(
                      colors: [
                        AppTheme.error,
                        AppTheme.error
                            .withRed(200)
                            .withOpacity(0.5 + 0.5 * _pulseController.value),
                      ],
                    )
                  : const LinearGradient(
                      colors: [AppTheme.primary, AppTheme.primaryDark],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
              boxShadow: [
                BoxShadow(
                  color: (_isRecording ? AppTheme.error : AppTheme.primary)
                      .withOpacity(
                          _isRecording ? 0.3 + 0.3 * _pulseController.value : 0.4),
                  blurRadius: _isRecording ? 25 : 20,
                  spreadRadius: _isRecording ? 4 : 0,
                  offset: const Offset(0, 6),
                ),
              ],
            ),
            child: child,
          ),
          child: Icon(
            _isRecording ? Icons.stop_rounded : Icons.mic_rounded,
            color: Colors.white,
            size: 30,
          ),
        ),
      ),
    );
  }
}
