<?php
require __DIR__ . '/../vendor/autoload.php';

// Parámetros que devuelve MercadoPago
$status = $_GET['status'] ?? null;
$external_ref = $_GET['external_reference'] ?? null;

if ($status === 'approved' && $external_ref) {
    // Decodificar los datos de la reserva
    $reservaData = json_decode($external_ref, true);
    $reservaJson = urlencode(json_encode($reservaData));

    
    header("Location: http://localhost:4200/reserva-success?data={$reservaJson}");
    exit;
} else {
    header("Location: http://localhost:4200/reserva-error");
    exit;
}
