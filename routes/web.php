<?php
/**
 * Definición de Rutas Web del Traductor Inteligente Laravel.
 *
 * Autor: Ingeniero Experto en IA, NLP & Laravel
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TranslatorController;

// 1. Vistas Principales del Dashboard Administrativo y Traducción
Route::get('/', [TranslatorController::class, 'index'])->name('translator.index');
Route::get('/reports', [TranslatorController::class, 'reports'])->name('translator.reports');

// 2. Rutas Proxy API para controlar el entrenamiento en GPU en background
Route::post('/api/train/start', [TranslatorController::class, 'startTraining'])->name('api.train.start');
Route::get('/api/train/status', [TranslatorController::class, 'getTrainingStatus'])->name('api.train.status');
