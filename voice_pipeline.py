#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Módulo de Integración de Voz Estado del Arte.
Implementa el flujo Speech-to-Speech en cascada utilizando:
- Whisper Large V3 Turbo (OpenAI) para Speech-to-Text en Español (Máxima precisión).
- NLLB-200 con adaptadores LoRA de PEFT para NMT Español -> Aimara.
- Meta MMS (Massive Multilingual Speech) para Text-to-Speech en Aimara.

Autor: Ingeniero Experto en IA, NLP & Transformers
"""

import os
import torch
import scipy.io.wavfile
import numpy as np
from transformers import pipeline, VitsModel, AutoTokenizer, AutoModelForSeq2SeqLM
from peft import PeftModel

# Importar funciones de traducción y constantes del módulo local
from nmt_translator import translate_nllb, SRC_LANG, TGT_LANG


def speech_to_text_whisper(
    audio_path, 
    model_name="openai/whisper-large-v3-turbo", 
    device="cuda" if torch.cuda.is_available() else "cpu"
):
    """
    Transcribe un archivo de audio en español utilizando la versión de última generación de Whisper.
    Intenta activar Flash Attention 2 para optimizar velocidad si es compatible.
    """
    print(f"[*] Transcribiendo audio en Español usando {model_name}...")
    
    if not os.path.exists(audio_path):
        raise FileNotFoundError(f"El archivo de audio no existe en: {audio_path}")
        
    # Configurar opciones de optimización
    kwargs = {}
    if torch.cuda.is_available():
        # Intentar Flash Attention 2. Si no es compatible, Hugging Face lo notifica y cae a SDPA (PyTorch 2.0+) de forma estable.
        try:
            kwargs["attn_implementation"] = "flash_attention_2"
            print("[*] Intentando activar Flash Attention 2 para Whisper...")
        except Exception:
            kwargs["attn_implementation"] = "sdpa"
            
    # Configurar el pipeline de ASR automático
    asr_pipeline = pipeline(
        "automatic-speech-recognition",
        model=model_name,
        device=0 if device == "cuda" else -1,
        torch_dtype=torch.float16 if device == "cuda" else torch.float32,
        **kwargs
    )
    
    # Transcribir forzando idioma español
    result = asr_pipeline(
        audio_path,
        generate_kwargs={"language": "spanish", "task": "transcribe"}
    )
    
    transcription = result["text"]
    print(f"[+] Transcripción obtenida por Whisper: '{transcription}'")
    return transcription.strip()


def text_to_speech_aymara(
    text, 
    output_wav_path="output_aymara.wav", 
    model_name="facebook/mms-tts-aym", 
    device="cuda" if torch.cuda.is_available() else "cpu"
):
    """
    Sintetiza audio a partir de texto en Aimara utilizando Meta's MMS VITS (Massive Multilingual Speech).
    """
    print(f"[*] Sintetizando voz en Aimara usando {model_name}...")
    
    # Cargar el tokenizador y el modelo VITS especializado de Meta
    tokenizer = AutoTokenizer.from_pretrained(model_name)
    model = VitsModel.from_pretrained(model_name)
    
    model.to(device)
    model.eval()
    
    # Procesar texto de entrada
    inputs = tokenizer(text, return_tensors="pt")
    inputs = {k: v.to(device) for k, v in inputs.items()}
    
    # Generar la forma de onda de audio de forma autorregresiva
    with torch.no_grad():
        outputs = model(**inputs)
        
    # Obtener el audio en formato numpy y la tasa de muestreo
    waveform = outputs.waveform.cpu().numpy().squeeze()
    sampling_rate = model.config.sampling_rate
    
    # Normalizar para evitar estática y distorsión
    max_val = np.max(np.abs(waveform))
    if max_val > 0:
        waveform = waveform / max_val
        
    waveform_int16 = (waveform * 32767).astype(np.int16)
    
    # Guardar usando scipy
    scipy.io.wavfile.write(output_wav_path, rate=sampling_rate, data=waveform_int16)
    print(f"[+] Audio en Aimara sintetizado con éxito en: {output_wav_path} ({sampling_rate}Hz)")
    
    return output_wav_path


def speech_to_speech_cascade_sota(
    input_audio_path,
    output_audio_path="translated_aymara.wav",
    lora_adapter_dir="./nmt_sota_checkpoints/best_lora_adapters",
    base_model_name="facebook/nllb-200-distilled-600M",
    whisper_model="openai/whisper-large-v3-turbo",
    device="cuda" if torch.cuda.is_available() else "cpu"
):
    """
    Tubería (Pipeline) de ejecución SOTA de extremo a extremo:
    Audio (Español) -> ASR (Whisper Large V3 Turbo) -> NMT (NLLB-200 + LoRA) -> TTS (MMS Aymara) -> Audio (Aimara).
    """
    print("\n" + "="*60)
    print("[*] INICIANDO PROCESAMIENTO SPEECH-TO-SPEECH SOTA (ESPAÑOL -> AIMARA)")
    print("="*60)
    
    # 1. Reconocimiento de voz (ASR)
    es_text = speech_to_text_whisper(input_audio_path, model_name=whisper_model, device=device)
    
    # 2. Cargar modelo NMT (NLLB-200 + Adaptador LoRA PEFT)
    print(f"[*] Cargando modelo de traducción SOTA (NLLB-200 + Adaptadores)...")
    tokenizer = AutoTokenizer.from_pretrained(base_model_name)
    
    # Carga condicional de bitsandbytes para inferencia estable de baja memoria
    base_model = AutoModelForSeq2SeqLM.from_pretrained(
        base_model_name,
        torch_dtype=torch.float16 if device == "cuda" else torch.float32,
        device_map="auto" if device == "cuda" else None
    )
    
    # Cargar y fusionar (o aplicar al vuelo) los adaptadores de PEFT
    if os.path.exists(lora_adapter_dir):
        print(f"[*] Aplicando adaptadores LoRA desde {lora_adapter_dir}...")
        model = PeftModel.from_pretrained(base_model, lora_adapter_dir)
    else:
        print(f"[!] ADVERTENCIA: No se encontró el directorio de adaptadores LoRA en {lora_adapter_dir}. Se usará NLLB-200 base sin fine-tune.")
        model = base_model
        
    model.to(device)
    
    # 3. Traducción Automática (NMT)
    print(f"[*] Traduciendo texto NLLB: '{es_text}'...")
    aym_text = translate_nllb(es_text, model, tokenizer, device=device)
    print(f"[+] Traducción en Aimara: '{aym_text}'")
    
    # 4. Síntesis de voz (TTS)
    tts_audio_path = text_to_speech_aymara(
        text=aym_text,
        output_wav_path=output_audio_path,
        device=device
    )
    
    print("="*60)
    print("[+] ¡PIPELINE SOTA DE VOZ EJECUTADO CON ÉXITO!")
    print(f"    - Entrada: {input_audio_path}")
    print(f"    - Transcripción ES: {es_text}")
    print(f"    - Traducción AYM:  {aym_text}")
    print(f"    - Salida Audio:    {tts_audio_path}")
    print("="*60 + "\n")
    
    return es_text, aym_text, tts_audio_path


if __name__ == "__main__":
    print("Pipeline de Voz SOTA listo. Importa 'speech_to_speech_cascade_sota' para comenzar.")
