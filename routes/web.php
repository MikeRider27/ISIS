<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ConsultaIpsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\IcvpController;
use App\Http\Controllers\MeowController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ValidarController;
use App\Http\Controllers\VhlController;
use App\Http\Controllers\VisorController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/visor/{id?}', [VisorController::class, 'index'])->name('visor');
    Route::post('/visor', [VisorController::class, 'store'])->name('visor.store');
    Route::get('/consulta-ips', [ConsultaIpsController::class, 'index'])->name('consulta-ips');
    Route::post('/consulta-ips/buscar', [ConsultaIpsController::class, 'buscarAjax'])->name('consulta-ips.buscar');
    Route::get('/vhl', [VhlController::class, 'index'])->name('vhl');
    Route::post('/vhl/buscar', [VhlController::class, 'buscar'])->name('vhl.buscar');
    Route::post('/vhl/generar', [VhlController::class, 'generar'])->name('vhl.generar');
    Route::post('/vhl/validar', [VhlController::class, 'validar'])->name('vhl.validar');
    Route::get('/icvp', [IcvpController::class, 'index'])->name('icvp');
    Route::post('/icvp/generar', [IcvpController::class, 'generar'])->name('icvp.generar');
    Route::get('/meow', [MeowController::class, 'index'])->name('meow');
    Route::post('/meow/generar', [MeowController::class, 'generar'])->name('meow.generar');
    Route::get('/validar', [ValidarController::class, 'index'])->name('validar');
    Route::post('/validar/verificar', [ValidarController::class, 'verificar'])->name('validar.verificar');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::match(['get', 'post'], '/logout', [LoginController::class, 'destroy'])->name('logout');
});
