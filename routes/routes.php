<?php
use Slim\App;
use App\Controller\UsuarioController;
use App\Controller\SalasController;
use App\Controller\ReservasController;
use App\Controller\LoginController;
use App\Controller\MercadoPagoController;
return function (App $app) {
    // Rutas de usuarios
    $app->get('/usuarios', [UsuarioController::class, 'getAll']);
    $app->get('/usuarios/{id}', [UsuarioController::class, 'getById']);
    $app->post('/usuarios', [UsuarioController::class, 'create']);
    $app->put('/usuarios/{id}', [UsuarioController::class, 'update']);
    $app->delete('/usuarios/{id}', [UsuarioController::class, 'delete']);

    // Rutas de salas
    $app->get('/salas', [SalasController::class, 'GetAll']);
    $app->get('/salas/{id}', [SalasController::class, 'getById']);
    $app->post('/salas', [SalasController::class, 'create']);
    $app->post('/salas/{id}', [SalasController::class, 'update']);
    $app->delete('/salas/{id}', [SalasController::class, 'delete']);

    // Rutas de reservas
    $app->get('/reservas', [ReservasController::class, 'getAll']);
    $app->get('/reservas/{id}', [ReservasController::class, 'getById']);
    $app->get('/reservas/usuario/{usuario_id}', [ReservasController::class, 'getByUsuarioId']);
    $app->get('/reservas/sala/{sala_id}', [ReservasController::class, 'getBySalaId']);
    $app->get('/reservas/horarios-disponibles/{sala_id}', [ReservasController::class, 'getHorariosDisponibles']); 
    $app->post('/reservas', [Reservascontroller::class, 'create']);
    $app->put('/reservas/{id}', [ReservasController::class, 'update']);
    $app->delete('/reservas/{id}', [ReservasController::class, 'delete']);

    // Rutas de login 
    $app->post('/login', [LoginController::class, 'login']);

    $app->post('/mercadopago/preferencia', [MercadoPagoController::class, 'crearPreferencia']);
   
};