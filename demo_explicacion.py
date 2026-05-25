# %% [markdown]
# # 🎓 El Teléfono Mágico Español ⇄ Aimara
# ### Explicación Paso a Paso del Procesamiento de Lenguaje Natural (NLP)
# 
# Este notebook está diseñado para ejecutarse celda por celda en **Jupyter Lab** o **VS Code Interactive**.
# Nos enseñará exactamente cómo las computadoras "escuchan" nuestra voz, cómo "entienden" el lenguaje y cómo "hablan".
# 
# ---
# 
# ## 👶 Explicación para un Niño: Los 3 Magos del Teléfono Mágico
# 
# Imagina que tenemos un teléfono de juguete mágico. Cuando un niño habla en **Español**, el teléfono hace que otro niño escuche en **Aimara**. 
# Para lograr este truco de magia, hay **tres pequeños magos** viviendo dentro del teléfono:
# 
# 1. 👂 **Willy el Escuchador (ASR - Whisper):**
#    Tiene orejas de murciélago. Su trabajo es escuchar el sonido que entra por el micrófono (que son como onditas de agua en el aire) y escribirlo en un papel con letras en **Español**.
# 
# 2. 🧠 **Nico el Traductor (NMT - NLLB-200 + LoRA):**
#    Es el cerebro del grupo. Lee el papel en Español de Willy y lo traduce a palabras escritas en **Aimara**. Pero Nico tiene un secreto: ¡el Aimara es como jugar con piezas de **LEGO**! Las palabras se arman pegando pequeños bloquecitos. Así que Nico usa unas tijeras mágicas llamadas **BPE (Slicing)** para cortar las palabras en bloquecitos y luego las vuelve a armar en Aimara usando su supercomputadora cerebral (un **Transformer**).
# 
# 3. 🗣️ **Mimi la Habladora (TTS - Meta MMS):**
#    Tiene una voz hermosa. Toma el papel en Aimara que escribió Nico, lee las palabras y las canta por los parlantes como si fuera una persona de verdad.
# 
# ¡Ahora vamos a ver el código de cómo trabajan estos magos!

# %% [markdown]
# ---
# ## 👂 Mago 1: ¿Cómo Willy (Whisper) ve las ondas del sonido?
# 
# Cuando hablamos, nuestra voz crea vibraciones en el aire. Para una computadora, el sonido no son letras, ¡son números!
# Vamos a generar una onda de sonido artificial y ver cómo la ve la computadora.

# %%
import numpy as np
import matplotlib.pyplot as plt

# Crear un segundo de sonido de un silbido suave (440 Hz - nota musical La)
frecuencia_muestreo = 16000  # 16,000 números por segundo (estándar de Whisper)
tiempo = np.linspace(0, 0.05, int(frecuencia_muestreo * 0.05))  # Ver solo 0.05 segundos
onda_sonora = np.sin(2 * np.pi * 440 * tiempo)

# Graficar cómo se ve la voz para Willy
plt.figure(figsize=(10, 4))
plt.plot(tiempo, onda_sonora, color="#06b6d4", linewidth=2.5)
plt.title("📈 Cómo Willy (Whisper) ve tu silbido en el aire (Onda de Sonido)", fontsize=12, fontweight='bold')
plt.xlabel("Tiempo (segundos)", fontsize=10)
plt.ylabel("Amplitud (Fuerza de la voz)", fontsize=10)
plt.grid(True, alpha=0.3)
plt.show()

print("¡Willy toma miles de estos puntos celestes por segundo para entender qué sonido hiciste!")

# %% [markdown]
# ---
# ## 🧠 Mago 2: ¿Cómo Nico (NMT) corta las palabras con sus Tijeras (BPE)?
# 
# En lenguas como el **Aimara**, las palabras se forman uniendo piezas de LEGO (morfemas).
# Por ejemplo, la palabra **"aruskipapxañanakasakipunirakispawa"** significa *"es una obligación mutua hablar entre nosotros"*. ¡Es larguísima!
# 
# Nico usa un algoritmo llamado **BPE (Byte-Pair Encoding)** que actúa como unas tijeras que cortan palabras largas en sílabas o partes pequeñas llamadas **Tokens**.
# 
# Ejecuta esta celda para ver un simulador en Python de cómo funcionan estas "tijeras de palabras":

# %%
def tijeras_bpe(palabra, vocabulario_piezas):
    """
    Simula cómo un tokenizador BPE corta una palabra en trozos conocidos.
    """
    palabra_restante = palabra
    piezas_encontradas = []
    
    # Intentamos buscar los bloquecitos más grandes que Nico ya conoce
    while len(palabra_restante) > 0:
        encontrado = False
        for pieza in sorted(vocabulario_piezas, key=len, reverse=True):
            if palabra_restante.startswith(pieza):
                piezas_encontradas.append(pieza)
                palabra_restante = palabra_restante[len(pieza):]
                encontrado = True
                break
        if not encontrado:
            # Si hay una letra desconocida, la saca como un bloque unitario
            piezas_encontradas.append(palabra_restante[0])
            palabra_restante = palabra_restante[1:]
            
    return piezas_encontradas

# Las piezas de LEGO que Nico ya aprendió en sus clases de Aimara
bloques_lego = ["arus", "ki", "pap", "xa", "ña", "naka", "saka", "puni", "raki", "spa", "wa", "arunaka", "taki"]

# Palabra a cortar
palabra_aimara = "aruskipapxañanakasakipunirakispawa"

# Cortar la palabra
resultado = tijeras_bpe(palabra_aimara, bloques_lego)

print("🧩 PALABRA ORIGINAL EN AIMARA:")
print(f"👉 {palabra_aimara}\n")
print("✂️ PIEZAS DE LEGO CORTADAS POR EL TOKENIZADOR BPE:")
print(" | ".join(resultado))
print(f"\n¡Nico convirtió una palabra enorme en {len(resultado)} piezas de LEGO simples para poder entenderla!")

# %% [markdown]
# ---
# ## 🚀 Mago 3: Conexión con los Magos Reales de tu GPU local
# 
# Ya que tienes levantados los servidores locales de **FastAPI (puerto 8000)** en tu PC con la **GPU RTX 5060**, 
# podemos hacerle preguntas a los magos reales directamente desde este Jupyter Notebook.
# 
# Vamos a enviar una frase en Español a **Nico** para que la traduzca en su cerebro neuronal y ver el resultado.

# %%
import requests

# URL del cerebro de la GPU
API_URL = "http://127.0.0.1:8000/api"

def hablar_con_nico(texto_espanol):
    payload = {
        "text": texto_espanol,
        "source_lang": "spa_Latn",
        "target_lang": "ayr_Latn"
    }
    
    print(f"✈️ Enviando frase a la GPU: '{texto_espanol}'...")
    try:
        response = requests.post(f"{API_URL}/translate", json=payload)
        if response.status_code == 200:
            datos = response.json()
            print("\n🎉 ¡RESPUESTA DEL MAGO NICO!")
            print(f"🌐 Español: {datos['original_text']}")
            print(f"⚡ Aimara:   {datos['translated_text']}")
        else:
            print("❌ Ocurrió un error en el servidor de la GPU:", response.text)
    except Exception as e:
        print("❌ No se pudo conectar con el microservicio GPU. Asegúrate de tener corriendo 'python app.py' en tu terminal.")

# Probemos con una frase de ejemplo
hablar_con_nico("el hablar con los amigos es hermoso")

# %% [markdown]
# ---
# ## 🔊 Mago 4: Escuchando la Voz de Mimi (Text-to-Speech)
# 
# Finalmente, cuando Nico tiene las palabras en Aimara, **Mimi** las convierte en ondas y las guarda en un archivo de sonido.
# Hagamos que Mimi genere la voz de la traducción en tiempo real y la guarde en tu carpeta.

# %%
import os

def pedir_voz_a_mimi(texto_aimara, nombre_archivo="voz_mimi.wav"):
    payload = {
        "text": texto_aimara
    }
    
    print(f"🗣️ Pidiéndole a Mimi que lea: '{texto_aimara}'...")
    try:
        response = requests.post(f"{API_URL}/text-to-speech", json=payload)
        if response.status_code == 200:
            with open(nombre_archivo, "wb") as f:
                f.write(response.content)
            print(f"💾 ¡Voz guardada exitosamente en: '{os.path.abspath(nombre_archivo)}'!")
            print("🔊 Puedes abrir este archivo en tu computadora para escuchar a Mimi hablar en Aimara de verdad.")
        else:
            print("❌ Ocurrió un error en el servidor de síntesis de voz:", response.text)
    except Exception as e:
        print("❌ No se pudo conectar con la GPU. Asegúrate de tener corriendo 'python app.py'.")

# Generar el archivo de voz para la frase en Aimara: "Aruskipaniwa"
pedir_voz_a_mimi("Aruskipaniwa")

# %% [markdown]
# ---
# ## 🏆 ¡Resumen de Magia del Sistema SOTA!
# 
# Ahora entiendes todo el circuito:
# 1. Hablas ➔ **Willy (Whisper)** lo convierte de **Sonido a Números y luego a Texto (Español)**.
# 2. **Nico (NLLB-200 + BPE)** corta el texto en **Piezas de LEGO (Tokens)** y las traduce usando **Atención Neuronal a Aimara**.
# 3. **Mimi (MMS)** toma las palabras y pinta de vuelta una **Onda de Sonido (Voz de Aimara)**.
# 
# Todo ocurre en tu GPU NVIDIA RTX 5060 local de manera extremadamente rápida. ¡Ahora puedes seguir experimentando en Jupyter Lab!
