import json
import os

def py_to_ipynb(py_path, ipynb_path):
    with open(py_path, "r", encoding="utf-8") as f:
        content = f.read()
        
    lines = content.splitlines()
    cells = []
    current_cell_type = None
    current_cell_lines = []
    
    for line in lines:
        if line.startswith("# %% [markdown]"):
            if current_cell_type is not None:
                cells.append({
                    "cell_type": current_cell_type,
                    "metadata": {},
                    "source": [l + "\n" for l in current_cell_lines]
                })
            current_cell_type = "markdown"
            current_cell_lines = []
        elif line.startswith("# %%"):
            if current_cell_type is not None:
                cells.append({
                    "cell_type": current_cell_type,
                    "metadata": {},
                    "source": [l + "\n" for l in current_cell_lines]
                })
            current_cell_type = "code"
            current_cell_lines = []
        else:
            if current_cell_type == "markdown":
                # Quitar el comentario inicial '# ' para que sea Markdown real
                if line.startswith("# "):
                    current_cell_lines.append(line[2:])
                elif line.startswith("#"):
                    current_cell_lines.append(line[1:])
                else:
                    current_cell_lines.append(line)
            else:
                current_cell_lines.append(line)
                
    if current_cell_type is not None:
        cells.append({
            "cell_type": current_cell_type,
            "metadata": {},
            "source": [l + "\n" for l in current_cell_lines]
        })
        
    # Limpieza final de líneas vacías e indentación
    for cell in cells:
        # Quitar líneas vacías al final de cada celda
        while cell["source"] and cell["source"][-1] == "\n":
            cell["source"].pop()
        # Asegurar que el último elemento no tenga un salto de línea adicional al final
        if cell["source"]:
            cell["source"][-1] = cell["source"][-1].rstrip("\n")
            
        if cell["cell_type"] == "code":
            cell["outputs"] = []
            cell["execution_count"] = None
            
    notebook = {
        "cells": cells,
        "metadata": {
            "kernelspec": {
                "display_name": "Python 3 (ipykernel)",
                "language": "python",
                "name": "python3"
            },
            "language_info": {
                "name": "python"
            }
        },
        "nbformat": 4,
        "nbformat_minor": 2
    }
    
    with open(ipynb_path, "w", encoding="utf-8") as f:
        json.dump(notebook, f, indent=1, ensure_ascii=False)
        
    print(f"[+] Convertido {py_path} a {ipynb_path} exitosamente.")

if __name__ == "__main__":
    # 1. Convertir demo_explicacion.py
    py_path1 = os.path.join(os.path.dirname(__file__), "demo_explicacion.py")
    ipynb_path1 = os.path.join(os.path.dirname(__file__), "demo_explicacion.ipynb")
    py_to_ipynb(py_path1, ipynb_path1)
    
    # 2. Convertir comparativa_tokenizadores.py
    py_path2 = os.path.join(os.path.dirname(__file__), "comparativa_tokenizadores.py")
    ipynb_path2 = os.path.join(os.path.dirname(__file__), "comparativa_tokenizadores.ipynb")
    py_to_ipynb(py_path2, ipynb_path2)
