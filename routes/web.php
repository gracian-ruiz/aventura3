<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BikeController;
use App\Http\Controllers\RevisionController;
use App\Http\Controllers\ComponentController;




/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});


Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/usuarios', [UserController::class, 'index'])->name('users.index');
    Route::get('/usuarios/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/usuarios', [UserController::class, 'store'])->name('users.store');
    Route::get('/usuarios/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/usuarios/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

Route::get('/usuarios-datatable', function () {
    return view('users.datatables');
})->name('users.datatable');
Route::get('/usuarios/data', [UserController::class, 'getUsers'])->name('users.data');


Route::middleware(['auth'])->group(function () {
    // Bicicletas
    Route::resource('bikes', BikeController::class);

    // Revisiones por bicicleta
    Route::get('bikes/{bike}/revisions', [RevisionController::class, 'index'])->name('bikes.revisions.index');
    Route::get('bikes/{bike}/revisions/create', [RevisionController::class, 'create'])->name('bikes.revisions.create');
    Route::post('bikes/{bike}/revisions', [RevisionController::class, 'store'])->name('bikes.revisions.store');
    Route::delete('revisions/{revision}', [RevisionController::class, 'destroy'])->name('revisions.destroy');

    Route::get('/revisions', [RevisionController::class, 'allRevisions'])->name('revisions.index');

    // Componentes
    Route::resource('components', ComponentController::class);
});

Route::prefix('bikes/{bike}/revisions')->middleware('auth')->group(function () {
    Route::get('/', [RevisionController::class, 'index'])->name('bikes.revisions.index');
    Route::get('/create', [RevisionController::class, 'create'])->name('bikes.revisions.create');
    Route::post('/', [RevisionController::class, 'store'])->name('bikes.revisions.store');
    Route::get('/{revision}/edit', [RevisionController::class, 'edit'])->name('bikes.revisions.edit');
    Route::put('/{revision}', [RevisionController::class, 'update'])->name('bikes.revisions.update');
    Route::delete('/{revision}', [RevisionController::class, 'destroy'])->name('bikes.revisions.destroy');
});

require __DIR__.'/auth.php';
