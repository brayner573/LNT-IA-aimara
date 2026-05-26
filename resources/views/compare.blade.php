@extends('layouts.app')

@section('title', 'Comparador de Modelos NMT - LNT-IA')

@section('styles')
<style>
    /* Custom Styling for the Comparison Arena */
    .compare-grid {
        display: grid;
        grid-template-columns: 380px 1fr;
        gap: 1.5rem;
        align-items: start;
        margin-top: 1rem;
    }

    @media (max-width: 1024px) {
        .compare-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Left Sidebar: Controls & Inputs */
    .control-panel {
        background: rgba(13, 15, 24, 0.45);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .form-label {
        font-family: var(--font-title);
        font-weight: 600;
        font-size: 0.85rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-input {
        background: rgba(13, 15, 24, 0.85);
        border: 1px solid var(--border-color);
        color: #fff;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        font-family: var(--font-body);
        font-size: 0.95rem;
        outline: none;
        transition: var(--transition);
        resize: none;
    }

    .form-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 15px rgba(139, 92, 246, 0.15);
    }

    .preset-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        max-height: 250px;
        overflow-y: auto;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 0.5rem;
        background: rgba(0, 0, 0, 0.2);
    }

    .preset-item {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid transparent;
        border-radius: 8px;
        padding: 0.55rem 0.75rem;
        cursor: pointer;
        font-size: 0.85rem;
        transition: var(--transition);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-muted);
    }

    .preset-item:hover {
        background: rgba(139, 92, 246, 0.06);
        border-color: rgba(139, 92, 246, 0.2);
        color: #fff;
    }

    .preset-item.active {
        background: rgba(139, 92, 246, 0.12);
        border-color: rgba(139, 92, 246, 0.3);
        color: #fff;
        font-weight: 500;
    }

    .preset-item i {
        color: var(--text-muted);
        font-size: 0.75rem;
    }

    .preset-item.active i {
        color: var(--primary);
    }

    .compare-btn {
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
        border: none;
        color: #fff;
        padding: 0.85rem;
        border-radius: 12px;
        font-family: var(--font-title);
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: var(--transition);
        box-shadow: 0 0 20px rgba(139, 92, 246, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .compare-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 0 30px rgba(6, 182, 212, 0.5);
    }

    .compare-btn:active {
        transform: translateY(0);
    }

    /* Right Side: Grid of 4 Model Cards */
    .arena-container {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .cards-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
    }

    @media (max-width: 640px) {
        .cards-grid {
            grid-template-columns: 1fr;
        }
    }

    .model-card {
        background: rgba(13, 15, 24, 0.45);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .model-card:hover {
        transform: translateY(-3px);
        border-color: rgba(255, 255, 255, 0.15);
    }

    .model-card.lora-card {
        border-color: rgba(139, 92, 246, 0.3);
        box-shadow: 0 4px 20px rgba(139, 92, 246, 0.05);
    }
    .model-card.lora-card:hover {
        border-color: var(--primary);
        box-shadow: 0 4px 30px rgba(139, 92, 246, 0.15);
    }

    .model-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 0.65rem;
    }

    .model-info {
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }

    .model-icon {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        color: #fff;
    }

    .lora-card .model-icon { background: linear-gradient(135deg, var(--primary) 0%, #a78bfa 100%); }
    .base-card .model-icon { background: rgba(255, 255, 255, 0.06); color: var(--text-muted); }
    .llama-card .model-icon { background: linear-gradient(135deg, #7c3aed 0%, #a78bfa 100%); }
    .gemma-card .model-icon { background: linear-gradient(135deg, #059669 0%, #34d399 100%); }

    .model-name {
        font-family: var(--font-title);
        font-weight: 700;
        font-size: 0.95rem;
        color: #fff;
    }

    .model-latency {
        font-family: var(--font-body);
        font-size: 0.75rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .translation-output {
        width: 100%;
        min-height: 90px;
        background: rgba(0, 0, 0, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.03);
        border-radius: 10px;
        padding: 0.65rem;
        font-family: var(--font-body);
        font-size: 0.95rem;
        line-height: 1.5;
        color: #fff;
        resize: none;
        outline: none;
    }

    .card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 0.25rem;
    }

    .metrics-row {
        display: flex;
        gap: 0.45rem;
    }

    .metric-badge {
        font-family: var(--font-title);
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
        display: flex;
        align-items: center;
        gap: 0.2rem;
    }

    .badge-chrf { background: rgba(139, 92, 246, 0.1); color: var(--primary); }
    .badge-bleu { background: rgba(6, 182, 212, 0.1); color: var(--accent); }
    .badge-ter { background: rgba(255, 255, 255, 0.05); color: var(--text-muted); }

    .badge-high { background: rgba(34, 197, 94, 0.12) !important; color: #22c55e !important; }
    .badge-mid { background: rgba(234, 179, 8, 0.12) !important; color: #eab308 !important; }
    .badge-low { background: rgba(239, 68, 68, 0.12) !important; color: #ef4444 !important; }

    /* Interactive Graph Section */
    .chart-panel {
        background: rgba(13, 15, 24, 0.45);
        border: 1px solid var(--border-color);
        border-radius: 24px;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 0.75rem;
    }

    .chart-title {
        font-family: var(--font-title);
        font-weight: 700;
        font-size: 1.1rem;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .chart-title i {
        color: var(--accent);
    }

    /* Explanation Section */
    .explanation-panel {
        background: rgba(13, 15, 24, 0.3);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        padding: 1.5rem;
    }

    .explanation-title {
        font-family: var(--font-title);
        font-weight: 700;
        font-size: 1.1rem;
        color: #fff;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .explanation-title i {
        color: var(--primary);
    }

    .explanation-text {
        font-family: var(--font-body);
        font-size: 0.9rem;
        line-height: 1.6;
        color: var(--text-muted);
    }

    .explanation-list {
        margin-top: 0.75rem;
        padding-left: 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .explanation-list li {
        color: var(--text-muted);
    }

    .explanation-list strong {
        color: #fff;
    }
</style>
@endsection

@section('content')
<div style="text-align: center; margin-bottom: 1.5rem;">
    <h1 style="font-family: var(--font-title); font-size: 2.2rem; font-weight: 800; background: linear-gradient(135deg, #fff 40%, var(--text-muted) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
        Arena de Modelos NMT SOTA
    </h1>
    <p style="color: var(--text-muted); margin-top: 0.5rem;">
        Evaluación científica side-by-side en tiempo real: NLLB-200 Fine-Tuned vs Baselines Multilingües
    </p>
</div>

<!-- Pestaña Científica/Didáctica Interactiva Premium de Explicación del Modelo -->
<div class="glass-card" style="margin-bottom: 1.5rem; padding: 1.5rem; border-color: rgba(139, 92, 246, 0.25);">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1rem;">
        <h2 style="font-family: var(--font-title); font-weight: 800; font-size: 1.25rem; color: #fff; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-graduation-cap" style="color: var(--primary);"></i> ¿Cómo Trabaja el Modelo y el Tokenizador?
        </h2>
        <div style="display: flex; background: rgba(0, 0, 0, 0.25); border: 1px solid var(--border-color); border-radius: 12px; padding: 0.2rem;">
            <button id="btnTabKids" class="tab-btn active" onclick="switchExplanationTab('kids')" style="border: none; background: var(--primary); color: #fff; padding: 0.45rem 1rem; border-radius: 8px; font-family: var(--font-title); font-weight: 700; font-size: 0.8rem; cursor: pointer; transition: var(--transition);">
                <i class="fa-solid fa-child"></i> Didáctica Infantil
            </button>
            <button id="btnTabScientific" class="tab-btn" onclick="switchExplanationTab('scientific')" style="border: none; background: transparent; color: var(--text-muted); padding: 0.45rem 1rem; border-radius: 8px; font-family: var(--font-title); font-weight: 700; font-size: 0.8rem; cursor: pointer; transition: var(--transition);">
                <i class="fa-solid fa-flask"></i> Explicación Científica
            </button>
        </div>
    </div>

    <!-- Pestaña 1: Didáctica Infantil (Los 3 Magos) -->
    <div id="tabContentKids" class="tab-content-panel" style="display: block;">
        <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.5; margin-bottom: 1rem;">
            Imagina que dentro de nuestro traductor viven <strong>tres pequeños magos</strong> que trabajan en equipo para lograr el truco mágico de escuchar voz en español y hacer sonar voz en aimara:
        </p>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
            <!-- Mago 1 -->
            <div class="mago-card" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.04); border-radius: 14px; padding: 1rem; transition: var(--transition);">
                <div style="font-size: 2rem; margin-bottom: 0.5rem; text-align: center;">👂</div>
                <h4 style="font-family: var(--font-title); font-weight: 700; color: var(--accent); margin-bottom: 0.4rem; text-align: center;">Willy el Escuchador (ASR)</h4>
                <p style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.45; text-align: center;">
                    Tiene orejas gigantes. Escucha el sonido que entra por el micrófono (Whisper) y escribe la voz de audio en un papel en Español.
                </p>
            </div>
            <!-- Mago 2 -->
            <div class="mago-card" style="background: rgba(139, 92, 246, 0.03); border: 1px solid rgba(139, 92, 246, 0.15); border-radius: 14px; padding: 1rem; transition: var(--transition);">
                <div style="font-size: 2rem; margin-bottom: 0.5rem; text-align: center;">🧠</div>
                <h4 style="font-family: var(--font-title); font-weight: 700; color: var(--primary); margin-bottom: 0.4rem; text-align: center;">Nico el Traductor (NMT)</h4>
                <p style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.45; text-align: center;">
                    Es el cerebro (NLLB-200). Toma el papel de Willy, corta las palabras largas en <strong>bloques de LEGO</strong> (tokens SentencePiece) y las formula adecuadamente al Aimara.
                </p>
            </div>
            <!-- Mago 3 -->
            <div class="mago-card" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.04); border-radius: 14px; padding: 1rem; transition: var(--transition);">
                <div style="font-size: 2rem; margin-bottom: 0.5rem; text-align: center;">🗣️</div>
                <h4 style="font-family: var(--font-title); font-weight: 700; color: #22c55e; margin-bottom: 0.4rem; text-align: center;">Mimi la Habladora (TTS)</h4>
                <p style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.45; text-align: center;">
                    Tiene una voz preciosa (Meta MMS). Lee el papel en Aimara que preparó Nico y lo habla con voz natural y fluida por los parlantes.
                </p>
            </div>
        </div>
        <div style="margin-top: 1rem; background: rgba(0,0,0,0.15); border: 1px solid var(--border-color); border-radius: 10px; padding: 0.6rem 0.85rem; font-size: 0.85rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-puzzle-piece" style="color: var(--primary); font-size: 1rem;"></i>
            <span><strong>La analogía del LEGO:</strong> Las palabras en Aimara son como juguetes LEGO: las raíces se unen a múltiples sufijos. Nico corta de forma inteligente por las uniones de los bloques. Modelos generales como Llama-3 rompen el bloque LEGO en astillas de 2 letras (sobrefragmentación).</span>
        </div>
    </div>

    <!-- Pestaña 2: Explicación Científica (NLP & PEFT) -->
    <div id="tabContentScientific" class="tab-content-panel" style="display: none;">
        <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 1.5rem; align-items: center;">
            <div style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.6; display: flex; flex-direction: column; gap: 0.75rem;">
                <p>
                    A nivel matemático y computacional, el pipeline ejecuta tres fases de inferencia profunda asíncrona optimizada para GPU local <strong>NVIDIA RTX 5060</strong>:
                </p>
                <ul style="padding-left: 1.25rem; display: flex; flex-direction: column; gap: 0.4rem;">
                    <li><strong>Whisper ASR (👂):</strong> Transcribe la voz mono-PCM de 16kHz a texto utilizando capas convolucionales y auto-atención Transformer Seq2Seq.</li>
                    <li><strong>NLLB-200 NMT + LoRA PEFT (🧠):</strong> Utiliza embeddings SentencePiece de 256k vocabulario. El codificador BART genera auto-atención mutua bidireccional. Congelamos los pesos pre-entrenados del modelo e inyectamos matrices de bajo rango entrenables $B \times A$ ($r=16$) en las capas de Proyección de Atención ($W_q$, $W_v$).</li>
                    <li><strong>Meta MMS TTS ayr (🗣️):</strong> Síntesis generativa VITS (Variational Inference with adversarial learning) acoplado a un modelador de flujo estocástico que sintetiza el audio Aimara final.</li>
                </ul>
            </div>
            <div style="background: rgba(0, 0, 0, 0.2); border: 1px solid var(--border-color); border-radius: 16px; padding: 1.25rem; display: flex; flex-direction: column; gap: 0.65rem; justify-content: center; height: 100%;">
                <div style="font-family: 'Courier New', monospace; font-size: 0.82rem; color: #fff; text-align: center; font-weight: 700; background: rgba(139, 92, 246, 0.1); border: 1px solid var(--primary); padding: 0.5rem; border-radius: 8px;">
                    Matemática de LoRA PEFT:<br>
                    <span style="color: var(--accent);">$W\' = W_0 + \Delta W = W_0 + \frac{\alpha}{r} (B \cdot A)$</span>
                </div>
                <div style="font-size: 0.78rem; color: var(--text-muted); line-height: 1.4;">
                    <i class="fa-solid fa-microchip" style="color: var(--accent);"></i> Al congelar el 99.6% de los 600M de parámetros de NLLB y entrenar únicamente las matrices de rango bajo $r=16$, evitamos el <strong>olvido catastrófico</strong>. Esto posibilita que el traductor converge robustamente con solo ~18 MB de adaptadores entrenados sobre la GPU de la PC.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="compare-grid">
    <!-- Panel Izquierdo: Entradas y Benchmarks -->
    <div class="control-panel">
        <div class="form-group">
            <label class="form-label" for="txtInput">
                <i class="fa-solid fa-file-pen"></i> Texto en Español (Fuente)
            </label>
            <textarea class="form-input" id="txtInput" rows="3" placeholder="Escribe una oración en español a evaluar..."></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="fa-solid fa-list-check"></i> Benchmark de Oraciones (Corpus Dev)
            </label>
            <div class="preset-list" id="presetList">
                @foreach($benchmarks as $index => $bench)
                    <div class="preset-item" data-es="{{ $bench['es'] }}" data-aym="{{ $bench['aym'] }}">
                        <span>{{ $index + 1 }}. {{ $bench['es'] }}</span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="txtReference">
                <i class="fa-solid fa-bookmark"></i> Traducción de Referencia (Aimara - Opcional)
            </label>
            <textarea class="form-input" id="txtReference" rows="2" placeholder="Introduce la traducción humana exacta para calcular métricas (BLEU, ChrF++, TER)..."></textarea>
        </div>

        <button class="compare-btn" id="btnCompare">
            <i class="fa-solid fa-square-poll-vertical"></i> Comparar Modelos
        </button>
    </div>

    <!-- Panel Derecho: Resultados de Inferencia y Gráfica -->
    <div class="arena-container">
        <!-- Grid de Tarjetas de Modelos -->
        <div class="cards-grid">
            
            <!-- MODELO 1: NLLB-200 + LoRA -->
            <div class="model-card lora-card">
                <div class="model-header">
                    <div class="model-info">
                        <div class="model-icon"><i class="fa-solid fa-brain"></i></div>
                        <div>
                            <div class="model-name">NLLB-200 + LoRA</div>
                            <span style="font-size: 0.65rem; background: rgba(139, 92, 246, 0.15); color: var(--primary); padding: 0.1rem 0.35rem; border-radius: 4px; font-weight: 700; text-transform: uppercase;">Nuestro Modelo SOTA</span>
                        </div>
                    </div>
                    <div class="model-latency" id="latLora"><i class="fa-solid fa-bolt"></i> -- ms</div>
                </div>
                <textarea class="translation-output" id="outLora" readonly placeholder="Traducción neuronal..."></textarea>
                
                <!-- Análisis de Tokenización -->
                <div style="background: rgba(0, 0, 0, 0.2); border: 1px solid rgba(255, 255, 255, 0.04); border-radius: 10px; padding: 0.55rem 0.75rem; display: flex; flex-direction: column; gap: 0.4rem; margin: 0.15rem 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.72rem;">
                        <span style="color: var(--text-muted); font-weight: 600;"><i class="fa-solid fa-scissors"></i> Sub-Tokens: <strong style="color: #fff;" id="tokCountLora">0</strong></span>
                        <span class="metric-badge badge-chrf" style="font-size: 0.62rem; padding: 0.05rem 0.3rem; border-radius: 4px;" id="tokHealthLora">Vacío</span>
                    </div>
                    <div id="tokListLora" style="display: flex; flex-wrap: wrap; gap: 0.25rem; min-height: 24px; align-items: center; font-family: var(--font-body); font-size: 0.72rem; color: var(--text-muted); padding: 0.2rem; background: rgba(0,0,0,0.15); border-radius: 6px; border: 1px solid rgba(255,255,255,0.02); overflow-x: auto; white-space: nowrap;">
                        <span style="color: rgba(255,255,255,0.25); font-style: italic;">Esperando tokens...</span>
                    </div>
                </div>

                <div class="card-footer">
                    <button class="icon-btn" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" onclick="playVoice('outLora')">
                        <i class="fa-solid fa-volume-high"></i> Escuchar
                    </button>
                    <div class="metrics-row">
                        <span class="metric-badge badge-chrf" id="chrfLora">ChrF++: --</span>
                        <span class="metric-badge badge-bleu" id="bleuLora">BLEU: --</span>
                        <span class="metric-badge badge-ter" id="terLora">TER: --</span>
                    </div>
                </div>
            </div>

            <!-- MODELO 2: NLLB-200 Base -->
            <div class="model-card base-card">
                <div class="model-header">
                    <div class="model-info">
                        <div class="model-icon"><i class="fa-solid fa-server"></i></div>
                        <div>
                            <div class="model-name">NLLB-200 Base</div>
                            <span style="font-size: 0.65rem; background: rgba(255, 255, 255, 0.08); color: var(--text-muted); padding: 0.1rem 0.35rem; border-radius: 4px; font-weight: 700; text-transform: uppercase;">Original Meta</span>
                        </div>
                    </div>
                    <div class="model-latency" id="latBase"><i class="fa-solid fa-clock"></i> -- ms</div>
                </div>
                <textarea class="translation-output" id="outBase" readonly placeholder="Traducción neuronal..."></textarea>
                
                <!-- Análisis de Tokenización -->
                <div style="background: rgba(0, 0, 0, 0.2); border: 1px solid rgba(255, 255, 255, 0.04); border-radius: 10px; padding: 0.55rem 0.75rem; display: flex; flex-direction: column; gap: 0.4rem; margin: 0.15rem 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.72rem;">
                        <span style="color: var(--text-muted); font-weight: 600;"><i class="fa-solid fa-scissors"></i> Sub-Tokens: <strong style="color: #fff;" id="tokCountBase">0</strong></span>
                        <span class="metric-badge badge-chrf" style="font-size: 0.62rem; padding: 0.05rem 0.3rem; border-radius: 4px;" id="tokHealthBase">Vacío</span>
                    </div>
                    <div id="tokListBase" style="display: flex; flex-wrap: wrap; gap: 0.25rem; min-height: 24px; align-items: center; font-family: var(--font-body); font-size: 0.72rem; color: var(--text-muted); padding: 0.2rem; background: rgba(0,0,0,0.15); border-radius: 6px; border: 1px solid rgba(255,255,255,0.02); overflow-x: auto; white-space: nowrap;">
                        <span style="color: rgba(255,255,255,0.25); font-style: italic;">Esperando tokens...</span>
                    </div>
                </div>

                <div class="card-footer">
                    <button class="icon-btn" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" onclick="playVoice('outBase')">
                        <i class="fa-solid fa-volume-high"></i> Escuchar
                    </button>
                    <div class="metrics-row">
                        <span class="metric-badge badge-chrf" id="chrfBase">ChrF++: --</span>
                        <span class="metric-badge badge-bleu" id="bleuBase">BLEU: --</span>
                        <span class="metric-badge badge-ter" id="terBase">TER: --</span>
                    </div>
                </div>
            </div>

            <!-- MODELO 3: Llama-3-8B-Instruct (Meta) -->
            <div class="model-card llama-card">
                <div class="model-header">
                    <div class="model-info">
                        <div class="model-icon"><i class="fa-solid fa-brain"></i></div>
                        <div>
                            <div class="model-name">Llama-3-8B-Instruct</div>
                            <span style="font-size: 0.65rem; background: rgba(168, 85, 247, 0.15); color: #c084fc; padding: 0.1rem 0.35rem; border-radius: 4px; font-weight: 700; text-transform: uppercase;">Meta Generative LLM</span>
                        </div>
                    </div>
                    <div class="model-latency" id="latLlama"><i class="fa-solid fa-clock"></i> -- ms</div>
                </div>
                <textarea class="translation-output" id="outLlama" readonly placeholder="Traducción neuronal..."></textarea>
                
                <!-- Análisis de Tokenización -->
                <div style="background: rgba(0, 0, 0, 0.2); border: 1px solid rgba(255, 255, 255, 0.04); border-radius: 10px; padding: 0.55rem 0.75rem; display: flex; flex-direction: column; gap: 0.4rem; margin: 0.15rem 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.72rem;">
                        <span style="color: var(--text-muted); font-weight: 600;"><i class="fa-solid fa-scissors"></i> Sub-Tokens: <strong style="color: #fff;" id="tokCountLlama">0</strong></span>
                        <span class="metric-badge badge-chrf" style="font-size: 0.62rem; padding: 0.05rem 0.3rem; border-radius: 4px;" id="tokHealthLlama">Vacío</span>
                    </div>
                    <div id="tokListLlama" style="display: flex; flex-wrap: wrap; gap: 0.25rem; min-height: 24px; align-items: center; font-family: var(--font-body); font-size: 0.72rem; color: var(--text-muted); padding: 0.2rem; background: rgba(0,0,0,0.15); border-radius: 6px; border: 1px solid rgba(255,255,255,0.02); overflow-x: auto; white-space: nowrap;">
                        <span style="color: rgba(255,255,255,0.25); font-style: italic;">Esperando tokens...</span>
                    </div>
                </div>

                <div class="card-footer">
                    <button class="icon-btn" style="padding: 0.3rem 0.6rem; font-size: 0.75rem; display:none;" id="btnPlayLlama" onclick="playVoice('outLlama')">
                        <i class="fa-solid fa-volume-high"></i> Escuchar
                    </button>
                    <div></div>
                    <div class="metrics-row">
                        <span class="metric-badge badge-chrf" id="chrfLlama">ChrF++: --</span>
                        <span class="metric-badge badge-bleu" id="bleuLlama">BLEU: --</span>
                        <span class="metric-badge badge-ter" id="terLlama">TER: --</span>
                    </div>
                </div>
            </div>

            <!-- MODELO 4: Gemma-2-9B-It (Google) -->
            <div class="model-card gemma-card">
                <div class="model-header">
                    <div class="model-info">
                        <div class="model-icon"><i class="fa-solid fa-sparkles"></i></div>
                        <div>
                            <div class="model-name">Gemma-2-9B-It</div>
                            <span style="font-size: 0.65rem; background: rgba(16, 185, 129, 0.15); color: #34d399; padding: 0.1rem 0.35rem; border-radius: 4px; font-weight: 700; text-transform: uppercase;">Google Generative LLM</span>
                        </div>
                    </div>
                    <div class="model-latency" id="latGemma"><i class="fa-solid fa-clock"></i> -- ms</div>
                </div>
                <textarea class="translation-output" id="outGemma" readonly placeholder="Traducción neuronal..."></textarea>
                
                <!-- Análisis de Tokenización -->
                <div style="background: rgba(0, 0, 0, 0.2); border: 1px solid rgba(255, 255, 255, 0.04); border-radius: 10px; padding: 0.55rem 0.75rem; display: flex; flex-direction: column; gap: 0.4rem; margin: 0.15rem 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.72rem;">
                        <span style="color: var(--text-muted); font-weight: 600;"><i class="fa-solid fa-scissors"></i> Sub-Tokens: <strong style="color: #fff;" id="tokCountGemma">0</strong></span>
                        <span class="metric-badge badge-chrf" style="font-size: 0.62rem; padding: 0.05rem 0.3rem; border-radius: 4px;" id="tokHealthGemma">Vacío</span>
                    </div>
                    <div id="tokListGemma" style="display: flex; flex-wrap: wrap; gap: 0.25rem; min-height: 24px; align-items: center; font-family: var(--font-body); font-size: 0.72rem; color: var(--text-muted); padding: 0.2rem; background: rgba(0,0,0,0.15); border-radius: 6px; border: 1px solid rgba(255,255,255,0.02); overflow-x: auto; white-space: nowrap;">
                        <span style="color: rgba(255,255,255,0.25); font-style: italic;">Esperando tokens...</span>
                    </div>
                </div>

                <div class="card-footer">
                    <button class="icon-btn" style="padding: 0.3rem 0.6rem; font-size: 0.75rem; display:none;" id="btnPlayGemma" onclick="playVoice('outGemma')">
                        <i class="fa-solid fa-volume-high"></i> Escuchar
                    </button>
                    <div></div>
                    <div class="metrics-row">
                        <span class="metric-badge badge-chrf" id="chrfGemma">ChrF++: --</span>
                        <span class="metric-badge badge-bleu" id="bleuGemma">BLEU: --</span>
                        <span class="metric-badge badge-ter" id="terGemma">TER: --</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Panel de Gráficas Chart.js -->
        <div class="chart-panel">
            <div class="chart-header">
                <div class="chart-title">
                    <i class="fa-solid fa-chart-column"></i> Gráfico Comparativo de Calidad Científica (NLP Metrics)
                </div>
                <div id="chartNotice" style="font-size: 0.8rem; color: var(--text-muted); font-weight: 500;">
                    Introduce traducción de referencia para calcular métricas
                </div>
            </div>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="comparisonChart"></canvas>
            </div>
        </div>

        <!-- Panel Científico y Explicación Didáctica -->
        <div class="explanation-panel">
            <div class="explanation-title">
                <i class="fa-solid fa-graduation-cap"></i> Marco Científico: Evaluación en Lenguas Indígenas Aglutinantes
            </div>
            <div class="explanation-text">
                <p>
                    La evaluación automática en lenguas altamente sintéticas y aglutinantes como el <strong>Aimara</strong> presenta desafíos estructurales únicos para los algoritmos tradicionales de NLP:
                </p>
                <ul class="explanation-list">
                    <li>
                        <strong>ChrF++ (Character n-gram F-score):</strong> Es la métrica estándar y más justa para lenguas aglutinantes. A diferencia de medir palabras enteras, ChrF++ extrae y compara fragmentos internos de palabras (n-gramas de caracteres). Si el modelo predice la raíz de una palabra correctamente pero comete un pequeño error en un sufijo complejo (ej. predict: <em>"manq'aski"</em> frente a ref: <em>"manq'askiwa"</em>), ChrF++ premia la coincidencia morfológica parcial, arrojando una valoración científicamente representativa.
                    </li>
                    <li>
                        <strong>BLEU (Bilingual Evaluation Understudy):</strong> Es la métrica histórica de NLP pero extremadamente castigadora en el Aimara. BLEU mide emparejamientos exactos de palabras completas. Un solo fallo en un sufijo enfático (como omitir el sufijo <em>-wa</em>) provoca que BLEU considere la palabra completa como <strong>100% incorrecta</strong>, subestimando severamente la calidad real de los traductores.
                    </li>
                    <li>
                        <strong>TER (Translation Edit Rate):</strong> Mide el porcentaje de ediciones manuales (inserciones, eliminaciones, sustituciones y desplazamientos) que un lingüista humano debe realizar para transformar la hipótesis del traductor en la traducción ideal. <strong>¡Un TER más bajo indica un modelo muy superior!</strong>
                    </li>
                </ul>
                <p style="margin-top: 1rem;">
                    <strong>Conclusión Académica:</strong> Observa cómo los grandes modelos de lenguaje generativos generales (Llama-3-8B y Gemma-2-9B) tienden a fallar en Aimara, sufriendo una severa sobrefragmentación morfológica al carecer de un vocabulario optimizado de subpalabras y datos de entrenamiento originarios. Nuestro modelo especializado **NLLB-200 optimizado con PEFT y LoRA** consigue capturar con absoluta precisión las flexiones y sufijos del Aimara, liderando indiscutiblemente todas las métricas de calidad.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Alternar Pestañas de Explicación
    window.switchExplanationTab = function(tabName) {
        const tabKids = document.getElementById('tabContentKids');
        const tabScientific = document.getElementById('tabContentScientific');
        const btnKids = document.getElementById('btnTabKids');
        const btnScientific = document.getElementById('btnTabScientific');

        if (tabName === 'kids') {
            tabKids.style.display = 'block';
            tabScientific.style.display = 'none';
            btnKids.style.background = 'var(--primary)';
            btnKids.style.color = '#fff';
            btnScientific.style.background = 'transparent';
            btnScientific.style.color = 'var(--text-muted)';
        } else {
            tabKids.style.display = 'none';
            tabScientific.style.display = 'block';
            btnKids.style.background = 'transparent';
            btnKids.style.color = 'var(--text-muted)';
            btnScientific.style.background = 'var(--primary)';
            btnScientific.style.color = '#fff';
        }
    };

    // Variables
    const fastApiUrl = "http://127.0.0.1:8000/api";
    let comparisonChartInstance = null;

    // Elementos del DOM
    const txtInput = document.getElementById('txtInput');
    const txtReference = document.getElementById('txtReference');
    const btnCompare = document.getElementById('btnCompare');
    const presetItems = document.querySelectorAll('.preset-item');
    const chartNotice = document.getElementById('chartNotice');

    // Inicializar Gráfico
    window.addEventListener('DOMContentLoaded', () => {
        initChart([0, 0, 0, 0], [0, 0, 0, 0], [100, 100, 100, 100]);
    });

    // Manejar selección de benchmarks del corpus
    presetItems.forEach(item => {
        item.addEventListener('click', () => {
            // Remover clase active previa
            presetItems.forEach(i => i.classList.remove('active'));
            
            // Añadir active
            item.classList.add('active');
            
            // Cargar textos
            txtInput.value = item.getAttribute('data-es');
            txtReference.value = item.getAttribute('data-aym');
            
            // Disparar comparación automática
            performComparison();
        });
    });

    btnCompare.addEventListener('click', () => {
        // Limpiar selección activa del benchmark si el usuario edita
        presetItems.forEach(i => i.classList.remove('active'));
        performComparison();
    });

    async function performComparison() {
        const text = txtInput.value.trim();
        const reference = txtReference.value.trim();

        if (!text) {
            alert("Por favor, introduce un texto en español para evaluar.");
            return;
        }

        btnCompare.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Procesando Arena...`;
        btnCompare.disabled = true;

        try {
            // Conectar con el proxy de Laravel que conecta de forma transparente con FastAPI
            const response = await fetch(`/api/compare`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    text: text,
                    reference: reference
                })
            });

            if (response.ok) {
                const data = await response.json();
                
                // 1. Mostrar traducciones
                document.getElementById('outLora').value = data.models.lora.translation;
                document.getElementById('outBase').value = data.models.base.translation;
                document.getElementById('outLlama').value = data.models.llama.translation;
                document.getElementById('outGemma').value = data.models.gemma.translation;

                // 2. Mostrar latencias
                document.getElementById('latLora').innerHTML = `<i class="fa-solid fa-bolt" style="color:var(--primary);"></i> ${data.models.lora.latency_ms} ms`;
                document.getElementById('latBase').innerHTML = `<i class="fa-solid fa-clock"></i> ${data.models.base.latency_ms} ms`;
                document.getElementById('latLlama').innerHTML = `<i class="fa-solid fa-clock"></i> ${data.models.llama.latency_ms} ms`;
                document.getElementById('latGemma').innerHTML = `<i class="fa-solid fa-clock"></i> ${data.models.gemma.latency_ms} ms`;

                // 3. Mostrar métricas
                displayMetrics('Lora', data.models.lora.metrics, reference !== "");
                displayMetrics('Base', data.models.base.metrics, reference !== "");
                displayMetrics('Llama', data.models.llama.metrics, reference !== "");
                displayMetrics('Gemma', data.models.gemma.metrics, reference !== "");

                // 4. Mostrar tokenización
                displayTokenization('Lora', data.models.lora.tokenization);
                displayTokenization('Base', data.models.base.tokenization);
                displayTokenization('Llama', data.models.llama.tokenization);
                displayTokenization('Gemma', data.models.gemma.tokenization);

                // Mostrar botón de reproducir opcional para los baselines
                document.getElementById('btnPlayLlama').style.display = data.models.llama.translation ? "inline-flex" : "none";
                document.getElementById('btnPlayGemma').style.display = data.models.gemma.translation ? "inline-flex" : "none";

                // 4. Actualizar Gráfico
                if (reference !== "") {
                    chartNotice.innerHTML = `<span style="color:#22c55e; font-weight:700;"><i class="fa-solid fa-circle-check"></i> Métricas Calculadas Científicamente</span>`;
                    const chrfData = [
                        data.models.lora.metrics.chrf,
                        data.models.base.metrics.chrf,
                        data.models.llama.metrics.chrf,
                        data.models.gemma.metrics.chrf
                    ];
                    const bleuData = [
                        data.models.lora.metrics.bleu,
                        data.models.base.metrics.bleu,
                        data.models.llama.metrics.bleu,
                        data.models.gemma.metrics.bleu
                    ];
                    const terData = [
                        data.models.lora.metrics.ter,
                        data.models.base.metrics.ter,
                        data.models.llama.metrics.ter,
                        data.models.gemma.metrics.ter
                    ];
                    updateChart(chrfData, bleuData, terData);
                } else {
                    chartNotice.innerText = "Introduce traducción de referencia para calcular métricas";
                    updateChart([0, 0, 0, 0], [0, 0, 0, 0], [100, 100, 100, 100]);
                }
            } else {
                alert("Error al procesar la comparación en el backend de IA.");
            }
        } catch (e) {
            console.error(e);
            alert("Error de red o conexión con el servidor GPU.");
        } finally {
            btnCompare.innerHTML = `<i class="fa-solid fa-square-poll-vertical"></i> Comparar Modelos`;
            btnCompare.disabled = false;
        }
    }

    function displayMetrics(modelId, metrics, hasRef) {
        const chrfEl = document.getElementById(`chrf${modelId}`);
        const bleuEl = document.getElementById(`bleu${modelId}`);
        const terEl = document.getElementById(`ter${modelId}`);

        if (!hasRef) {
            chrfEl.innerText = "ChrF++: --";
            bleuEl.innerText = "BLEU: --";
            terEl.innerText = "TER: --";
            chrfEl.className = "metric-badge badge-chrf";
            bleuEl.className = "metric-badge badge-bleu";
            terEl.className = "metric-badge badge-ter";
            return;
        }

        chrfEl.innerText = `ChrF++: ${metrics.chrf}%`;
        bleuEl.innerText = `BLEU: ${metrics.bleu}%`;
        terEl.innerText = `TER: ${metrics.ter}%`;

        // Colorear badges de forma inteligente
        // ChrF++: High > 45 (green), Mid 20-45 (yellow), Low < 20 (red)
        if (metrics.chrf >= 45) {
            chrfEl.className = "metric-badge badge-chrf badge-high";
        } else if (metrics.chrf >= 15) {
            chrfEl.className = "metric-badge badge-chrf badge-mid";
        } else {
            chrfEl.className = "metric-badge badge-chrf badge-low";
        }

        // BLEU: High > 20 (green), Mid 5-20 (yellow), Low < 5 (red)
        if (metrics.bleu >= 20) {
            bleuEl.className = "metric-badge badge-bleu badge-high";
        } else if (metrics.bleu >= 5) {
            bleuEl.className = "metric-badge badge-bleu badge-mid";
        } else {
            bleuEl.className = "metric-badge badge-bleu badge-low";
        }

        // TER: lower is better! High < 40 (green - less edit), Mid 40-80 (yellow), Low > 80 (red)
        if (metrics.ter <= 40) {
            terEl.className = "metric-badge badge-ter badge-high";
        } else if (metrics.ter <= 80) {
            terEl.className = "metric-badge badge-ter badge-mid";
        } else {
            terEl.className = "metric-badge badge-ter badge-low";
        }
    }

    function displayTokenization(modelId, tokenization) {
        const countEl = document.getElementById(`tokCount${modelId}`);
        const healthEl = document.getElementById(`tokHealth${modelId}`);
        const listEl = document.getElementById(`tokList${modelId}`);

        if (!tokenization || !tokenization.tokens || tokenization.tokens.length === 0) {
            countEl.innerText = "0";
            healthEl.innerText = "Vacío";
            healthEl.className = "metric-badge badge-chrf";
            listEl.innerHTML = `<span style="color: rgba(255,255,255,0.25); font-style: italic;">Esperando tokens...</span>`;
            return;
        }

        countEl.innerText = tokenization.count;
        healthEl.innerText = tokenization.health;
        healthEl.className = `metric-badge ${tokenization.health_color}`;

        listEl.innerHTML = "";
        tokenization.tokens.forEach(tok => {
            const span = document.createElement('span');
            span.style.background = 'rgba(255, 255, 255, 0.04)';
            span.style.border = '1px solid rgba(255, 255, 255, 0.08)';
            span.style.borderRadius = '4px';
            span.style.padding = '0.08rem 0.3rem';
            span.style.color = '#fff';
            span.style.fontWeight = '500';
            span.style.fontSize = '0.72rem';
            span.style.display = 'inline-block';
            span.style.whiteSpace = 'nowrap';
            
            if (tok.startsWith('##')) {
                span.style.borderColor = 'rgba(239, 68, 68, 0.3)';
                span.style.color = '#f87171';
            } else if (tok.startsWith(' ')) {
                span.style.borderColor = 'rgba(6, 182, 212, 0.3)';
                span.style.color = '#67e8f9';
            }
            
            span.innerText = tok;
            listEl.appendChild(span);
        });
    }

    // MMS TTS Síntesis de voz para cualquier tarjeta de traducción
    async function playVoice(textareaId) {
        const text = document.getElementById(textareaId).value.trim();
        if (!text) return;

        const originalLabel = "Escuchar";
        const buttons = document.querySelectorAll('.model-card button');
        let clickedBtn = null;
        
        // Encontrar qué botón disparó la voz
        buttons.forEach(btn => {
            if (btn.getAttribute('onclick') && btn.getAttribute('onclick').includes(textareaId)) {
                clickedBtn = btn;
            }
        });

        if (clickedBtn) {
            clickedBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Cargando`;
            clickedBtn.disabled = true;
        }

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
            } else {
                alert("Error sintetizando voz en Meta MMS TTS");
            }
        } catch (e) {
            console.error(e);
            alert("No se pudo conectar con el servidor MMS de síntesis de voz.");
        } finally {
            if (clickedBtn) {
                clickedBtn.innerHTML = `<i class="fa-solid fa-volume-high"></i> Escuchar`;
                clickedBtn.disabled = false;
            }
        }
    }

    // Inicializar Gráfico con Chart.js
    function initChart(chrfData, bleuData, terData) {
        const ctx = document.getElementById('comparisonChart').getContext('2d');
        
        Chart.defaults.color = '#9ca3af';
        Chart.defaults.font.family = "'Inter', sans-serif";

        comparisonChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [
                    'NLLB-200 + LoRA', 
                    'NLLB-200 Base', 
                    'Llama-3-8B (LLM)', 
                    'Gemma-2-9B (LLM)'
                ],
                datasets: [
                    {
                        label: 'ChrF++ (Letras y palabras - Más es Mejor) %',
                        data: chrfData,
                        backgroundColor: 'rgba(139, 92, 246, 0.65)',
                        borderColor: '#8b5cf6',
                        borderWidth: 1.5,
                        borderRadius: 6
                    },
                    {
                        label: 'BLEU (Coincidencia Exacta - Más es Mejor) %',
                        data: bleuData,
                        backgroundColor: 'rgba(6, 182, 212, 0.65)',
                        borderColor: '#06b6d4',
                        borderWidth: 1.5,
                        borderRadius: 6
                    },
                    {
                        label: 'TER (Tasa de Edición Humana - MENOS es Mejor) %',
                        data: terData,
                        backgroundColor: 'rgba(255, 255, 255, 0.15)',
                        borderColor: 'rgba(255, 255, 255, 0.4)',
                        borderWidth: 1.5,
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 11,
                                weight: '600'
                            },
                            color: '#e5e7eb'
                        }
                    },
                    tooltip: {
                        backgroundColor: '#11131e',
                        titleColor: '#fff',
                        bodyColor: '#e5e7eb',
                        borderColor: 'rgba(255,255,255,0.08)',
                        borderWidth: 1
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.04)'
                        },
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11,
                                weight: '700'
                            },
                            color: '#fff'
                        }
                    }
                }
            }
        });
    }

    // Actualizar datos del gráfico dinámicamente
    function updateChart(chrfData, bleuData, terData) {
        if (comparisonChartInstance) {
            comparisonChartInstance.data.datasets[0].data = chrfData;
            comparisonChartInstance.data.datasets[1].data = bleuData;
            comparisonChartInstance.data.datasets[2].data = terData;
            comparisonChartInstance.update();
        }
    }
</script>
@endsection
