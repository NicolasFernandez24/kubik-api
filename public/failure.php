<?php
require __DIR__ . '/../vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// URL del frontend
$frontendUrl = $_ENV['FRONTEND_URL'] ?? 'http://localhost:4200';

// Redirigir siempre a la página de error
header("Location: {$frontendUrl}/reserva-error");
exit;
