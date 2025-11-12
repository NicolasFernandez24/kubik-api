<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Database\Database;

class UsuarioController
{
    private $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function getAll(Request $request, Response $response): Response
    {
        $stmt = $this->db->query("SELECT * FROM usuarios");
        $usuarios = $stmt->fetchAll();
        $response->getBody()->write(json_encode($usuarios));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function getById(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $usuario = $stmt->fetch();
        if ($usuario) {
            $response->getBody()->write(json_encode($usuario));
        } else {
            $response->getBody()->write(json_encode(['error' => 'Usuario no encontrado']));
            return $response->withStatus(404);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

   public function create(Request $request, Response $response): Response
{
    // Obtener los datos de la solicitud
     $data = json_decode($request->getBody(), true);

    // Verificar si los datos requeridos están presentes
    if (empty($data['nombre']) || empty($data['apellido']) || empty($data['telefono']) || empty($data['email']) || empty($data['contrasena']) || empty($data['rol'])) {
        // Si faltan campos, devolver un mensaje de error con los datos recibidos
        $response->getBody()->write(json_encode([
            'error' => 'Faltan campos obligatorios.',
            'received_data' => $data
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400); // Código 400 para solicitud incorrecta
    }

    try {
        // Preparar la consulta para insertar los datos
        $stmt = $this->db->prepare("INSERT INTO usuarios (nombre, apellido, telefono, email, contrasena, rol) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['nombre'],
            $data['apellido'],
            $data['telefono'],
            $data['email'],
            password_hash($data['contrasena'], PASSWORD_DEFAULT),
            $data['rol']
        ]);

        // Responder con un mensaje de éxito
        $response->getBody()->write(json_encode(['message' => 'Usuario creado']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201); // Código 201 para creado
    } catch (\PDOException $e) {
        // Si ocurre un error en la base de datos, devolver el error con los datos recibidos
        $response->getBody()->write(json_encode([
            'error' => 'Error al crear el usuario',
            'received_data' => $data,
            'exception' => $e->getMessage()
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500); // Código 500 para error en el servidor
    }
}


    public function update(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $data = json_decode($request->getBody(), true);
        $stmt = $this->db->prepare("UPDATE usuarios SET nombre = ?, apellido = ?, telefono = ?, email = ?, contrasena = ?, rol = ? WHERE id = ?");
        $stmt->execute([
            $data['nombre'],
            $data['apellido'],
            $data['telefono'],
            $data['email'],
            password_hash($data['contrasena'], PASSWORD_DEFAULT),
            $data['rol'],
            $id
        ]);
        $response->getBody()->write(json_encode(['message' => 'Usuario actualizado']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $response->getBody()->write(json_encode(['message' => 'Usuario eliminado']));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
