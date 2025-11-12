
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

   
    App\Controller\UsuarioController::class => function(ContainerInterface $c) {
        return new App\Controller\UsuarioController($c->get(Database::class));
    },
    
    App\Controller\SalasController::class => function(ContainerInterface $c) {
        return new App\Controller\SalasController($c->get(Database::class));
    },

   App\Controller\ReservasController::class => function(ContainerInterface $c) {
        return new App\Controller\ReservasController($c->get(Database::class));
    },

    App\Controller\LoginController::class => function(ContainerInterface $c){
        return new App\Controller\LoginController($c->get(Database::class));
    },
    App\Controller\MercadoPagoController::class => function (ContainerInterface $c) {
        $settings = $c->get('settings');
    return new App\Controller\MercadoPagoController($settings,$c->get(Database::class));
},

];
