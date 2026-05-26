#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Script para graficar la Matriz de Confusión y el Reporte de Clasificación.
Genera imágenes científicas premium a partir de los resultados de texto del modelo.
"""

import os
import numpy as np
import matplotlib.pyplot as plt
import seaborn as sns

def main():
    print("[*] Iniciando generación de gráficos científicos de resultados...")
    
    # ─────────────────────────────────────────────────────────────────────────
    # 1. DATOS REALES EXTRAÍDOS DEL REPORTE
    # ─────────────────────────────────────────────────────────────────────────
    # Matriz de Confusión
    cm = np.array([
        [48, 52, 93],
        [44, 74, 108],
        [67, 86, 144]
    ])
    
    classes = ["Clase 0", "Clase 1", "Clase 2"]
    
    # Reporte de Clasificación
    # [precision, recall, f1-score]
    metrics = {
        "Clase 0": [0.30, 0.25, 0.27],
        "Clase 1": [0.35, 0.33, 0.34],
        "Clase 2": [0.42, 0.48, 0.45]
    }
    
    # Estilo de graficación Premium (Fondo Oscuro elegante)
    plt.style.use('dark_background')
    
    # ─────────────────────────────────────────────────────────────────────────
    # GRÁFICO 1: Mapa de Calor de la Matriz de Confusión
    # ─────────────────────────────────────────────────────────────────────────
    fig1, ax1 = plt.subplots(figsize=(6.5, 5))
    fig1.patch.set_facecolor('#0A0C16')
    ax1.set_facecolor('#12162B')
    
    # Normalizar para mostrar porcentajes por fila (Recall por clase)
    cm_norm = cm.astype('float') / cm.sum(axis=1)[:, np.newaxis]
    
    # Combinar número absoluto con porcentaje para las anotaciones
    labels = np.empty_like(cm, dtype=object)
    for i in range(cm.shape[0]):
        for j in range(cm.shape[1]):
            labels[i, j] = f"{cm[i, j]}\n({cm_norm[i, j]*100:.1f}%)"
            
    # Graficar heatmap usando paleta violeta/cian acorde a LNT-IA
    sns.heatmap(cm_norm, annot=labels, fmt="", cmap="Purples", cbar=True,
                xticklabels=classes, yticklabels=classes, ax=ax1,
                annot_kws={"size": 10, "weight": "bold"})
    
    ax1.set_title("Mapa de Calor de la Matriz de Confusión\n(Accuracy: 37.15%)", 
                  fontsize=12, fontweight="bold", pad=15, color="#FFFFFF")
    ax1.set_xlabel("Clase Predicha (Predicted)", fontsize=10, labelpad=10, color="#94A3B8")
    ax1.set_ylabel("Clase Real (True)", fontsize=10, labelpad=10, color="#94A3B8")
    
    plt.tight_layout()
    output_cm = "matriz_confusion_heatmap.png"
    plt.savefig(output_cm, dpi=300, facecolor=fig1.get_facecolor(), edgecolor='none')
    print(f"[+] Guardado con éxito: {os.path.abspath(output_cm)}")
    plt.close()
    
    # ─────────────────────────────────────────────────────────────────────────
    # GRÁFICO 2: Comparativa de Métricas (Precision, Recall, F1-Score)
    # ─────────────────────────────────────────────────────────────────────────
    fig2, ax2 = plt.subplots(figsize=(7.5, 4.8))
    fig2.patch.set_facecolor('#0A0C16')
    ax2.set_facecolor('#12162B')
    
    # Agrupar datos por métrica
    precisions = [metrics[c][0] for c in classes]
    recalls = [metrics[c][1] for c in classes]
    f1_scores = [metrics[c][2] for c in classes]
    
    x = np.arange(len(classes))
    width = 0.25  # Ancho de las barras
    
    rects1 = ax2.bar(x - width, precisions, width, label='Precision', color='#8B5CF6', alpha=0.9)
    rects2 = ax2.bar(x, recalls, width, label='Recall (Sensibilidad)', color='#06B6D4', alpha=0.9)
    rects3 = ax2.bar(x + width, f1_scores, width, label='F1-Score', color='#10B981', alpha=0.9)
    
    # Añadir etiquetas de valor arriba de cada barra
    def autolabel(rects):
        for rect in rects:
            height = rect.get_height()
            ax2.annotate(f'{height:.2f}',
                        xy=(rect.get_x() + rect.get_width() / 2, height),
                        xytext=(0, 3),  # 3 points vertical offset
                        textcoords="offset points",
                        ha='center', va='bottom', fontsize=8, color='#FFFFFF', fontweight='bold')
            
    autolabel(rects1)
    autolabel(rects2)
    autolabel(rects3)
    
    ax2.set_title("Comparativa Científica de Métricas por Clase", 
                  fontsize=12, fontweight="bold", pad=15, color="#FFFFFF")
    ax2.set_xticks(x)
    ax2.set_xticklabels(classes, fontsize=9, color="#FFFFFF")
    ax2.set_ylabel("Valor (Escala de 0 a 1)", fontsize=10, labelpad=8, color="#94A3B8")
    ax2.set_ylim(0, 0.6)  # Escala ajustada
    
    # Cuadrícula de fondo
    ax2.grid(True, color="#1E293B", linewidth=0.5, linestyle="--", axis='y')
    ax2.set_axisbelow(True)
    
    # Leyenda premium
    ax2.legend(loc="upper left", framealpha=0.2, edgecolor="#8B5CF6")
    
    # Remover bordes innecesarios
    for spine in ax2.spines.values():
        spine.set_edgecolor("#1E293B")
        
    plt.tight_layout()
    output_rep = "reporte_clasificacion_chart.png"
    plt.savefig(output_rep, dpi=300, facecolor=fig2.get_facecolor(), edgecolor='none')
    print(f"[+] Guardado con éxito: {os.path.abspath(output_rep)}")
    plt.close()
    
    print("\n[+] ¡Todos los gráficos científicos han sido generados exitosamente!")

if __name__ == "__main__":
    main()
