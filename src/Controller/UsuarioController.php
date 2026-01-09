<?php
namespace App\Controller;

use App\Database\Database;
use App\Repository\UsuarioRepository;
use App\Service\UsuarioService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UsuarioController
{
    private UsuarioService $service;

    public function __construct(Database $db)
    {
        $repo = new UsuarioRepository($db->getConnection());
        $this->service = new UsuarioService($repo);
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
            return $res->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
    }

    public function create(Request $req, Response $res): Response
    {
        $data = json_decode($req->getBody()->getContents(), true);

        $this->service->crear($data);

        $res->getBody()->write(json_encode(['message' => 'Usuario creado']));
        return $res->withStatus(201)->withHeader('Content-Type', 'application/json');
    }

    public function update(Request $req, Response $res, array $args): Response
    {
        $data = json_decode($req->getBody()->getContents(), true);

        $this->service->actualizar((int)$args['id'], $data);

        $res->getBody()->write(json_encode(['message' => 'Usuario actualizado']));
        return $res->withHeader('Content-Type', 'application/json');
    }

    public function delete(Request $req, Response $res, array $args): Response
    {
        $this->service->eliminar((int)$args['id']);

        $res->getBody()->write(json_encode(['message' => 'Usuario eliminado']));
        return $res->withHeader('Content-Type', 'application/json');
    }
}
