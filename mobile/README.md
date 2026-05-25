# 📱 LNT-IA Mobile — App Flutter Traductor Español-Aimara

<p align="center">
  <img src="https://img.shields.io/badge/Flutter-3.x-02569B?style=for-the-badge&logo=flutter"/>
  <img src="https://img.shields.io/badge/Dart-3.x-0175C2?style=for-the-badge&logo=dart"/>
  <img src="https://img.shields.io/badge/Android-5.0%2B-3DDC84?style=for-the-badge&logo=android"/>
  <img src="https://img.shields.io/badge/iOS-13%2B-000000?style=for-the-badge&logo=apple"/>
</p>

App móvil para el **Traductor Neuronal Español → Aimara** con soporte completo de voz. Se conecta al backend FastAPI del proyecto [LNT-IA](https://github.com/brayner573/LNT-IA-aimara) que corre los modelos de IA localmente.

---

## ✨ Funciones

| Función | Descripción |
|---|---|
| 📝 **Traducción de texto** | Escribe en español y obtén la traducción en Aimara |
| 🎤 **Reconocimiento de voz** | Habla en español y el sistema transcribe y traduce |
| 🔊 **Síntesis de voz** | Escucha la traducción en Aimara con Meta MMS TTS |
| 📋 **Historial** | Guarda todas tus traducciones localmente |
| 📋 **Copiar / Pegar** | Copia traducciones con un toque |

---

## 🏗️ Arquitectura

```
App Flutter (Móvil)
      │
      │  HTTP REST (Wi-Fi red local)
      │
Backend FastAPI (PC con GPU)
      │
      ├── NLLB-200 + LoRA → Traducción
      ├── Whisper V3 Turbo → Voz a Texto
      └── Meta MMS TTS   → Texto a Voz
```

La app **no corre los modelos de IA localmente** — se conecta al servidor FastAPI que corre en tu PC con GPU. Ambos dispositivos deben estar en la misma red Wi-Fi.

---

## 📦 Requisitos previos

1. **Flutter SDK 3.x** instalado — [Instalar Flutter](https://docs.flutter.dev/get-started/install)
2. **Android Studio** o **Xcode** (para iOS)
3. El **servidor FastAPI** del proyecto `LNT-IA-aimara` corriendo en tu PC

---

## ⚙️ Instalación paso a paso

### Paso 1 — Instalar Flutter

**Windows:**
```powershell
# Descarga el SDK desde https://docs.flutter.dev/get-started/install/windows
# Descomprime en C:\flutter
# Agrega C:\flutter\bin al PATH del sistema

flutter doctor   # Verifica la instalación
```

**Linux:**
```bash
sudo snap install flutter --classic
flutter doctor
```

**macOS:**
```bash
brew install flutter
flutter doctor
```

---

### Paso 2 — Clonar este repositorio

```bash
git clone https://github.com/brayner573/LNT-IA-aimara.git
cd LNT-IA-aimara/LNT-IA-Mobile
```

---

### Paso 3 — Instalar dependencias Flutter

```bash
flutter pub get
```

---

### Paso 4 — Configurar la IP del servidor

Abre el archivo `lib/services/api_service.dart` y cambia la URL base:

```dart
// Para emulador Android (10.0.2.2 apunta al localhost de tu PC):
static const String _baseUrl = 'http://10.0.2.2:8000';

// Para dispositivo físico (usa la IP local de tu PC):
// Encuéntrala con: ipconfig (Windows) o ip addr (Linux)
static const String _baseUrl = 'http://192.168.1.XXX:8000';
```

> **¿Cómo saber mi IP?**
> - **Windows:** Abre cmd → escribe `ipconfig` → busca "Dirección IPv4"
> - **Linux/macOS:** Escribe `ip addr` o `ifconfig`

---

### Paso 5 — Iniciar el servidor FastAPI (en tu PC)

Antes de usar la app, asegúrate de que el backend esté corriendo:

```bash
# En el directorio del proyecto principal LNT-IA-aimara
cd ..
.venv\Scripts\Activate.ps1   # Windows
python app.py
```

Verifica que veas: `[+] ¡TODOS LOS MODELOS LISTOS PARA INFERENCIA!`

---

### Paso 6 — Ejecutar la app

**En emulador Android:**
```bash
flutter emulators --launch <nombre_emulador>
flutter run
```

**En dispositivo físico (Android):**
1. Activa **Opciones de desarrollador** en tu teléfono
2. Activa **Depuración USB**
3. Conecta el teléfono con cable USB
4. Ejecuta:
```bash
flutter devices    # Lista los dispositivos disponibles
flutter run        # Instala y ejecuta la app
```

**En iOS (requiere macOS y Xcode):**
```bash
open ios/Runner.xcworkspace   # Configura el equipo de desarrollo en Xcode
flutter run
```

---

### Paso 7 — Compilar APK para Android

```bash
# APK de release (para compartir o instalar manualmente)
flutter build apk --release

# El APK quedará en:
# build/app/outputs/flutter-apk/app-release.apk
```

Para instalar el APK en tu teléfono:
```bash
flutter install
```

O transfiere el APK al teléfono y ábrelo (necesitas permitir "Instalar apps de fuentes desconocidas").

---

## 📁 Estructura del proyecto

```
LNT-IA-Mobile/
│
├── lib/
│   ├── main.dart                    ← Punto de entrada
│   ├── theme/
│   │   └── app_theme.dart           ← Paleta de colores y tema oscuro
│   ├── models/
│   │   └── translation_entry.dart   ← Modelo de datos del historial
│   ├── services/
│   │   ├── api_service.dart         ← Cliente HTTP → Backend FastAPI
│   │   └── history_service.dart     ← Historial local (SharedPreferences)
│   └── screens/
│       ├── translator_screen.dart   ← Pantalla principal
│       └── history_screen.dart      ← Pantalla de historial
│
├── android/
│   └── app/src/main/
│       └── AndroidManifest.xml      ← Permisos (internet, micrófono)
│
└── pubspec.yaml                     ← Dependencias Flutter
```

---

## 🔧 Dependencias principales

| Paquete | Versión | Uso |
|---|---|---|
| `http` | ^1.2.0 | Llamadas HTTP al backend FastAPI |
| `record` | ^5.1.2 | Grabación de audio del micrófono |
| `audioplayers` | ^6.1.0 | Reproducción del audio TTS |
| `path_provider` | ^2.1.3 | Archivos temporales de audio |
| `shared_preferences` | ^2.3.1 | Historial persistente local |
| `permission_handler` | ^11.3.1 | Permisos de micrófono en Android/iOS |
| `animate_do` | ^3.3.4 | Animaciones de entrada de UI |

---

## 🐛 Solución de problemas

### La app dice "Servidor desconectado"
- Verifica que el servidor FastAPI esté corriendo: `python app.py`
- Verifica que ambos dispositivos estén en la **misma red Wi-Fi**
- Si usas emulador Android, la IP debe ser `10.0.2.2` (no `localhost`)
- Si usas dispositivo físico, usa la IP de tu PC (`ipconfig`)

### Error de micrófono
- La primera vez, acepta el permiso de micrófono cuando la app lo solicite
- Si lo rechazaste, ve a Configuración → Apps → LNT-IA Aimara → Permisos → Micrófono

### `flutter doctor` muestra errores
- Para Android: instala Android Studio y acepta las licencias con `flutter doctor --android-licenses`
- Para iOS: instala Xcode desde la App Store

### El APK no se instala en el teléfono
- Activa **"Instalar apps de fuentes desconocidas"** en Configuración → Seguridad

---

## 📄 Licencia

Uso académico e investigativo. Backend basado en modelos de Meta AI y OpenAI distribuidos bajo sus respectivas licencias en HuggingFace Hub.

---

## 🔗 Repositorio principal

👉 [github.com/brayner573/LNT-IA-aimara](https://github.com/brayner573/LNT-IA-aimara)
