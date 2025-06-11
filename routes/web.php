<?php

use App\Http\Controllers\Alquiler\AlquilerController;
use App\Http\Controllers\Alquiler\AventuraBikeController;
use App\Http\Controllers\Alquiler\MaterialController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BikeController;
use App\Http\Controllers\RevisionController;
use App\Http\Controllers\ComponentController;
use App\Http\Controllers\RecordatorioController;
use App\Http\Controllers\AvisoEnviadoController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\PresupuestoController;
use App\Http\Controllers\Alquiler\UsuarioAlquilerController;
use App\Http\Controllers\MecanicoController;
use App\Http\Controllers\WhatsappController;
use App\Models\Bike;
use App\Models\Material;

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


// Rutas protegidas por autenticación
Route::middleware(['auth', 'admin'])->group(function () {

    // ✅ Bicicletas
    Route::resource('bikes', BikeController::class);

    // ✅ Revisiones en general (todas las revisiones)
    Route::get('/revisions', [RevisionController::class, 'allRevisions'])->name('revisions.index');

    // ✅ Revisiones específicas por bicicleta
    Route::prefix('bikes/{bike}/revisions')->group(function () {
        Route::get('/', [RevisionController::class, 'index'])->name('bikes.revisions.index');
        Route::get('/create', [RevisionController::class, 'create'])->name('bikes.revisions.create');
        Route::post('/', [RevisionController::class, 'store'])->name('bikes.revisions.store');
        Route::get('/{revision}/edit', [RevisionController::class, 'edit'])->name('bikes.revisions.edit');
        Route::put('/{revision}', [RevisionController::class, 'update'])->name('bikes.revisions.update');
        Route::delete('/{revision}', [RevisionController::class, 'destroy'])->name('bikes.revisions.destroy');
    });

    // ✅ Componentes
    Route::resource('components', ComponentController::class);

    Route::get('/avisos-enviados', [AvisoEnviadoController::class, 'index'])->name('avisos.index');
    Route::get('/enviar-recordatorios', [RecordatorioController::class, 'enviarRecordatorios'])->name('enviar.recordatorios');



    // Rutas de citas
    Route::get('/citas', [AppointmentController::class, 'index'])->name('appointments.index'); // Ver citas pendientes
    Route::get('/mecanico', [MecanicoController::class, 'index'])->name('mecanico.index'); // Ver citas pendientes
    Route::get('/citas/historico', [AppointmentController::class, 'historico'])->name('appointments.historico');
    // Ver citas completadas
    Route::post('/citas', [AppointmentController::class, 'store'])->name('appointments.store'); // Registrar nueva cita
    Route::put('/citas/{appointment}/updateEstado', [AppointmentController::class, 'updateEstado'])->name('appointments.updateEstado'); // Completar cita
    Route::delete('/citas/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy'); // Eliminar cita

    Route::post('/mecanico', [MecanicoController::class, 'store'])->name('mecanico.store'); // Registrar nueva cita
    Route::put('/mecanico/{appointment}/updateEstado', [MecanicoController::class, 'updateEstado'])->name('mecanico.updateEstado'); // Completar cita
    Route::delete('/mecanico/{appointment}', [MecanicoController::class, 'destroy'])->name('mecanico.destroy'); // Eliminar cita

    Route::get('/citas/create', [AppointmentController::class, 'create'])->name('appointments.create');
    Route::get('/citas/{appointment}/edit', [AppointmentController::class, 'edit'])->name('appointments.edit');
    Route::put('/citas/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');

    Route::get('/mecanico/create', [MecanicoController::class, 'create'])->name('mecanico.create');
    Route::get('/mecanico/{appointment}/edit', [MecanicoController::class, 'edit'])->name('mecanico.edit');
    Route::put('/mecanico/{appointment}', [MecanicoController::class, 'update'])->name('mecanico.update');

    Route::get('/citas/{appointment}/confirm', [AppointmentController::class, 'confirmCompletion'])
        ->name('appointments.confirmCompletion');

    Route::get('/mecanico/{appointment}/confirm', [MecanicoController::class, 'confirmCompletion'])
        ->name('mecanico.confirmCompletion');    

    Route::put('/citas/{appointment}/complete', [AppointmentController::class, 'complete'])
        ->name('appointments.complete');
    Route::get('/citas/{appointment}', [AppointmentController::class, 'show'])->name('appointments.show');


    Route::put('/mecanico/{appointment}/complete', [MecanicoController::class, 'complete'])
    ->name('mecanico.complete');
Route::get('/mecanico/{appointment}', [MecanicoController::class, 'show'])->name('mecanico.show');


    // ✅ Nueva ruta para update con otro nombre
    Route::put('/{appointment}/updatedos', [AppointmentController::class, 'updatedos'])->name('appointments.updatedos');
    Route::get('/citas/{appointment}/reparacion', [AppointmentController::class, 'showReparacion'])->name('appointments.reparacion.show');
    Route::put('/citas/{appointment}/reparacion', [AppointmentController::class, 'updateReparacion'])->name('appointments.updateReparacion');
    Route::delete('/presupuestos/{id}', [PresupuestoController::class, 'destroy'])->name('presupuestos.destroy');


    Route::put('mecanico/{appointment}/updatedos', [MecanicoController::class, 'updatedos'])->name('mecanico.updatedos');
    Route::get('/mecanico/{appointment}/reparacion', [MecanicoController::class, 'showReparacion'])->name('mecanico.reparacion.show');
    Route::put('/mecanico/{appointment}/reparacion', [MecanicoController::class, 'updateReparacion'])->name('mecanico.updateReparacion');









    





    Route::resource('presupuestos', PresupuestoController::class);
    Route::get('/bikes/by-user/{userId}', function ($userId) {
        return response()->json(Bike::where('user_id', $userId)->get());
    });

    Route::get('/presupuestos/create/{user}', [PresupuestoController::class, 'create'])->name('presupuestos.create');

    Route::get('/presupuestos/{id}/factura', [PresupuestoController::class, 'factura'])->name('presupuestos.factura');
    Route::get('/presupuestos/{id}/pdf', [PresupuestoController::class, 'descargarPDF'])->name('presupuestos.pdf');
    Route::get('/presupuestos/{id}', [PresupuestoController::class, 'show'])->name('presupuestos.show');
    Route::patch('/presupuestos/{presupuesto}/estado', [PresupuestoController::class, 'actualizarEstado'])->name('presupuestos.actualizarEstado');
    



    Route::get('/presupuesto/{clienteId}/{presupuestoId}/enviar', [WhatsAppController::class, 'enviarPresupuestoWhatsApp'])
    ->name('presupuesto.enviar');




    //ALQUILER


    // Listado de usuarios de alquiler
    Route::get('/usuarios_alquiler', [UsuarioAlquilerController::class, 'index'])->name('usuarios_alquiler.index');
    
    // Formulario para crear nuevo usuario de alquiler
    Route::get('/usuarios_alquiler/create', [UsuarioAlquilerController::class, 'create'])->name('usuarios_alquiler.create');
    
    // Guardar nuevo usuario de alquiler
    Route::post('/usuarios_alquiler', [UsuarioAlquilerController::class, 'store'])->name('usuarios_alquiler.store');
    
    // Ver usuario de alquiler específico (opcional)
    Route::get('/usuarios_alquiler/{usuario_alquiler}', [UsuarioAlquilerController::class, 'show'])->name('usuarios_alquiler.show');
    
    // Formulario para editar usuario de alquiler
    Route::get('/usuarios_alquiler/{usuario_alquiler}/edit', [UsuarioAlquilerController::class, 'edit'])->name('usuarios_alquiler.edit');
    
    // Actualizar usuario de alquiler
    Route::put('/usuarios_alquiler/{usuario_alquiler}', [UsuarioAlquilerController::class, 'update'])->name('usuarios_alquiler.update');
    
    // Eliminar usuario de alquiler
    Route::delete('/usuarios_alquiler/{usuario_alquiler}', [UsuarioAlquilerController::class, 'destroy'])->name('usuarios_alquiler.destroy');
    
    Route::resource('material', MaterialController::class);

    Route::get('usuarios/{usuario_alquiler}/alquileres/create', [AlquilerController::class, 'create'])->name('alquiler.create');

    Route::post('usuarios/{usuario_alquiler}/alquileres', [AlquilerController::class, 'store'])->name('alquiler.store');

    Route::get('/alquileres', [\App\Http\Controllers\Alquiler\AlquilerController::class, 'index'])->name('alquileres.index');



    // Ruta para obtener materiales disponibles
Route::get('/alquileres/materiales-disponibles', [AlquilerController::class, 'bicicletasDisponibles']);

Route::get('/alquileres/{alquiler}/edit', [AlquilerController::class, 'edit'])->name('alquileres.edit');
Route::put('/alquileres/{alquiler}', [AlquilerController::class, 'update'])->name('alquileres.update');

Route::delete('/alquileres/materiales/{pivotId}', [AlquilerController::class, 'eliminarMaterial'])
    ->name('alquileres.materiales.destroy');


Route::post('/alquileres/{alquiler}/materiales', [AlquilerController::class, 'añadirMaterial'])
->name('alquileres.materiales.store');

Route::get('/alquileres/finalizado', [\App\Http\Controllers\Alquiler\AlquilerController::class, 'finalizado'])->name('alquileres.finalizado');




// Ruta para obtener materiales disponibles
// web.php
Route::post('/alquileres/{alquiler}/materiales', [AlquilerController::class, 'addMateriales'])->name('alquileres.addMateriales');

Route::get('/alquileres/{id}', [AlquilerController::class, 'show'])->name('alquileres.show');
Route::patch('alquileres/material/{pivotId}/devolver', [AlquilerController::class, 'devolverMaterial'])
    ->name('alquileres.material.devolver');

Route::patch('/alquileres/{alquiler}/finalizar', [AlquilerController::class, 'finalizar'])->name('alquileres.finalizar');

Route::delete('/alquileres/{alquiler}', [AlquilerController::class, 'destroy'])->name('alquileres.destroy');










});


Route::get('/confirmacion/presupuesto/{presupuestoId}', [PresupuestoController::class, 'confirmarPresupuesto'])
    ->name('presupuesto.confirmar');

Route::post('/confirmacion/presupuesto/{presupuestoId}', [PresupuestoController::class, 'procesarConfirmacion'])
    ->name('presupuesto.procesar');





Route::get('/alquiler/bicicletas', [AventuraBikeController::class, 'bicismontaña'])
    ->name('bicismontaña');

Route::post('/alquiler/bicicletas/montana', [AventuraBikeController::class, 'store'])->middleware('throttle:5,1')->name('addbicismontaña');

Route::post('/verificar-disponibilidad', [
    AventuraBikeController::class, 'comprobarDisponibilidad'])->name('bicicletas.disponibilidad');










require __DIR__ . '/auth.php';
