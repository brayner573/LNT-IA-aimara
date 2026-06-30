@extends('layouts.app')

@section('title', 'Traductor de Voz SOTA - LNT-IA')

@section('styles')
<style>
    /* Grid de traducción */
    .translator-grid {
        display: grid;
        grid-template-columns: 1fr 120px 1fr;
        gap: 1.5rem;
        align-items: stretch;
        margin-top: 1rem;
    }

    @media (max-width: 900px) {
        .translator-grid {
            grid-template-columns: 1fr;
        }
        .controls-column {
            order: 3;
            flex-direction: row !important;
            height: auto !important;
            padding: 1rem !important;
        }
    }

    .lang-box {
        background: rgba(13, 15, 24, 0.4);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
        transition: var(--transition);
        position: relative;
    }

    .lang-box:focus-within {
        border-color: var(--primary);
        box-shadow: 0 0 25px rgba(139, 92, 246, 0.12);
        background: rgba(13, 15, 24, 0.55);
    }

    .lang-box.target-box:focus-within {
        border-color: var(--accent);
        box-shadow: 0 0 25px rgba(6, 182, 212, 0.12);
    }

    .box-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 0.75rem;
    }

    .lang-title {
        font-family: var(--font-title);
        font-weight: 700;
        font-size: 1.1rem;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .source-box .lang-title i {
        color: var(--primary);
    }

    .target-box .lang-title i {
        color: var(--accent);
    }

    .text-area {
        width: 100%;
        min-height: 250px;
        background: transparent;
        border: none;
        outline: none;
        color: #fff;
        font-family: var(--font-body);
        font-size: 1.1rem;
        line-height: 1.6;
        resize: none;
    }

    .text-area::placeholder {
        color: var(--text-muted);
    }

    .box-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid var(--border-color);
        padding-top: 0.75rem;
        color: var(--text-muted);
        font-size: 0.85rem;
    }

    .icon-btn {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--border-color);
        color: var(--text-muted);
        padding: 0.5rem 0.85rem;
        border-radius: 10px;
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
    }

    .icon-btn:hover {
        background: rgba(255, 255, 255, 0.08);
        color: #fff;
        border-color: var(--glass-border);
    }

    .icon-btn:active {
        transform: scale(0.96);
    }

    /* Columna Central de Control */
    .controls-column {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        gap: 1.5rem;
        padding: 2rem 0;
    }

    .swap-btn {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        background: rgba(139, 92, 246, 0.1);
        border: 1px solid rgba(139, 92, 246, 0.2);
        color: var(--primary);
        font-size: 1.2rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
    }

    .swap-btn:hover {
        background: var(--primary);
        color: #fff;
        box-shadow: 0 0 20px var(--primary-glow);
        transform: rotate(180deg);
    }

    /* Mic Button Premium SOTA */
    .mic-container {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .mic-btn {
        width: 76px;
        height: 76px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
        border: none;
        color: #fff;
        font-size: 1.8rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
        box-shadow: 0 0 30px rgba(139, 92, 246, 0.4);
        z-index: 10;
    }

    .mic-btn:hover {
        transform: scale(1.08);
        box-shadow: 0 0 40px rgba(6, 182, 212, 0.6);
    }

    .mic-btn.recording {
        background: #ef4444;
        animation: pulse-recording 1.5s infinite;
        box-shadow: 0 0 35px rgba(239, 68, 68, 0.6);
    }

    @keyframes pulse-recording {
        0% { transform: scale(1); }
        50% { transform: scale(1.06); }
        100% { transform: scale(1); }
    }

    /* Ondas de Audio Premium */
    .audio-wave-wrapper {
        display: none;
        align-items: center;
        justify-content: center;
        gap: 4px;
        height: 40px;
        margin-top: 1rem;
        padding: 0.5rem;
        background: rgba(255, 255, 255, 0.02);
        border-radius: 12px;
        border: 1px solid var(--border-color);
    }

    .audio-bar {
        width: 4px;
        height: 10px;
        background: var(--accent);
        border-radius: 20px;
        animation: dance 1s ease infinite alternate;
    }

    .audio-bar:nth-child(2) { animation-delay: 0.1s; height: 25px; background: var(--primary); }
    .audio-bar:nth-child(3) { animation-delay: 0.2s; height: 15px; }
    .audio-bar:nth-child(4) { animation-delay: 0.3s; height: 35px; background: var(--primary); }
    .audio-bar:nth-child(5) { animation-delay: 0.4s; height: 20px; }
    .audio-bar:nth-child(6) { animation-delay: 0.5s; height: 30px; background: var(--primary); }
    .audio-bar:nth-child(7) { animation-delay: 0.6s; height: 12px; }

    @keyframes dance {
        0% { transform: scaleY(0.3); }
        100% { transform: scaleY(1); }
    }

    .status-text {
        font-family: var(--font-title);
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--text-muted);
        text-align: center;
        margin-top: 0.5rem;
    }

    .char-count {
        font-variant-numeric: tabular-nums;
    }
</style>
@section('content')
<div class="glass-card">
    <div style="text-align: center; margin-bottom: 2rem;">
        <h1 style="font-family: var(--font-title); font-size: 2.2rem; font-weight: 800; background: linear-gradient(135deg, #fff 40%, var(--text-muted) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            Traductor Inteligente de Voz
        </h1>
        <p style="color: var(--text-muted); margin-top: 0.5rem;">
            Inferencia neuronal bidireccional en tiempo real Español ⇄ Aimara con Whisper ASR y Meta MMS TTS
        </p>
    </div>

    <!-- Grid del Traductor -->
    <div class="translator-grid">
        
        <!-- Caja Fuente (Español) -->
        <div class="lang-box source-box" id="sourceBox">
            <div class="box-header">
                <span class="lang-title" id="sourceLangTitle">
                    <i class="fa-solid fa-earth-americas"></i> Español
                </span>
                <span style="background: rgba(139, 92, 246, 0.1); color: var(--primary); font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 20px;">
                    Fuente
                </span>
            </div>
            <textarea class="text-area" id="sourceInput" placeholder="Escribe el texto a traducir aquí o pulsa el micrófono para hablar..."></textarea>
            <div class="box-footer">
                <button class="icon-btn" id="btnPlaySource" style="display:none;">
                    <i class="fa-solid fa-volume-high"></i> Escuchar
                </button>
                <button class="icon-btn" id="btnCopySource">
                    <i class="fa-solid fa-copy"></i> Copiar
                </button>
                <span class="char-count" id="sourceCharCount">0 / 1000</span>
            </div>
        </div>

        <!-- Botones de Control Central -->
        <div class="controls-column">
            <button class="swap-btn" id="btnSwap" title="Intercambiar Idiomas">
                <i class="fa-solid fa-arrows-rotate"></i>
            </button>
            
            <div class="mic-container" style="flex-direction: column; gap: 0.75rem;">
                <button class="mic-btn" id="btnMic" title="Traducir por Voz (Hablar)">
                    <i class="fa-solid fa-microphone"></i>
                </button>
            </div>

            <!-- Selector de Micrófono Premium -->
            <div style="width: 100%; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; margin: 0.25rem 0;">
                <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Elegir Micrófono</span>
                <select id="micSelect" style="background: rgba(13, 15, 24, 0.85); border: 1px solid var(--border-color); color: #fff; padding: 0.45rem 0.65rem; border-radius: 10px; font-family: var(--font-body); font-size: 0.8rem; outline: none; width: 140px; cursor: pointer; transition: var(--transition); text-align: center;">
                    <option value="">Por defecto</option>
                </select>
                <button class="icon-btn" id="btnRefreshMics" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;" title="Recargar lista de micrófonos">
                    <i class="fa-solid fa-rotate"></i> Buscar dispositivos
                </button>
            </div>
            
            <div>
                <div class="audio-wave-wrapper" id="audioWave">
                    <div class="audio-bar"></div>
                    <div class="audio-bar"></div>
                    <div class="audio-bar"></div>
                    <div class="audio-bar"></div>
                    <div class="audio-bar"></div>
                    <div class="audio-bar"></div>
                    <div class="audio-bar"></div>
                </div>
                <div class="status-text" id="statusText">Listo</div>
            </div>
        </div>

        <!-- Caja Destino (Aimara) -->
        <div class="lang-box target-box" id="targetBox">
            <div class="box-header">
                <span class="lang-title" id="targetLangTitle">
                    <i class="fa-solid fa-earth-americas"></i> Aimara
                </span>
                <span style="background: rgba(6, 182, 212, 0.1); color: var(--accent); font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 20px;">
                    Traducción
                </span>
            </div>
            <textarea class="text-area" id="targetOutput" placeholder="La traducción se mostrará aquí de manera instantánea..." readonly></textarea>
            <div class="box-footer">
                <button class="icon-btn" id="btnPlayTarget" style="display:none;">
                    <i class="fa-solid fa-volume-high"></i> Escuchar
                </button>
                <button class="icon-btn" id="btnCopyTarget">
                    <i class="fa-solid fa-copy"></i> Copiar
                </button>
                <span class="char-count" id="targetCharCount">0</span>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    // Variables de configuración de lenguajes
    let sourceLang = "spa_Latn"; // NLLB Code for Spanish
    let targetLang = "ayr_Latn"; // NLLB Code for Aymara
    let isRecording = false;
    let translationTimeout;

    // Web Audio API para grabación robusta de PCM WAV a 16kHz (Whisper Native)
    let audioContext;
    let scriptProcessor;
    let mediaStreamSource;
    let audioBuffers = [];
    let mediaStream;
    let maxRmsSeen = 0;
    const recordingSampleRate = 16000;

    // Elementos del DOM
    const sourceInput = document.getElementById('sourceInput');
    const targetOutput = document.getElementById('targetOutput');
    const sourceLangTitle = document.getElementById('sourceLangTitle');
    const targetLangTitle = document.getElementById('targetLangTitle');
    const sourceCharCount = document.getElementById('sourceCharCount');
    const targetCharCount = document.getElementById('targetCharCount');
    const btnSwap = document.getElementById('btnSwap');
    const btnMic = document.getElementById('btnMic');
    const audioWave = document.getElementById('audioWave');
    const statusText = document.getElementById('statusText');
    const btnPlayTarget = document.getElementById('btnPlayTarget');
    const btnCopySource = document.getElementById('btnCopySource');
    const btnCopyTarget = document.getElementById('btnCopyTarget');

    // FastAPI Server Config
    const fastApiUrl = "http://127.0.0.1:8005/api";

    // 1. Manejo del input de texto con debounce para traducción instantánea SOTA
    sourceInput.addEventListener('input', () => {
        const text = sourceInput.value;
        sourceCharCount.innerText = `${text.length} / 1000`;
        
        // Limpiar temporizador anterior
        clearTimeout(translationTimeout);
        
        if (text.trim() === "") {
            targetOutput.value = "";
            targetCharCount.innerText = "0";
            btnPlayTarget.style.display = "none";
            return;
        }

        statusText.innerText = "Traduciendo...";
        // Esperar 450ms después de escribir para mandar a la GPU
        translationTimeout = setTimeout(performTranslation, 450);
    });

    async function performTranslation() {
        const text = sourceInput.value.trim();
        if (!text) return;

        try {
            const response = await fetch(`${fastApiUrl}/translate`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    text: text,
                    source_lang: sourceLang,
                    target_lang: targetLang
                })
            });

            if (response.ok) {
                const data = await response.json();
                targetOutput.value = data.translated_text;
                targetCharCount.innerText = data.translated_text.length;
                statusText.innerText = "Traducción completada";
                
                // Mostrar botón de escuchar si el idioma destino es Aimara
                if (targetLang === "ayr_Latn") {
                    btnPlayTarget.style.display = "flex";
                } else {
                    btnPlayTarget.style.display = "none";
                }
            } else {
                statusText.innerText = "Error en servidor GPU";
            }
        } catch (e) {
            console.error(e);
            statusText.innerText = "Error de conexión GPU";
        }
    }

    // 2. Intercambio de idiomas
    btnSwap.addEventListener('click', () => {
        const tempLang = sourceLang;
        sourceLang = targetLang;
        targetLang = tempLang;

        const tempText = sourceInput.value;
        sourceInput.value = targetOutput.value;
        targetOutput.value = tempText;

        if (sourceLang === "spa_Latn") {
            sourceLangTitle.innerHTML = `<i class="fa-solid fa-earth-americas"></i> Español`;
            targetLangTitle.innerHTML = `<i class="fa-solid fa-earth-americas"></i> Aimara`;
            sourceInput.placeholder = "Escribe el texto a traducir aquí o pulsa el micrófono para hablar...";
        } else {
            sourceLangTitle.innerHTML = `<i class="fa-solid fa-earth-americas"></i> Aimara`;
            targetLangTitle.innerHTML = `<i class="fa-solid fa-earth-americas"></i> Español`;
            sourceInput.placeholder = "Qillqaña arunaka aka chiqana...";
        }

        sourceCharCount.innerText = `${sourceInput.value.length} / 1000`;
        targetCharCount.innerText = targetOutput.value.length;

        if (sourceInput.value.trim() !== "") {
            performTranslation();
        }
    });

    // 3. Síntesis de voz MMS TTS para Aimara
    btnPlayTarget.addEventListener('click', async () => {
        const text = targetOutput.value.trim();
        if (!text) return;

        statusText.innerText = "Sintetizando voz...";
        btnPlayTarget.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Cargando`;

        try {
            const response = await fetch(`${fastApiUrl}/text-to-speech`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ text: text })
            });

            if (response.ok) {
                const blob = await response.blob();
                const audioUrl = URL.createObjectURL(blob);
                const audio = new Audio(audioUrl);
                audio.play();
                statusText.innerText = "Reproduciendo audio";
            } else {
                statusText.innerText = "Error en síntesis de voz";
            }
        } catch (e) {
            console.error(e);
            statusText.innerText = "Error de conexión GPU";
        } finally {
            btnPlayTarget.innerHTML = `<i class="fa-solid fa-volume-high"></i> Escuchar`;
        }
    });

    // 4. Captura de audio y Speech-to-Speech con grabador PCM WAV directo
    const btnRefreshMics = document.getElementById('btnRefreshMics');
    const micSelect = document.getElementById('micSelect');

    btnRefreshMics.addEventListener('click', () => {
        refreshMicrophoneList();
    });

    // Cargar micrófonos en la carga inicial si hay permiso previo
    window.addEventListener('DOMContentLoaded', () => {
        refreshMicrophoneList();
    });

    async function refreshMicrophoneList() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) {
            console.warn("Enumeración de dispositivos no soportada");
            return;
        }
        try {
            // Solicitar un permiso silencioso inicial si no hay etiquetas para cargar nombres de hardware reales
            let devices = await navigator.mediaDevices.enumerateDevices();
            let hasLabels = devices.some(d => d.kind === 'audioinput' && d.label);
            
            if (!hasLabels) {
                try {
                    const tempStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    tempStream.getTracks().forEach(track => track.stop());
                    devices = await navigator.mediaDevices.enumerateDevices();
                } catch (err) {
                    console.log("No se pudo pre-solicitar permisos de micro:", err);
                }
            }

            const audioDevices = devices.filter(device => device.kind === 'audioinput');
            const currentSelected = micSelect.value;
            
            micSelect.innerHTML = '<option value="">Por defecto</option>';
            
            audioDevices.forEach((device, index) => {
                const option = document.createElement('option');
                option.value = device.deviceId;
                option.text = device.label || `Micrófono ${index + 1} (Sin nombre)`;
                if (device.deviceId === currentSelected) {
                    option.selected = true;
                }
                micSelect.appendChild(option);
            });
        } catch (e) {
            console.error("Error al enumerar micrófonos: ", e);
        }
    }

    btnMic.addEventListener('click', async () => {
        if (!isRecording) {
            startAudioRecording();
        } else {
            stopAudioRecording();
        }
    });

    function updateRealVolumeVisuals(rms) {
        // Mapear el valor RMS (generalmente entre 0 y 0.2 para voz activa) a un multiplicador visual de escala
        const volume = Math.min(100, Math.round(rms * 450));
        const bars = document.querySelectorAll('.audio-bar');
        
        // Alturas base originales de los 7 elementos visuales
        const baseHeights = [10, 25, 15, 35, 20, 30, 12];
        
        bars.forEach((bar, index) => {
            const baseHeight = baseHeights[index];
            // Si hay volumen, escalar la altura dinámicamente con transiciones suaves
            const newHeight = Math.max(3, Math.min(55, baseHeight * (volume / 20 + 0.15)));
            bar.style.height = `${newHeight}px`;
            // Cambiar el color de los picos más altos para un feedback premium
            if (rms > 0.05) {
                bar.style.background = 'var(--accent)';
            } else {
                bar.style.background = index % 2 === 0 ? 'var(--primary)' : 'var(--accent)';
            }
        });
    }

    async function startAudioRecording() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            statusText.innerText = "Micrófono no soportado en este contexto";
            return;
        }

        audioBuffers = [];
        maxRmsSeen = 0;
        try {
            const selectedMicId = micSelect.value;
            const constraints = {
                audio: {
                    deviceId: selectedMicId ? { exact: selectedMicId } : undefined,
                    echoCancellation: false,
                    noiseSuppression: false,
                    autoGainControl: true
                }
            };
            mediaStream = await navigator.mediaDevices.getUserMedia(constraints);
            
            // Refrescar nombres de micrófonos en tiempo real ya con permisos de sesión
            refreshMicrophoneList();
            
            // Intentar inicializar a 16kHz nativos de Whisper directamente para evitar errores de downsampling en JS
            try {
                audioContext = new (window.AudioContext || window.webkitAudioContext)({ sampleRate: 16000 });
            } catch (ctxErr) {
                console.warn("Fallo inicialización nativa 16kHz, usando fallback del hardware:", ctxErr);
                audioContext = new (window.AudioContext || window.webkitAudioContext)();
            }

            if (audioContext.state === 'suspended') {
                await audioContext.resume();
            }
            const originalSampleRate = audioContext.sampleRate;
            console.log("AudioContext inicializado a:", originalSampleRate, "Hz");

            mediaStreamSource = audioContext.createMediaStreamSource(mediaStream);
            
            // Crear el procesador de script y anclarlo a window para prevenir Garbage Collection (GC) en Chrome/Edge
            scriptProcessor = audioContext.createScriptProcessor(4096, 1, 1);
            window.scriptProcessorRef = scriptProcessor;

            scriptProcessor.onaudioprocess = (event) => {
                if (!isRecording) return;
                const inputBuffer = event.inputBuffer.getChannelData(0);
                
                // Almacenar buffer clonado
                audioBuffers.push(new Float32Array(inputBuffer));
                
                // Calcular RMS (Root Mean Square) en tiempo real para visualizar amplitud física
                let sum = 0;
                for (let i = 0; i < inputBuffer.length; i++) {
                    sum += inputBuffer[i] * inputBuffer[i];
                }
                const rms = Math.sqrt(sum / inputBuffer.length);
                if (rms > maxRmsSeen) {
                    maxRmsSeen = rms;
                }
                
                // Actualizar volumen dinámico en el grid
                updateRealVolumeVisuals(rms);
            };

            mediaStreamSource.connect(scriptProcessor);
            scriptProcessor.connect(audioContext.destination);

            isRecording = true;
            btnMic.classList.add('recording');
            audioWave.style.display = "flex";
            
            // Quitar animación estática CSS de dance de las barras de audio para que respondan 100% al volumen real de voz
            document.querySelectorAll('.audio-bar').forEach(bar => {
                bar.style.animation = 'none';
            });

            statusText.innerHTML = `<span style="color:var(--accent); font-weight:700;"><i class="fa-solid fa-microphone"></i> Escuchando...</span> Habla ahora`;
        } catch (e) {
            console.error(e);
            statusText.innerText = "Error: Sin permisos de micrófono";
            alert("No se pudo iniciar la grabación. Por favor, asegúrate de conectar un micrófono y otorgar permisos en la barra de direcciones de tu navegador (haz clic en el candado al lado de la URL y activa el Micrófono). Error: " + e.message);
        }
    }

    function stopAudioRecording() {
        if (isRecording) {
            isRecording = false;
            btnMic.classList.remove('recording');
            audioWave.style.display = "none";
            statusText.innerText = "Procesando audio localmente...";

            const originalSampleRate = audioContext.sampleRate;

            // Desconectar y liberar hardware de micrófono correctamente (evita que el botón rojo siga encendido en el navegador)
            if (scriptProcessor) {
                scriptProcessor.disconnect();
                window.scriptProcessorRef = null;
            }
            if (mediaStreamSource) mediaStreamSource.disconnect();
            if (mediaStream) {
                mediaStream.getTracks().forEach(track => track.stop());
                mediaStream = null;
            }
            if (audioContext) {
                audioContext.close();
            }

            // Restaurar animaciones CSS de dance inactivas
            document.querySelectorAll('.audio-bar').forEach((bar, idx) => {
                bar.style.animation = '';
                bar.style.height = '';
                bar.style.background = '';
            });

            // Diagnóstico visual en UI sobre volumen extremadamente bajo
            console.log("Grabación completada. Amplitud máxima registrada (RMS Peak):", maxRmsSeen);
            if (maxRmsSeen < 0.0035) {
                statusText.innerHTML = `<span style="color:#ef4444; font-weight:700;"><i class="fa-solid fa-triangle-exclamation"></i> ¡Micrófono Silencioso!</span>`;
                alert("⚠️ Se ha detectado una grabación completamente silenciosa. Tu micrófono podría estar apagado, silenciado en Windows o mal seleccionado. Por favor, selecciona otro dispositivo en el selector superior y haz la prueba de nuevo.");
                return;
            }

            // Aplanar buffers flotantes capturados
            let totalLength = 0;
            for (let i = 0; i < audioBuffers.length; i++) {
                totalLength += audioBuffers[i].length;
            }
            const flatBuffer = new Float32Array(totalLength);
            let offset = 0;
            for (let i = 0; i < audioBuffers.length; i++) {
                flatBuffer.set(audioBuffers[i], offset);
                offset += audioBuffers[i].length;
            }

            // Remuestrear los datos flotantes a 16kHz nativos de Whisper si es necesario
            const downsampledBuffer = downsampleBuffer(flatBuffer, originalSampleRate, recordingSampleRate);

            // Codificar a PCM WAV
            const wavBlob = encodeWAV(downsampledBuffer, recordingSampleRate);
            sendAudioToGPU(wavBlob);
        }
    }

    // Algoritmo SOTA de remuestreo lineal de audio en JavaScript
    function downsampleBuffer(buffer, inputSampleRate, outputSampleRate) {
        if (inputSampleRate === outputSampleRate) {
            return buffer;
        }
        const sampleRateRatio = inputSampleRate / outputSampleRate;
        const newLength = Math.round(buffer.length / sampleRateRatio);
        const result = new Float32Array(newLength);
        let offsetResult = 0;
        let offsetBuffer = 0;
        while (offsetResult < result.length) {
            const nextOffsetBuffer = Math.round((offsetResult + 1) * sampleRateRatio);
            let accum = 0, count = 0;
            for (let i = offsetBuffer; i < nextOffsetBuffer && i < buffer.length; i++) {
                accum += buffer[i];
                count++;
            }
            result[offsetResult] = count > 0 ? accum / count : 0;
            offsetResult++;
            offsetBuffer = nextOffsetBuffer;
        }
        return result;
    }

    // Algoritmo de Codificación PCM WAV 16-bit a 16kHz nativo
    function encodeWAV(samples, sampleRate) {
        const buffer = new ArrayBuffer(44 + samples.length * 2);
        const view = new DataView(buffer);

        writeString(view, 0, 'RIFF');
        view.setUint32(4, 36 + samples.length * 2, true);
        writeString(view, 8, 'WAVE');
        writeString(view, 12, 'fmt ');
        view.setUint32(16, 16, true);
        view.setUint16(20, 1, true); // Raw PCM
        view.setUint16(22, 1, true); // Mono channel
        view.setUint32(24, sampleRate, true); // 16kHz
        view.setUint32(28, sampleRate * 2, true); // Byte rate
        view.setUint16(32, 2, true); // Block align
        view.setUint16(34, 16, true); // 16 bits
        writeString(view, 36, 'data');
        view.setUint32(40, samples.length * 2, true);

        floatTo16BitPCM(view, 44, samples);

        return new Blob([view], { type: 'audio/wav' });
    }

    function floatTo16BitPCM(output, offset, input) {
        for (let i = 0; i < input.length; i++, offset += 2) {
            let s = Math.max(-1, Math.min(1, input[i]));
            output.setInt16(offset, s < 0 ? s * 0x8000 : s * 0x7FFF, true);
        }
    }

    function writeString(view, offset, string) {
        for (let i = 0; i < string.length; i++) {
            view.setUint8(offset + i, string.charCodeAt(i));
        }
    }

    async function sendAudioToGPU(blob) {
        const formData = new FormData();
        formData.append('file', blob, 'recording.wav');

        try {
            statusText.innerText = "Procesando audio en GPU...";
            let endpoint = `${fastApiUrl}/speech-to-speech`;
            
            if (sourceLang !== "spa_Latn") {
                endpoint = `${fastApiUrl}/speech-to-text`;
            }

            const response = await fetch(endpoint, {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                const data = await response.json();
                
                if (sourceLang === "spa_Latn") {
                    sourceInput.value = data.transcription;
                    targetOutput.value = data.translation;
                    sourceCharCount.innerText = `${data.transcription.length} / 1000`;
                    targetCharCount.innerText = data.translation.length;
                    
                    statusText.innerText = "¡Procesamiento de voz completado!";
                    btnPlayTarget.style.display = "flex";

                    if (data.audio_url) {
                        const audio = new Audio(`http://127.0.0.1:8005${data.audio_url}`);
                        audio.play();
                    }
                } else {
                    sourceInput.value = data.transcription;
                    targetOutput.value = data.translation;
                    sourceCharCount.innerText = `${data.transcription.length} / 1000`;
                    targetCharCount.innerText = data.translation.length;
                    statusText.innerText = "¡Procesamiento de voz completado!";
                    btnPlayTarget.style.display = "none";
                }
            } else {
                statusText.innerText = "Error procesando voz en GPU";
            }
        } catch (e) {
            console.error(e);
            statusText.innerText = "Error de conexión GPU";
        }
    }

    // 5. Utilidades para copiar textos
    btnCopySource.addEventListener('click', () => {
        navigator.clipboard.writeText(sourceInput.value);
        const originalHtml = btnCopySource.innerHTML;
        btnCopySource.innerHTML = `<i class="fa-solid fa-check"></i> Copiado`;
        setTimeout(() => { btnCopySource.innerHTML = originalHtml; }, 2000);
    });

    btnCopyTarget.addEventListener('click', () => {
        navigator.clipboard.writeText(targetOutput.value);
        const originalHtml = btnCopyTarget.innerHTML;
        btnCopyTarget.innerHTML = `<i class="fa-solid fa-check"></i> Copiado`;
        setTimeout(() => { btnCopyTarget.innerHTML = originalHtml; }, 2000);
    });
</script>
@endsection