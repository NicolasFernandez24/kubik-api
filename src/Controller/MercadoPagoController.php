<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Service\MercadoPagoService;

class MercadoPagoController
{
    private MercadoPagoService $service;

    public function __construct(MercadoPagoService $service)
    {
        $this->service = $service;
    }

    public function crearPreferencia(Request $request, Response $response): Response
{
    try {
        $data = json_decode((string) $request->getBody(), true);

        if (!$data) {
            return $this->error($response, 'JSON inválido', 400);
        }

        $result = $this->service->crearPreferencia($data);

        $response->getBody()->write(json_encode($result));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);

    } catch (\Throwable $e) {
        error_log($e->getMessage());

        return $this->error(
            $response,
            'Error creando preferencia',
            500
        );
    }
}


    private function error(Response $response, string $message, int $status): Response
    {
        $response->getBody()->write(json_encode([
            'error' => $message
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
