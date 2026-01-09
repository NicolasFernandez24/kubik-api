<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Database\Database;
use App\Repository\ReservaRepository;
use App\Service\ReservaService;

class ReservasController
{
    private ReservaService $service;
    private ReservaRepository $repo;

    public function __construct(
        ReservaService $service,
        ReservaRepository $repo
    ) {
        $this->service = $service;
        $this->repo = $repo;
    }


    public function getAll(Request $request, Response $response): Response
    {
        $data = $this->repo->getAll();
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function getByUsuarioId(Request $request, Response $response, array $args): Response
    {
        try {
            $data = $this->repo->getByUsuarioId((int)$args['usuario_id']);
            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            return $this->error($response, $e->getMessage());
        }
    }

    public function getBySala(Request $request, Response $response, array $args): Response
    {
        $data = $this->repo->getBySalaAndFecha((int)$args['sala_id'], date('Y-m-d'));
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function create(Request $request, Response $response): Response
    {
        try {
            $data = json_decode($request->getBody()->getContents(), true);
            $this->service->crearReserva($data);

            $response->getBody()->write(json_encode([
                'message' => 'Reserva creada con éxito'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

        } catch (\DomainException $e) {
            $response->getBody()->write($e->getMessage());
            return $response->withHeader('Content-Type', 'application/json')->withStatus(409);

        } catch (\Throwable $e) {
            return $this->error($response, $e->getMessage());
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $data = json_decode($request->getBody()->getContents(), true);
            $this->service->actualizarReserva((int)$args['id'], $data);

            $response->getBody()->write(json_encode([
                'message' => 'Reserva actualizada correctamente'
            ]));
            return $response->withHeader('Content-Type', 'application/json');

        } catch (\DomainException $e) {
            $response->getBody()->write($e->getMessage());
            return $response->withHeader('Content-Type', 'application/json')->withStatus(409);

        } catch (\Throwable $e) {
            return $this->error($response, $e->getMessage());
        }
    }

    public function getHorariosDisponibles(Request $request, Response $response, array $args): Response
    {
        try {
            $fecha = $request->getQueryParams()['fecha'] ?? null;
            $duracion = intval($request->getQueryParams()['duracion'] ?? 60);

            if (!$fecha) {
                return $this->error($response, 'Fecha requerida', 400);
            }

            $horarios = $this->service->calcularHorariosDisponibles(
                (int)$args['sala_id'],
                $fecha,
                $duracion
            );

            $response->getBody()->write(json_encode([
                'fecha' => $fecha,
                'duracion' => $duracion,
                'horarios_disponibles' => $horarios
            ]));

            return $response->withHeader('Content-Type', 'application/json');

        } catch (\Throwable $e) {
            return $this->error($response, 'Error interno del servidor');
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->repo->delete((int)$args['id']);

        $response->getBody()->write(json_encode([
            'message' => 'Reserva eliminada'
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }

    private function error(Response $response, string $msg, int $status = 500): Response
    {
        $response->getBody()->write(json_encode(['error' => $msg]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
