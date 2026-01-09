<?php

use App\Database\Database;
use Psr\Container\ContainerInterface;



return [

 
    'settings' => function () {
        return require __DIR__ . '/settings.php';
    },

 
    Database::class => function (ContainerInterface $c) {
        $config = $c->get('settings')['db'];
        return new Database($config);
    },

   
    App\Repository\UsuarioRepository::class => function ($c) {
        return new App\Repository\UsuarioRepository(
            $c->get(Database::class)->getConnection()
        );
    },

    App\Repository\SalaRepository::class => function ($c) {
        return new App\Repository\SalaRepository(
            $c->get(Database::class)->getConnection()
        );
    },

    App\Repository\ReservaRepository::class => function ($c) {
        return new App\Repository\ReservaRepository(
            $c->get(Database::class)->getConnection()
        );
    },

  
    App\Service\AuthService::class => function ($c) {
        return new App\Service\AuthService(
            $c->get(App\Repository\UsuarioRepository::class)
        );
    },

    App\Service\SalaService::class => function ($c) {
        return new App\Service\SalaService(
            $c->get(App\Repository\SalaRepository::class),
            $c->get(App\Utils\FileUploader::class)
        );
    },

    App\Service\ReservaService::class => function ($c) {
        return new App\Service\ReservaService(
            $c->get(App\Repository\ReservaRepository::class)
        );
    },
    App\Service\MercadoPagoService::class => function ($c) {
    return new App\Service\MercadoPagoService(
        $c->get(App\Repository\SalaRepository::class),
        $c->get('settings')['mercadopago']['access_token'] 
    );
},

    
    App\Utils\FileUploader::class => function () {
        return new App\Utils\FileUploader(
            'C:/Users/Equipo/Desktop/proga/kubik/src/assets/salas'
        );
    },

  

    App\Controller\LoginController::class => function ($c) {
        return new App\Controller\LoginController(
            $c->get(App\Service\AuthService::class)
        );
    },

    App\Controller\SalasController::class => function ($c) {
        return new App\Controller\SalasController(
            $c->get(App\Service\SalaService::class)
        );
    },

    App\Controller\ReservasController::class => function ($c) {
    return new App\Controller\ReservasController(
        $c->get(App\Service\ReservaService::class),
        $c->get(App\Repository\ReservaRepository::class)
    );
},


    
    App\Controller\UsuarioController::class => function ($c) {
        return new App\Controller\UsuarioController(
            $c->get(Database::class)
        );
    },

  
   App\Controller\MercadoPagoController::class => function ($c) {
    return new App\Controller\MercadoPagoController(
        $c->get(App\Service\MercadoPagoService::class)
    );
},

];
