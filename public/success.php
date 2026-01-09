<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$frontendUrl = $_ENV['FRONTEND_URL'] ?? 'http://localhost:4200';

$status = $_GET['status'] ?? null;
$externalRef = $_GET['external_reference'] ?? null;

if ($status === 'approved' && $externalRef) {

    // ✅ PASO 1: base64 decode
    $decoded = base64_decode($externalRef, true);

    if ($decoded === false) {
        header("Location: {$frontendUrl}/reserva-error");
        exit;
    }

    // ✅ PASO 2: json decode
    $reservaData = json_decode($decoded, true);

    if (!is_array($reservaData)) {
        header("Location: {$frontendUrl}/reserva-error");
        exit;
    }

    $reservaJson = urlencode(json_encode($reservaData));

    header("Location: {$frontendUrl}/reserva-success?data={$reservaJson}");
    exit;
}

header("Location: {$frontendUrl}/reserva-error");
exit;
