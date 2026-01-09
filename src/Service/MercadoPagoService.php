<?php
namespace App\Service;

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use App\Repository\SalaRepository;

class MercadoPagoService
{
    private SalaRepository $salaRepository;

    public function __construct(
        SalaRepository $salaRepository,
        string $accessToken
    ) {
        $this->salaRepository = $salaRepository;
        MercadoPagoConfig::setAccessToken($accessToken);
    }

   public function crearPreferencia(array $data): array
{
    $monto = (float) ($data['pagado'] ?? 0);

    if ($monto <= 0) {
        throw new \InvalidArgumentException('Monto inválido');
    }

    $client = new PreferenceClient();


    $externalReference = base64_encode(json_encode($data));

   try {
    $preference = $client->create([
        'items' => [[
            'title'       => 'Reserva sala de ensayo',
            'quantity'    => 1,
            'unit_price'  => $monto,
            'currency_id' => 'ARS'
        ]],
        'back_urls' => [
            'success' => $_ENV['MP_SUCCESS_URL'] ,
            'failure' => $_ENV['MP_FAILURE_URL'] ,
            'pending' => $_ENV['MP_PENDING_URL'] 
        ],
        'auto_return' => 'approved',
        'external_reference' => base64_encode(json_encode($data))
    ]);
} catch (\Throwable $e) {
    // 🔥 LOG REAL
    error_log('MercadoPago error: ' . $e->getMessage());
    throw $e;
}


    return [
        'preference_id' => $preference->id,
        'init_point'    => $preference->init_point
    ];
}

}
