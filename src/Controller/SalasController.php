<?php
namespace App\Controller;

use App\Service\SalaService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SalasController
{
    private SalaService $service;

    public function __construct(SalaService $service)
    {
        $this->service = $service;
    }

    public function getAll(Request $req, Response $res): Response
    {
        $res->getBody()->write(json_encode($this->service->listar()));
        return $res->withHeader('Content-Type', 'application/json');
    }

    public function getById(Request $req, Response $res, array $args): Response
    {
        try {
            $res->getBody()->write(json_encode(
                $this->service->obtener((int)$args['id'])
            ));
            return $res->withHeader('Content-Type', 'application/json');
        } catch (\DomainException $e) {
            $res->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $res
                ->withStatus(404)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    public function create(Request $req, Response $res): Response
    {
        $this->service->crear(
            $req->getParsedBody(),
            $req->getUploadedFiles()
        );

        $res->getBody()->write(json_encode(['message' => 'Sala creada']));
        return $res
            ->withStatus(201)
            ->withHeader('Content-Type', 'application/json');
    }

    public function update(Request $req, Response $res, array $args): Response
    {
        $this->service->actualizar(
            (int)$args['id'],
            $req->getParsedBody(),
            $req->getUploadedFiles()
        );

        $res->getBody()->write(json_encode(['message' => 'Sala actualizada']));
        return $res->withHeader('Content-Type', 'application/json');
    }

    public function delete(Request $req, Response $res, array $args): Response
    {
        $this->service->eliminar((int)$args['id']);

        $res->getBody()->write(json_encode(['message' => 'Sala eliminada']));
        return $res->withHeader('Content-Type', 'application/json');
    }
}
